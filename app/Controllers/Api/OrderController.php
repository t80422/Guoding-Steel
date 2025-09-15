<?php

namespace App\Controllers\Api;

use App\Models\OrderModel;
use App\Models\OrderDetailModel;
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

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->orderService = new OrderService();
    }

    // 新增
    public function create()
    {
        try {
            $data = $this->request->getPost();
            $files = $this->request->getFiles();

            $jsonOrder = json_decode($data['order'], true);
            $jsonDetails = json_decode($data['details'], true);

            // 使用 OrderService 統一處理
            $orderId = $this->orderService->createOrder($jsonOrder, $jsonDetails, $files);

            return $this->respondCreated(['order_id' => $orderId]);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('新增失敗:' . $e->getMessage());
        }
    }

    // 列表
    public function index()
    {
        try {
            // 根據地點權限過濾訂單
            $orders = $this->orderModel->getByInProgress();

            $data = [];
            foreach ($orders as $order) {
                $data[] = [
                    'o_id' => $order['o_id'],
                    'from_location_id' => $order['o_from_location'],
                    'o_from_location' => $order['from_location_name'],
                    'to_location_id' => $order['o_to_location'],
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
                foreach ($this->orderService->getSupportedKeys() as $key) {
                    if (!empty($order[$key])) {
                        $order[$key] = url_to('OrderController::serveSignature', $order[$key]);
                    } else {
                        $order[$key] = null; // 確保如果沒有檔案，欄位值為 null
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
        try {
            $userId = $this->request->getHeaderLine('X-User-ID');
            if (empty($userId)) {
                return $this->fail('缺少使用者識別資訊，請重新登入後再試。');
            }

            $data = $this->request->getPost();
            $files = $this->request->getFiles();

            $jsonOrder = json_decode($data['order'], true);
            $jsonDetails = json_decode($data['details'], true);

            // 使用 OrderService 統一處理
            $success = $this->orderService->updateOrder((int)$id, $jsonOrder, $jsonDetails, $files, (int)$userId);

            if ($success) {
                return $this->respondNoContent();
            } else {
                return $this->fail('更新失敗');
            }
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('更新失敗');
        }
    }

    // 歷史紀錄
    public function history()
    {
        try {
            // 根據地點權限過濾訂單
            $orders = $this->orderModel->getByCompleted();

            $data = [];
            foreach ($orders as $order) {
                $data[] = [
                    'o_id' => $order['o_id'],
                    'from_location_id' => $order['o_from_location'],
                    'to_location_id' => $order['o_to_location'],
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
