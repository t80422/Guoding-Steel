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
            log_message('debug', print_r($data, true));
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
        $orderDetails = $this->orderDetailModel->getDetailByOrderId($orderId);

        $staticItems = [
            ['中間樁','支','覆工板','塊','鋪路鋼板','片'],
            ['支撐料','支','構台角鐵','支','不鏽鋼花板','片'],
            ['圍令','支','構台帽','個','踢腳板','片'],
            ['短料','個','千斤頂','個','樓梯','座'],
            ['三角架','支','千斤頂保護蓋','片','組合樓梯','組'],
            ['大U','支','長螺絲','顆','平台踏板','個'],
            ['小U','支','短螺絲','顆','帆布','支'],
            ['大小U角鐵','支','膨脹螺絲','顆','襯片','捆'],
            ['角鐵','支','安全母索','捆','竹片','把'],
            ['PC連接板','片','母索立柱','支','夾板','片'],
            ['斜撐','支','GIP錏管','支','PGB錶、土壓計','台'],
            ['斜撐頭(大)','個','錏管帽蓋','個','油壓機','台'],
            ['斜撐頭(小)','個','C型夾','個','手動加壓機','台'],
            ['加勁盒','個','萬向活扣','個','操作油','桶'],
            ['車輪檔','座','活扣保護蓋','個','鐵籠','個'],
        ];

        $itemMap = [];
        foreach ($staticItems as $r_idx => $row) {
            if (isset($row[0])) $itemMap[$row[0]] = ['row' => $r_idx, 'col' => 0];
            if (isset($row[2])) $itemMap[$row[2]] = ['row' => $r_idx, 'col' => 1];
            if (isset($row[4])) $itemMap[$row[4]] = ['row' => $r_idx, 'col' => 2];
        }
        
        $quantities = array_map(fn($row) => array_fill(0, 3, ''), $staticItems);
        $displayNames = array_map(fn($row) => array_fill(0, 3, ''), $staticItems); // 用於記錄顯示名稱

        // 處理訂單明細
        foreach ($orderDetails as $item) {
            $minorCategoryName = $item['mic_name']; // 小分類名稱
            $productName = $item['pr_name'];       // 產品名稱

            if (isset($itemMap[$minorCategoryName])) {
                // 匹配成功
                $pos = $itemMap[$minorCategoryName];
                
                // 累加數量（處理同一產品多個規格的情況）
                if (empty($quantities[$pos['row']][$pos['col']])) {
                    $quantities[$pos['row']][$pos['col']] = 0;
                }
                $quantities[$pos['row']][$pos['col']] += (int)$item['od_qty'];

                // 決定顯示名稱（只設定一次，避免重複覆蓋）
                if (empty($displayNames[$pos['row']][$pos['col']])) {
                    if ($minorCategoryName === $productName) {
                        // 簡單產品：mic_name = pr_name，只顯示一次
                        $displayNames[$pos['row']][$pos['col']] = $minorCategoryName;
                    } else {
                        // 複雜產品：顯示 mic_name + pr_name
                        $displayNames[$pos['row']][$pos['col']] = $minorCategoryName . $productName;
                    }
                }
            }
        }

        // 取得項目明細統計（取代原本的 $otherDetails）
        $projectItemDetails = $this->orderDetailProjectItemModel->getProjectItemsDetailForPrint($orderId);

        $data = [
            'order'       => $order,
            'staticItems' => $staticItems,
            'quantities'  => $quantities,
            'displayNames'=> $displayNames,
            'details'     => $projectItemDetails,
        ];

        return view('print/warehouse_form', $data);
    }

    // 取得訂單明細
    public function getDetail($id){
        $orderDetails = $this->orderDetailModel->getDetailByOrderId($id);
        return $this->response->setJSON($orderDetails);
    }
}
