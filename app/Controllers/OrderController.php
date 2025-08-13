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
        log_message('debug', print_r($orderDetails, true));
        $gpsOptions = $this->gpsModel->getOptions();

        $data = [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'gpsOptions' => $gpsOptions,
        ];

        return view('order/form', ['data' => $data, 'isEdit' => true]);
    }

    // 保存
    public function save(){
        $this->orderModel->db->transStart();

        try {
            $data = $this->request->getPost();
            $userId = session()->get('userId');
    
            if (!$userId) {
                return redirect()->to(url_to('AuthController::index'))
                    ->with('error', '請先登入！');
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
        // 動態產生材料規格清單
        $allProducts = $this->productModel->getAllForPrint();
        $qtyByPrId = $this->orderDetailModel->getOrderProductQuantities((int) $orderId);

        $itemsA = [];
        $itemsB = [];
        foreach ($allProducts as $p) {
            $prId = (int) ($p['pr_id'] ?? 0);
            $prName = (string) ($p['pr_name'] ?? '');
            $prUnit = (string) ($p['mic_unit'] ?? '');
            $micName = (string) ($p['mic_name'] ?? '');

            $isSameName = ($prName === $micName);
            $hasQty = array_key_exists($prId, $qtyByPrId);

            if (!$isSameName) {
                if ($hasQty) {
                    $itemsA[] = [
                        'name' => trim($micName . ' ' . $prName),
                        'unit' => $prUnit,
                        'qty'  => $qtyByPrId[$prId] ?? ''
                    ];
                }
            } else {
                $itemsB[] = [
                    'name' => $prName,
                    'unit' => $prUnit,
                    'qty'  => $qtyByPrId[$prId] ?? ''
                ];
            }
        }

        // 合併 A（不同名且有明細）在前，B（同名全列）在後
        $items = array_merge($itemsA, $itemsB);

        // 轉成三欄網格
        $itemsGrid = [];
        $row = [];
        foreach ($items as $idx => $item) {
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

        // 取得項目明細統計（改由 OrderDetailModel 提供）
        $projectItemDetails = $this->orderDetailModel->getDetailsForPrint((int)$orderId);

        return view('print/warehouse_form', [
            'order' => $order,
            'itemsGrid' => $itemsGrid,
            'details' => $projectItemDetails,
        ]);
    }

    // 取得訂單明細
    public function getDetail($id){
        $orderDetails = $this->orderDetailModel->getDetailByOrderId($id);
        return $this->response->setJSON($orderDetails);
    }
}
