<?php

namespace App\Controllers\Api;

use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\UserLocationModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use Exception;
use App\Libraries\OrderService;

class OrderController extends Controller
{
    use ResponseTrait;

    protected $orderModel;
    protected $orderDetailModel;
    protected $orderService;
    protected $userLocationModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->orderService = new OrderService();
        $this->userLocationModel = new UserLocationModel();
    }

    // 新增
    public function create()
    {
        $this->orderModel->db->transStart();

        try {
            $data = $this->request->getPost();
            $files = $this->request->getFiles();

            $jsonOrder = json_decode($data['order'], true);
            $jsonDetails = json_decode($data['details'], true);

            //todo: 改成使用FileManager
            // 處理檔案上傳
            $signatureKeys = ['o_driver_signature', 'o_from_signature', 'o_to_signature'];
            $newFileNames = []; // 初始化新檔案名稱陣列
            foreach ($signatureKeys as $key) {
                if (isset($files[$key]) && $files[$key]->isValid() && !$files[$key]->hasMoved()) {
                    $file = $files[$key];
                    $jsonOrder[$key] = $this->orderService->uploadSignature($file);
                    $newFileNames[] = $jsonOrder[$key]; // 記錄新檔案名稱
                } else {
                    $jsonOrder[$key] = null; // 如果沒有上傳檔案，確保欄位為空
                }
            }

            // 狀態
            $jsonOrder['o_status'] = OrderModel::STATUS_IN_PROGRESS;

            // 新增主表
            $orderId = $this->orderModel->insert($jsonOrder);

            // 新增明細表
            foreach ($jsonDetails as &$detail) {
                $detail['od_o_id'] = $orderId;
            }
            unset($detail); // Unset the reference to avoid unexpected side effects

            $this->orderDetailModel->insertBatch($jsonDetails);

            $this->orderModel->db->transComplete();

            return $this->respondCreated(null);
        } catch (Exception $e) {
            $this->orderModel->db->transRollback();
            // 如果新增失敗，刪除已上傳的檔案
            foreach ($newFileNames as $fileName) {
                $this->orderService->deleteSignature($fileName);
            }
            log_message('error', $e->getMessage());
            return $this->fail('新增失敗');
        }
    }

    // 列表
    public function index()
    {
        try {
            // 取得使用者ID (header 取得)
            $userId = $this->request->getHeaderLine('X-User-ID');
            
            if (!$userId) {
                return $this->fail('缺少使用者身份資訊');
            }

            // 取得使用者有權限的地點ID
            $userLocationIds = $this->userLocationModel->getUserLocationIds($userId);
            
            // 根據地點權限過濾訂單
            $orders = $this->orderModel->getByInProgressWithLocationFilter($userLocationIds, $userId);

            $data = [];
            foreach ($orders as $order) {
                $data[] = [
                    'o_id' => $order['o_id'],
                    'o_from_location' => $order['from_location_name'],
                    'o_to_location' => $order['to_location_name'],
                    'o_car_number' => $order['o_car_number'],
                    'o_driver_signature' => $order['o_driver_signature'] ? true : false,
                    'o_from_signature' => $order['o_from_signature'] ? true : false,
                    'o_to_signature' => $order['o_to_signature'] ? true : false,
                ];
            }
            return $this->respond($data);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('取得列表失敗');
        }
    }

    // 詳細資料
    public function detail($id = null)
    {
        try {
            $order = $this->orderModel->getDetail($id);

            if ($order) {
                $signatureKeys = ['o_driver_signature', 'o_from_signature', 'o_to_signature'];
                foreach ($signatureKeys as $key) {
                    if (!empty($order[$key])) {
                        $order[$key] = url_to('OrderController::serveSignature', $order[$key]);
                    } else {
                        $order[$key] = null; // 確保如果沒有簽名，欄位值為 null
                    }
                }
            }

            $details = $this->orderDetailModel->getDetailByOrderId($id);

            $data = [
                'order' => $order,
                'details' => $details,
            ];

            return $this->respond($data);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('取得詳細資料失敗');
        }
    }

    // 更新
    public function update($id = null)
    {
        $this->orderModel->db->transStart();
        try {
            $order = $this->orderModel->find($id);

            if (!$order) {
                return $this->failNotFound('訂單不存在');
            }

            $data = $this->request->getPost();
            $files = $this->request->getFiles();

            $jsonOrder = json_decode($data['order'], true);
            $jsonDetails = json_decode($data['details'], true);

            //todo: 改成使用FileManager
            // 處理檔案上傳
            $signatureKeys = ['o_driver_signature', 'o_from_signature', 'o_to_signature'];
            foreach ($signatureKeys as $key) {
                if (isset($files[$key]) && $files[$key]->isValid() && !$files[$key]->hasMoved()) {
                    // 如果有新檔案上傳，先刪除舊檔案
                    if (!empty($order[$key])) {
                        $this->orderService->deleteSignature($order[$key]);
                    }
                    $file = $files[$key];
                    $jsonOrder[$key] = $this->orderService->uploadSignature($file);
                } else if (array_key_exists($key, $jsonOrder) && $jsonOrder[$key] === null) {
                    // 如果前端傳送 null，表示要刪除簽名
                    if (!empty($order[$key])) {
                        $this->orderService->deleteSignature($order[$key]);
                    }
                    $jsonOrder[$key] = null;
                } else {
                    // 如果沒有新檔案上傳，且前端沒有傳送該欄位或值不為 null，則保留原有簽名
                    if (!isset($jsonOrder[$key])) {
                        $jsonOrder[$key] = $order[$key];
                    }
                }
            }

            $jsonDetails['o_update_at'] = date('Y-m-d H:i:s');
            $this->orderModel->update($id, $jsonOrder);

            // 呼叫 OrderService 處理明細的增、改、刪
            $this->orderService->updateOrderDetails($id, $jsonDetails);

            $this->orderModel->db->transComplete();

            return $this->respondNoContent();
        } catch (Exception $e) {
            $this->orderModel->db->transRollback();
            log_message('error', $e->getMessage());
            return $this->fail('更新失敗');
        }
    }

    // 歷史紀錄
    public function history()
    {
        try {
            // 取得使用者ID (可從 GET 參數或 header 取得)
            $userId = $this->request->getHeaderLine('X-User-ID');
            
            if (!$userId) {
                return $this->fail('缺少使用者身份資訊');
            }

            // 取得使用者有權限的地點ID
            $userLocationIds = $this->userLocationModel->getUserLocationIds($userId);
            
            // 根據地點權限過濾訂單
            $orders = $this->orderModel->getByCompletedWithLocationFilter($userLocationIds, $userId);

            $data = [];
            foreach ($orders as $order) {
                $data[] = [
                    'o_id' => $order['o_id'],
                    'o_from_location' => $order['from_location_name'],
                    'o_to_location' => $order['to_location_name'],
                    'o_car_number' => $order['o_car_number']
                ];
            }
            return $this->respond($data);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('取得歷史紀錄失敗');
        }
    }
}
