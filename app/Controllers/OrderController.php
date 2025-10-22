<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\OrderDetailProjectItemModel;
use App\Models\LocationModel;
use App\Models\GpsModel;
use App\Models\MinorCategoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;
use App\Libraries\OrderService;
use App\Services\PermissionService;

class OrderController extends BaseController
{
    protected $orderModel;
    protected $orderDetailModel;
    protected $orderDetailProjectItemModel;
    protected $orderService;
    protected $locationModel;
    protected $gpsModel;
    protected $minorCategoryModel;
    protected $permissionService;
    private ?array $defaultMinorCategories = null;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->orderDetailProjectItemModel = new OrderDetailProjectItemModel();
        $this->orderService = new OrderService();
        $this->locationModel = new LocationModel();
        $this->gpsModel = new GpsModel();
        $this->minorCategoryModel = new MinorCategoryModel();
        $this->permissionService = new PermissionService();
    }

    // 列表
    public function index()
    {
        $query = $this->request->getGet();

        $keyword = $query['keyword'] ?? null;
        $orderDateStart = $query['order_date_start'] ?? null;
        $orderDateEnd = $query['order_date_end'] ?? null;
        $type = $query['type'] ?? null;
        $page = isset($query['page']) ? (int) $query['page'] : 1;

        $result = $this->orderModel->getList($keyword, $orderDateStart, $orderDateEnd, $type, $page);

        return view('order/index', [
            'data' => $result['data'],
            'keyword' => $keyword,
            'order_date_start' => $orderDateStart,
            'order_date_end' => $orderDateEnd,
            'type' => $type,
            'pagination' => $result['pagination'],
        ]);
    }

    // 編輯
    public function edit($id = null)
    {
        $order = $this->orderModel->getDetail($id);

        if (!$order) {
            throw new PageNotFoundException('無法找到該訂單: ' . $id);
        }

        $orderDetails = $this->orderDetailModel->getDetailByOrderId($id);
        $gpsOptions = $this->gpsModel->getOptions();

        $data = [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'gpsOptions' => $gpsOptions,
        ];

        return view('order/form', ['data' => $data, 'isEdit' => true]);
    }

    // 保存
    public function save()
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        try {
            $data = $this->request->getPost();
            $files = $this->request->getFiles();
            $userId = session()->get('userId');
            
            if (!$userId) {
                return redirect()->to(url_to('AuthController::index'))
                    ->with('error', '請先登入！');
            }

            $orderId = $data['o_id'];
            $detailsData = $data['details'] ?? [];

            // 使用 OrderService 統一處理
            $success = $this->orderService->updateOrder((int)$orderId, $data, $detailsData, $files, (int)$userId);

            if ($success) {
                return redirect()->to(url_to('OrderController::index'))->with('success', '訂單更新成功');
            } else {
                return redirect()->to(url_to('OrderController::index'))->with('error', '儲存失敗');
            }
        } catch (Exception $e) {
            return redirect()->to(url_to('OrderController::index'))->with('error', '儲存失敗：' . $e->getMessage());
        }
    }

    // 提供簽名圖片
    public function serveSignature(string $filename)
    {
        $path = OrderService::UPLOAD_PATH . $filename;

        if (!file_exists($path)) {
            throw new PageNotFoundException('無法找到簽名圖片: ' . $filename);
        }

        // 設置正確的內容類型
        $mime = mime_content_type($path);
        $this->response->setContentType($mime);

        // 讀取並輸出文件內容
        return $this->response->setBody(file_get_contents($path));
    }

    // 刪除
    public function delete($id = null)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        try {
            // 使用 OrderService 統一處理
            $success = $this->orderService->deleteOrder((int)$id);
            
            if ($success) {
                return redirect()->to(url_to('OrderController::index'))->with('success', '刪除成功');
            } else {
                return redirect()->to(url_to('OrderController::index'))->with('error', '刪除失敗');
            }
        } catch (Exception $e) {
            return redirect()->to(url_to('OrderController::index'))->with('error', '刪除失敗');
        }
    }

    /**
     * 取得預設的小分類清單（資料庫設定的預設項目，維持排序）
     */
    private function getDefaultMinorCategories(): array
    {
        if ($this->defaultMinorCategories !== null) {
            return $this->defaultMinorCategories;
        }

        $rows = $this->minorCategoryModel
            ->where('mic_is_default', 1)
            ->orderBy('mic_id', 'ASC')
            ->findAll();

        $this->defaultMinorCategories = array_map(static function ($row) {
            return [
                'mic_id' => (int) ($row['mic_id'] ?? 0),
                'mic_name' => (string) ($row['mic_name'] ?? ''),
                'mic_unit' => (string) ($row['mic_unit'] ?? ''),
            ];
        }, $rows);

        return $this->defaultMinorCategories;
    }

    /**
     * 建立材料規格清單
     */
    private function buildMaterialGrid(int $orderId): array
    {
        $defaultCategories = $this->getDefaultMinorCategories();
        $orderProducts = $this->orderDetailModel->getOrderProductsWithCategories($orderId);

        // 以小分類彙整訂單明細
        $minorCategoryMap = [];
        foreach ($orderProducts as $item) {
            $mcName = (string) ($item['mc_name'] ?? '');
            $micId = (int) ($item['mic_id'] ?? 0);
            $micName = (string) ($item['mic_name'] ?? '');
            $micUnit = (string) ($item['mic_unit'] ?? '');
            $prName = (string) ($item['pr_name'] ?? '');
            $qty = $item['od_qty'] ?? 0;
            $prId = (int) ($item['pr_id'] ?? 0);
            $prIsLength = !empty($item['pr_is_length']);
            $odLength = (float) ($item['od_length'] ?? 0);

            if ($micId === 0) {
                // 沒有小分類資訊就略過，避免放入無效資料列
                continue;
            }

            if (!isset($minorCategoryMap[$micId])) {
                $minorCategoryMap[$micId] = [
                    'mic_id' => $micId,
                    'mic_name' => $micName,
                    'mic_unit' => $micUnit,
                    'products' => [],
                    'total_qty' => 0,
                    'min_pr_id' => $prId > 0 ? $prId : PHP_INT_MAX,
                ];
            }

            $minorCategoryMap[$micId]['total_qty'] += $qty;

            if (!isset($minorCategoryMap[$micId]['products'][$prId])) {
                $minorCategoryMap[$micId]['products'][$prId] = [
                    'pr_id' => $prId,
                    'name' => $prName,
                    'is_length' => $prIsLength,
                    'qty' => 0,
                    'length_groups' => [],
                ];
            }

            // 如果是長度產品，按長度分組
            if ($prIsLength && $mcName !== '型鋼') {
                // 正規化長度值為字串 key，避免浮點數精度問題
                $lengthKey = rtrim(rtrim(number_format($odLength, 6, '.', ''), '0'), '.');
                if ($lengthKey === '') {
                    $lengthKey = '0';
                }
                
                if (!isset($minorCategoryMap[$micId]['products'][$prId]['length_groups'][$lengthKey])) {
                    $minorCategoryMap[$micId]['products'][$prId]['length_groups'][$lengthKey] = [
                        'value' => $odLength,
                        'qty' => 0,
                    ];
                }
                $minorCategoryMap[$micId]['products'][$prId]['length_groups'][$lengthKey]['qty'] += $qty;
            }

            $minorCategoryMap[$micId]['products'][$prId]['qty'] += $qty;

            if ($prId > 0 && $prId < $minorCategoryMap[$micId]['min_pr_id']) {
                $minorCategoryMap[$micId]['min_pr_id'] = $prId;
            }
        }

        $items = [];

        // 先加入預設小分類
        foreach ($defaultCategories as $category) {
            $micId = (int) $category['mic_id'];
            $micName = (string) $category['mic_name'];
            $unit = (string) $category['mic_unit'];

            if (isset($minorCategoryMap[$micId])) {
                $products = $minorCategoryMap[$micId]['products'];
                ksort($products);
                $products = array_values($products);

                $productNames = [];
                $quantities = [];

                foreach ($products as $product) {
                    $baseName = (string) $product['name'];
                    $isLength = !empty($product['is_length']);

                    if ($isLength && !empty($product['length_groups'])) {
                        // 按長度值排序（由小到大）
                        $lengthGroups = $product['length_groups'];
                        uasort($lengthGroups, function($a, $b) {
                            return $a['value'] <=> $b['value'];
                        });

                        $formattedLengths = [];
                        $qtys = [];
                        
                        foreach ($lengthGroups as $group) {
                            $lengthValue = $group['value'];
                            $qtyValue = $group['qty'];
                            
                            // 格式化長度（保留小數，移除不必要的尾隨0，加上單位m）
                            $formatted = rtrim(rtrim(number_format((float)$lengthValue, 2, '.', ''), '0'), '.');
                            $formattedLengths[] = $formatted . 'm';
                            $qtys[] = $qtyValue;
                        }

                        $productNames[] = $baseName . ' ' . implode('/', $formattedLengths);
                        $quantities[] = implode('/', $qtys);
                    } else {
                        $productNames[] = $baseName;
                        $quantities[] = (string) $product['qty'];
                    }
                }

                $allNamesMatchCategory = !empty($productNames) && empty(array_filter($productNames, static function ($name) use ($micName) {
                    $cleanName = explode(' ', trim((string) $name))[0];
                    return $cleanName !== $micName;
                }));

                if ($allNamesMatchCategory && count($productNames) === 1) {
                    $items[] = [
                        'name' => $productNames[0],
                        'unit' => $unit,
                        'qty' => $quantities[0],
                    ];
                } else {
                    $displayName = $micName;
                    if (!empty($productNames)) {
                        $displayName .= ' ' . implode('/', $productNames);
                    }

                    $items[] = [
                        'name' => $displayName,
                        'unit' => $unit,
                        'qty' => !empty($quantities) ? implode('/', $quantities) : '',
                    ];
                }

                unset($minorCategoryMap[$micId]);
            } else {
                $items[] = [
                    'name' => $micName,
                    'unit' => $unit,
                    'qty' => '',
                ];
            }
        }

        if (!empty($minorCategoryMap)) {
            // 處理非預設但出現在訂單中的小分類
            uasort($minorCategoryMap, static function ($a, $b) {
                return $a['min_pr_id'] <=> $b['min_pr_id'];
            });

            foreach ($minorCategoryMap as $micId => $categoryData) {
                $micName = $categoryData['mic_name'];
                $unit = $categoryData['mic_unit'];
                $products = $categoryData['products'];
                ksort($products);
                $products = array_values($products);

                $productNames = [];
                $quantities = [];

                foreach ($products as $product) {
                    $baseName = (string) $product['name'];
                    $isLength = !empty($product['is_length']);

                    if ($isLength && !empty($product['length_groups'])) {
                        // 按長度值排序（由小到大）
                        $lengthGroups = $product['length_groups'];
                        uasort($lengthGroups, function($a, $b) {
                            return $a['value'] <=> $b['value'];
                        });

                        $formattedLengths = [];
                        $qtys = [];
                        
                        foreach ($lengthGroups as $group) {
                            $lengthValue = $group['value'];
                            $qtyValue = $group['qty'];
                            
                            // 格式化長度（保留小數，移除不必要的尾隨0，加上單位m）
                            $formatted = rtrim(rtrim(number_format((float)$lengthValue, 2, '.', ''), '0'), '.');
                            $formattedLengths[] = $formatted . 'm';
                            $qtys[] = $qtyValue;
                        }

                        $productNames[] = $baseName . ' ' . implode('/', $formattedLengths);
                        $quantities[] = implode('/', $qtys);
                    } else {
                        $productNames[] = $baseName;
                        $quantities[] = (string) $product['qty'];
                    }
                }

                $allNamesMatchCategory = !empty($productNames) && empty(array_filter($productNames, static function ($name) use ($micName) {
                    $cleanName = explode(' ', trim((string) $name))[0];
                    return $cleanName !== $micName;
                }));

                if ($allNamesMatchCategory && count($productNames) === 1) {
                    $items[] = [
                        'name' => $productNames[0],
                        'unit' => $unit,
                        'qty' => $quantities[0],
                    ];
                } else {
                    $displayName = $micName;
                    if (!empty($productNames)) {
                        $displayName .= ' ' . implode('/', $productNames);
                    }

                    $items[] = [
                        'name' => $displayName,
                        'unit' => $unit,
                        'qty' => !empty($quantities) ? implode('/', $quantities) : '',
                    ];
                }
            }
        }

        // 轉成三欄網格
        $itemsGrid = [];
        $row = [];
        foreach ($items as $item) {
            $row[] = $item;
            if (count($row) === 3) {
                $itemsGrid[] = $row;
                $row = [];
            }
        }

        if (count($row) > 0) {
            while (count($row) < 3) {
                $row[] = null;
            }
            $itemsGrid[] = $row;
        }

        return $itemsGrid;
    }

    // 列印
    public function print($orderId = null)
    {
        if (!$orderId) {
            return redirect()->to(url_to('OrderController::index'))->with('error', '請提供訂單ID');
        }

        $order = $this->orderModel->getDetail($orderId);
        if (!$order) {
            throw new PageNotFoundException('找不到該訂單: ' . $orderId);
        }

        $itemsGrid = $this->buildMaterialGrid((int) $orderId);

        // 取得項目明細統計
        $projectItemDetails = $this->orderDetailModel->getDetailsForPrint((int)$orderId);

        // 取得總噸數
        $totalWeight = $this->orderDetailModel->getTotalWeight((int)$orderId);
        $formatTotalWeight = $totalWeight > 0 ? round($totalWeight / 1000, 2) : '';

        return view('order/warehouse_form', [
            'order' => $order,
            'itemsGrid' => $itemsGrid,
            'details' => $projectItemDetails,
            'totalWeight' => $formatTotalWeight
        ]);
    }

    // 取得訂單明細
    public function getDetail($id)
    {
        $orderDetails = $this->orderDetailModel->getDetailByOrderId($id);
        return $this->response->setJSON($orderDetails);
    }
}
