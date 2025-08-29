<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RentalModel;
use App\Models\RentalOrderModel;
use App\Models\RentalOrderDetailModel;
use App\Models\LocationModel;
use App\Models\GpsModel;
use App\Models\ProductModel;
use App\Models\ManufacturerModel;
use App\Libraries\FileManager;
use App\Services\ManufacturerInventoryService;
use App\Services\PermissionService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class RentalController extends BaseController
{
    private $rentalModel;
    protected $rentalOrderModel;
    protected $rentalOrderDetailModel;
    protected $locationModel;
    protected $gpsModel;
    protected $productModel;
    protected $manufacturerInventoryService;
    protected $manufacturerModel;
    protected $permissionService;

    const UPLOAD_PATH = WRITEPATH . 'uploads/rentals/';

    public function __construct()
    {
        $this->rentalModel = new RentalModel();
        $this->rentalOrderModel = new RentalOrderModel();
        $this->rentalOrderDetailModel = new RentalOrderDetailModel();
        $this->locationModel = new LocationModel();
        $this->gpsModel = new GpsModel();
        $this->productModel = new ProductModel();
        $this->manufacturerInventoryService = new ManufacturerInventoryService();
        $this->manufacturerModel = new ManufacturerModel();
        $this->permissionService = new PermissionService();
    }

    // 列表
    public function index()
    {
        $filter = [
            'r_memo' => $this->request->getGet('memo')
        ];

        $data = $this->rentalModel->getList($filter);
        return view('rental', ['data' => $data, 'filter' => $filter]);
    }

    // 提供圖片檔案存取
    public function image($filename)
    {
        $filepath = self::UPLOAD_PATH . $filename;

        if (!file_exists($filepath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $file = new \CodeIgniter\Files\File($filepath);
        $mimeType = $file->getMimeType();

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', filesize($filepath))
            ->setBody(file_get_contents($filepath));
    }

    // 刪除
    public function delete($id)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $fileManager = new FileManager(self::UPLOAD_PATH);
        $rental = $this->rentalModel->find($id);
        $fileManager->deleteFiles([
            $rental['r_front_image'],
            $rental['r_side_image'],
            $rental['r_doc_image']
        ]);
        $this->rentalModel->delete($id);

        return redirect()->to(base_url('rental'));
    }

    // 租賃訂單列表
    public function index_order()
    {
        $keyword = $this->request->getGet('keyword');
        $rentalDateStart = $this->request->getGet('rental_date_start');
        $rentalDateEnd = $this->request->getGet('rental_date_end');
        $type = $this->request->getGet('type');

        $data = $this->rentalOrderModel->getList($keyword, $rentalDateStart, $rentalDateEnd, $type);

        return view('rental_order/index', [
            'data' => $data,
            'keyword' => $keyword,
            'rental_date_start' => $rentalDateStart,
            'rental_date_end' => $rentalDateEnd,
            'type' => $type
        ]);
    }

    // 新增租賃訂單
    public function createOrder()
    {
        $gpsOptions = $this->gpsModel->getOptions();
        $manufacturerOptions = $this->manufacturerModel->getDropdown();
        $locationOptions = $this->locationModel->getConstructionSiteDropdown();
        $data = [
            'gpsOptions' => $gpsOptions,
            'manufacturerOptions' => $manufacturerOptions,
            'locationOptions' => $locationOptions
        ];

        return view('rental_order/form', ['data' => $data, 'isEdit' => false]);
    }

    // 編輯租賃訂單
    public function editOrder($id = null)
    {
        $rental = $this->rentalOrderModel->getDetail($id);

        if (!$rental) {
            throw new PageNotFoundException('無法找到該租賃訂單: ' . $id);
        }

        $rentalDetails = $this->rentalOrderDetailModel->getDetailByRentalId($id);
        $gpsOptions = $this->gpsModel->getOptions();
        $manufacturerOptions = $this->manufacturerModel->getDropdown();
        $locationOptions = $this->locationModel->getConstructionSiteDropdown();
        $data = [
            'rental' => $rental,
            'rentalDetails' => $rentalDetails,
            'gpsOptions' => $gpsOptions,
            'manufacturerOptions' => $manufacturerOptions,
            'locationOptions' => $locationOptions
        ];

        return view('rental_order/form', ['data' => $data, 'isEdit' => true]);
    }

    // 保存租賃訂單
    public function saveOrder()
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->rentalOrderModel->db->transStart();

        try {
            $userId = session()->get('userId');

            if (!$userId) {
                return redirect()->to(url_to('AuthController::index'))
                    ->with('error', '請先登入！');
            }

            $data = $this->request->getPost();

            if (isset($data['ro_id']) && !empty($data['ro_id'])) {
                // 更新 - 取得修改前的租賃資料用於庫存更新
                $rentalId = $data['ro_id'];
                $oldRental = $this->rentalOrderModel->find($rentalId);
                $oldRentalDetails = $this->rentalOrderDetailModel->getByRentalId($rentalId);

                $data['ro_update_by'] = $userId;
                $data['ro_update_at'] = date('Y-m-d H:i:s');

                $this->rentalOrderModel->save($data);
                $this->updateRentalDetails($data['ro_id'], $data['details']);

                // 更新庫存
                $this->manufacturerInventoryService->updateInventoryForRental($rentalId, 'UPDATE', $oldRental, $oldRentalDetails);
            } else {
                // 新增
                $data['ro_create_by'] = $userId;
                $roId = $this->rentalOrderModel->insert($data);
                $this->updateRentalDetails($roId, $data['details']);
                $this->manufacturerInventoryService->updateInventoryForRental($roId, 'CREATE');
            }

            $this->rentalOrderModel->db->transComplete();
            return redirect()->to(url_to('RentalController::index_order'));
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $this->rentalOrderModel->db->transRollback();
            $redirectUrl = isset($data['ro_id']) && !empty($data['ro_id'])
                ? url_to('RentalController::editOrder', $data['ro_id'])
                : url_to('RentalController::createOrder');

            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // 取得租賃訂單明細（提供給項目數量表載入）
    public function getDetail($id)
    {
        $details = $this->rentalOrderDetailModel->getDetailByRentalId((int)$id);
        return $this->response->setJSON($details);
    }

    /**
     * 更新租賃明細
     *
     * @param int $rentalId 租賃 ID
     * @param array $newDetails 前端傳來的最新明細資料
     * @throws Exception
     */
    private function updateRentalDetails(int $roId, array $newDetails)
    {
        try {
            // 取得現有的明細
            $existingDetails = $this->rentalOrderDetailModel->getByRentalId($roId);
            $existingDetailIds = array_column($existingDetails, 'rod_id');

            $newDetailIds = array_column($newDetails, 'rod_id');

            $toInsert = [];
            $toUpdate = [];
            $toDeleteIds = [];

            // 識別要新增或更新的明細
            foreach ($newDetails as $detail) {
                // 確保每個明細都有 rod_ro_id
                $detail['rod_ro_id'] = $roId;

                if (empty($detail['rod_id'])) {
                    // 新增的明細，沒有 rod_id
                    $toInsert[] = $detail;
                } else if (in_array($detail['rod_id'], $existingDetailIds)) {
                    // 存在的明細，需要更新
                    $toUpdate[] = $detail;
                }
            }

            // 識別要刪除的明細
            foreach ($existingDetailIds as $existingId) {
                if (!in_array($existingId, $newDetailIds)) {
                    $toDeleteIds[] = $existingId;
                }
            }

            // 執行刪除操作
            if (!empty($toDeleteIds)) {
                $this->rentalOrderDetailModel->delete($toDeleteIds);
            }

            // 執行更新操作
            if (!empty($toUpdate)) {
                // updateBatch 需要指定用哪個欄位來匹配
                $this->rentalOrderDetailModel->updateBatch($toUpdate, 'rod_id');
            }

            // 執行新增操作
            if (!empty($toInsert)) {
                $this->rentalOrderDetailModel->insertBatch($toInsert);
            }
        } catch (Exception $e) {
            throw $e; // 重新拋出異常，讓控制器捕獲並處理
        }
    }

    // 刪除租賃訂單
    public function deleteOrder($id = null)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->rentalOrderModel->db->transStart();
        try {
            $rentalOrder = $this->rentalOrderModel->find($id);
            if (!$rentalOrder) {
                throw new PageNotFoundException('無法找到該租賃訂單: ' . $id);
            }

            // 更新庫存 (在實際刪除前)
            $this->manufacturerInventoryService->updateInventoryForRental($id, 'DELETE');

            // 刪除租賃明細
            $this->rentalOrderDetailModel->where('rod_ro_id', $id)->delete();

            // 刪除租賃訂單
            $this->rentalOrderModel->delete($id);

            $this->rentalOrderModel->db->transComplete();
            return redirect()->to(url_to('RentalController::index_order'))->with('success', '刪除成功');
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $this->rentalOrderModel->db->transRollback();
            return redirect()->to(url_to('RentalController::index_order'))->with('error', '刪除失敗');
        }
    }
}
