<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\OrderDetailProjectItemModel;
use App\Models\LocationModel;
use App\Models\GpsModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;
use App\Libraries\OrderService;
use App\Libraries\FileManager;
use App\Services\InventoryService;
use App\Services\PermissionService;

class OrderController extends BaseController
{
    protected $orderModel;
    protected $orderDetailModel;
    protected $orderDetailProjectItemModel;
    protected $orderService;
    protected $locationModel;
    protected $gpsModel;
    protected $productModel;
    protected $fileManager;
    protected $inventoryService;
    protected $permissionService;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->orderDetailProjectItemModel = new OrderDetailProjectItemModel();
        $this->orderService = new OrderService();
        $this->locationModel = new LocationModel();
        $this->gpsModel = new GpsModel();
        $this->productModel = new ProductModel();
        $this->fileManager = new FileManager(WRITEPATH . 'uploads/signatures/');
        $this->inventoryService = new InventoryService();
        $this->permissionService = new PermissionService();
    }

    // 列表
    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $orderDateStart = $this->request->getGet('order_date_start');
        $orderDateEnd = $this->request->getGet('order_date_end');
        $type = $this->request->getGet('type');

        $data = $this->orderModel->getList($keyword, $orderDateStart, $orderDateEnd, $type);

        return view('order/index', [
            'data' => $data,
            'keyword' => $keyword,
            'order_date_start' => $orderDateStart,
            'order_date_end' => $orderDateEnd,
            'type' => $type
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

        $this->orderModel->db->transStart();

        try {
            $data = $this->request->getPost();
            $userId = session()->get('userId');
            if (!$userId) {
                return redirect()->to(url_to('AuthController::index'))
                    ->with('error', '請先登入！');
            }
            
            // 處理外鍵欄位：空字串轉為 NULL，避免外鍵約束錯誤
            $foreignKeyFields = ['o_g_id', 'o_from_location', 'o_to_location'];
            foreach ($foreignKeyFields as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            
            // 取得修改前的訂單資料用於庫存更新
            $orderId = $data['o_id'];
            $oldOrder = $this->orderModel->find($orderId);
            $oldOrderDetails = $this->orderDetailModel->getByOrderId($orderId);

            $data['o_update_by'] = $userId;
            $data['o_update_at'] = date('Y-m-d H:i:s');

            $this->orderModel->save($data);
            $this->orderService->updateOrderDetails($data['o_id'], $data['details']);

            // 更新庫存
            $this->inventoryService->updateInventoryForOrder($orderId, 'UPDATE', $oldOrder, $oldOrderDetails);

            $this->orderModel->db->transComplete();
            return redirect()->to(url_to('OrderController::index'));
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $this->orderModel->db->transRollback();
            return redirect()->to(url_to('OrderController::index'))->with('error', '儲存失敗');
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

        $this->orderModel->db->transStart();
        try {
            $order = $this->orderModel->find($id);
            if (!$order) {
                throw new PageNotFoundException('無法找到該訂單: ' . $id);
            }

            // 更新庫存 (在實際刪除前)
            $this->inventoryService->updateInventoryForOrder($id, 'DELETE');

            // 刪除訂單明細
            $this->orderDetailModel->where('od_o_id', $id)->delete();

            // 刪除訂單
            $this->orderModel->delete($id);

            // 刪除相關簽名檔案 - 使用 FileManager
            $signatureKeys = ['o_driver_signature', 'o_from_signature', 'o_to_signature'];
            $filesToDelete = [];
            foreach ($signatureKeys as $key) {
                if (!empty($order[$key])) {
                    $filesToDelete[] = $order[$key];
                }
            }
            if (!empty($filesToDelete)) {
                $this->fileManager->deleteFiles($filesToDelete);
            }

            $this->orderModel->db->transComplete();
            return redirect()->to(url_to('OrderController::index'))->with('success', '刪除成功');
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $this->orderModel->db->transRollback();
            return redirect()->to(url_to('OrderController::index'))->with('error', '刪除失敗');
        }
    }

    /**
     * 取得固定產品清單模板（按指定順序排列，必須全部顯示）
     */
    private function getFixedProductTemplate(): array
    {
        return [
            ['name' => '中間樁', 'unit' => '支', 'dynamic' => true],
            ['name' => '覆工板', 'unit' => '塊', 'dynamic' => true],
            ['name' => '鋪路鋼板', 'unit' => '片', 'dynamic' => true],
            ['name' => '支撐料', 'unit' => '支', 'dynamic' => true],
            ['name' => '構台角鐵', 'unit' => '支', 'dynamic' => false],
            ['name' => '不鏽鋼花板', 'unit' => '片', 'dynamic' => true],
            ['name' => '圍令', 'unit' => '支', 'dynamic' => true],
            ['name' => '構台帽', 'unit' => '個', 'dynamic' => false],
            ['name' => '踢腳板', 'unit' => '片', 'dynamic' => false],
            ['name' => '短料', 'unit' => '個', 'dynamic' => true],
            ['name' => '千斤頂', 'unit' => '個', 'dynamic' => true],
            ['name' => '樓梯', 'unit' => '座', 'dynamic' => true],
            ['name' => '三角架', 'unit' => '支', 'dynamic' => true],
            ['name' => '千斤頂保護蓋', 'unit' => '片', 'dynamic' => false],
            ['name' => '組合樓梯', 'unit' => '組', 'dynamic' => true],
            ['name' => '大U', 'unit' => '支', 'dynamic' => true],
            ['name' => '長螺絲', 'unit' => '顆', 'dynamic' => false],
            ['name' => '平台踏板', 'unit' => '個', 'dynamic' => false],
            ['name' => '小U', 'unit' => '支', 'dynamic' => false],
            ['name' => '短螺絲', 'unit' => '顆', 'dynamic' => false],
            ['name' => '帆布', 'unit' => '支', 'dynamic' => true],
            ['name' => '大小U角鐵', 'unit' => '支', 'dynamic' => false],
            ['name' => '膨脹螺絲', 'unit' => '支', 'dynamic' => true],
            ['name' => '襯板', 'unit' => '捆', 'dynamic' => false],
            ['name' => '角鐵', 'unit' => '支', 'dynamic' => true],
            ['name' => '安全母索', 'unit' => '捆', 'dynamic' => false],
            ['name' => '竹片', 'unit' => '把', 'dynamic' => false],
            ['name' => 'PC連接板', 'unit' => '片', 'dynamic' => false],
            ['name' => '母索立柱', 'unit' => '支', 'dynamic' => false],
            ['name' => '夾板', 'unit' => '片', 'dynamic' => true],
            ['name' => '斜撐', 'unit' => '支', 'dynamic' => false],
            ['name' => 'GIP錏管', 'unit' => '支', 'dynamic' => true],
            ['name' => 'PGB錶、土壓計', 'unit' => '台', 'dynamic' => false],
            ['name' => '斜撐頭(大)', 'unit' => '個', 'dynamic' => false],
            ['name' => '錏管帽蓋', 'unit' => '個', 'dynamic' => false],
            ['name' => '油壓機', 'unit' => '台', 'dynamic' => false],
            ['name' => '斜撐頭(小)', 'unit' => '個', 'dynamic' => false],
            ['name' => 'C型夾', 'unit' => '個', 'dynamic' => false],
            ['name' => '手動加壓機', 'unit' => '台', 'dynamic' => false],
            ['name' => '加勁盒', 'unit' => '個', 'dynamic' => false],
            ['name' => '萬向活扣', 'unit' => '個', 'dynamic' => false],
            ['name' => '操作油', 'unit' => '桶', 'dynamic' => true],
            ['name' => '車輪檔', 'unit' => '座', 'dynamic' => true],
            ['name' => '活扣保護蓋', 'unit' => '個', 'dynamic' => false],
            ['name' => '鐵籠', 'unit' => '個', 'dynamic' => false],
        ];
    }

    /**
     * 建立新的材料規格清單
     */
    private function buildMaterialGrid(int $orderId): array
    {
        $fixedTemplate = $this->getFixedProductTemplate();

        // 取得訂單明細（用於統計數量）
        $orderProducts = $this->orderDetailModel->getOrderProductsWithCategories($orderId);

        // 按產品ID分組統計數量
        $productQtyMap = [];
        $productInfoMap = [];
        foreach ($orderProducts as $item) {
            $prId = (int) $item['od_pr_id'];
            $qty = (int) $item['od_qty'];

            if (!isset($productQtyMap[$prId])) {
                $productQtyMap[$prId] = 0;
            }
            $productQtyMap[$prId] += $qty;

            $productInfoMap[$prId] = [
                'pr_name' => $item['pr_name'],
                'mic_name' => $item['mic_name'],
                'mic_unit' => $item['mic_unit'],
                'mc_name' => $item['mc_name'],
            ];
        }

        // 取得所有型鋼/配件產品
        $allSteelAccessoryProducts = $this->productModel->getSteelAndAccessoryProducts();

        $items = [];

        // 1. 固定清單優先（按定義順序，必須全部顯示）
        $processedMicNames = [];
        foreach ($fixedTemplate as $templateItem) {
            $micName = $templateItem['name'];
            $unit = $templateItem['unit'];
            $isDynamic = $templateItem['dynamic'];

            $processedMicNames[] = $micName;

            if ($isDynamic) {
                // 動態項目：找出該小分類下有哪些產品型號
                $matchedProducts = [];
                $totalQty = 0;

                foreach ($productInfoMap as $prId => $info) {
                    if ($info['mic_name'] === $micName && isset($productQtyMap[$prId])) {
                        $matchedProducts[] = $info['pr_name'];
                        $totalQty += $productQtyMap[$prId];
                    }
                }

                if (!empty($matchedProducts)) {
                    // 收集產品名稱和對應數量，保持對應關係
                    $productDetails = [];
                    $quantities = [];
                    foreach ($productInfoMap as $prId => $info) {
                        if ($info['mic_name'] === $micName && isset($productQtyMap[$prId])) {
                            $productDetails[] = $info['pr_name'];
                            $quantities[] = $productQtyMap[$prId];
                        }
                    }

                    $productNames = implode('/', $productDetails);
                    $qtyDisplay = implode('/', $quantities);
                    $items[] = [
                        'name' => $micName . ' ' . $productNames,
                        'unit' => $unit,
                        'qty' => $qtyDisplay
                    ];
                } else {
                    // 沒有明細也要顯示
                    $items[] = [
                        'name' => $micName,
                        'unit' => $unit,
                        'qty' => ''
                    ];
                }
            } else {
                // 固定項目：直接顯示小分類名稱
                $qty = '';
                foreach ($productInfoMap as $prId => $info) {
                    if ($info['mic_name'] === $micName && isset($productQtyMap[$prId])) {
                        $qty = $productQtyMap[$prId];
                        break;
                    }
                }

                $items[] = [
                    'name' => $micName,
                    'unit' => $unit,
                    'qty' => $qty
                ];
            }
        }

        // 2. 其他型鋼/配件產品（按小分類分組，類似第一優先級處理）
        $steelAccessoryByCategory = [];
        foreach ($allSteelAccessoryProducts as $product) {
            $prId = (int) $product['pr_id'];
            $micName = $product['mic_name'];
            $prName = $product['pr_name'];
            $unit = $product['mic_unit'];

            // 排除已經在固定清單中的項目
            if (!in_array($micName, $processedMicNames)) {
                if (!isset($steelAccessoryByCategory[$micName])) {
                    $steelAccessoryByCategory[$micName] = [
                        'unit' => $unit,
                        'products' => [],
                        'total_qty' => 0
                    ];
                }

                $steelAccessoryByCategory[$micName]['products'][$prId] = [
                    'pr_name' => $prName,
                    'qty' => $productQtyMap[$prId] ?? 0
                ];

                if (isset($productQtyMap[$prId])) {
                    $steelAccessoryByCategory[$micName]['total_qty'] += $productQtyMap[$prId];
                }
            }
        }

        foreach ($steelAccessoryByCategory as $micName => $categoryData) {
            $unit = $categoryData['unit'];
            $totalQty = $categoryData['total_qty'];

            // 找出在訂單明細中的產品
            $productsInOrder = [];
            foreach ($categoryData['products'] as $prId => $productData) {
                if ($productData['qty'] > 0) {
                    $productsInOrder[] = $productData['pr_name'];
                }
            }

            if (!empty($productsInOrder)) {
                // 有產品在明細中，檢查是否與小分類名稱相同
                $productDetails = [];
                $quantities = [];
                foreach ($categoryData['products'] as $prId => $productData) {
                    if ($productData['qty'] > 0) {
                        $productDetails[] = $productData['pr_name'];
                        $quantities[] = $productData['qty'];
                    }
                }

                // 檢查是否所有產品名稱都等於小分類名稱
                $allProductsSameAsMic = true;
                foreach ($productDetails as $productName) {
                    if ($productName !== $micName) {
                        $allProductsSameAsMic = false;
                        break;
                    }
                }

                if ($allProductsSameAsMic) {
                    // 所有產品名稱都等於小分類名稱：只顯示小分類
                    $qtyDisplay = implode('/', $quantities);
                    $items[] = [
                        'name' => $micName,
                        'unit' => $unit,
                        'qty' => $qtyDisplay
                    ];
                } else {
                    // 有產品名稱不同於小分類：小分類 + 產品名稱/數量
                    $productNames = implode('/', $productDetails);
                    $qtyDisplay = implode('/', $quantities);
                    $items[] = [
                        'name' => $micName . ' ' . $productNames,
                        'unit' => $unit,
                        'qty' => $qtyDisplay
                    ];
                }
            } else {
                // 沒有產品在明細中：只顯示小分類
                $items[] = [
                    'name' => $micName,
                    'unit' => $unit,
                    'qty' => ''
                ];
            }
        }

        // 3. 其他分類產品（按小分類分組，只顯示訂單明細中有的）
        $otherProductsByCategory = [];
        foreach ($productInfoMap as $prId => $info) {
            if (
                !in_array($info['mc_name'], ['型鋼', '配件']) &&
                isset($productQtyMap[$prId])
            ) {

                $micName = $info['mic_name'];
                $prName = $info['pr_name'];
                $unit = $info['mic_unit'];
                $qty = $productQtyMap[$prId];

                if (!isset($otherProductsByCategory[$micName])) {
                    $otherProductsByCategory[$micName] = [
                        'unit' => $unit,
                        'products' => [],
                        'total_qty' => 0,
                        'min_pr_id' => $prId // 用於排序
                    ];
                }

                $otherProductsByCategory[$micName]['products'][$prId] = [
                    'pr_name' => $prName,
                    'qty' => $qty
                ];

                $otherProductsByCategory[$micName]['total_qty'] += $qty;

                // 記錄最小的產品ID作為排序依據
                if ($prId < $otherProductsByCategory[$micName]['min_pr_id']) {
                    $otherProductsByCategory[$micName]['min_pr_id'] = $prId;
                }
            }
        }

        // 按最小產品ID排序
        uasort($otherProductsByCategory, function ($a, $b) {
            return $a['min_pr_id'] <=> $b['min_pr_id'];
        });

        foreach ($otherProductsByCategory as $micName => $categoryData) {
            $unit = $categoryData['unit'];
            $totalQty = $categoryData['total_qty'];

            // 找出在訂單明細中的產品（第三優先級都是有明細的）
            $productsInOrder = [];
            foreach ($categoryData['products'] as $prId => $productData) {
                $productsInOrder[] = $productData['pr_name'];
            }

            // 檢查是否所有產品名稱都等於小分類名稱
            $allProductsSameAsMic = true;
            foreach ($productsInOrder as $productName) {
                if ($productName !== $micName) {
                    $allProductsSameAsMic = false;
                    break;
                }
            }

            if ($allProductsSameAsMic) {
                // 所有產品名稱都等於小分類名稱：只顯示小分類
                $items[] = [
                    'name' => $micName,
                    'unit' => $unit,
                    'qty' => $totalQty
                ];
            } else {
                // 有產品名稱不同於小分類：小分類 + 產品名稱/數量
                $productDetails = [];
                $quantities = [];
                foreach ($categoryData['products'] as $prId => $productData) {
                    $productDetails[] = $productData['pr_name'];
                    $quantities[] = $productData['qty'];
                }

                $productNames = implode('/', $productDetails);
                $qtyDisplay = implode('/', $quantities);
                $items[] = [
                    'name' => $micName . ' ' . $productNames,
                    'unit' => $unit,
                    'qty' => $qtyDisplay
                ];
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
                $row[] = null; // 補滿三欄
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

        // 使用新的材料規格清單邏輯
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
