<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Libraries\FileManager;
use App\Models\RentalModel;
use Exception;

// 租賃單
class RentalController extends Controller
{
    use ResponseTrait;

    private $rentalModel;
    private $permissionService;

    const UPLOAD_PATH = WRITEPATH . 'uploads/rentals/';

    public function __construct()
    {
        $this->rentalModel = new RentalModel();
        $this->permissionService = new \App\Services\PermissionService();
    }

    public function create()
    {
        $userId = $this->request->getPost('userId');

        // 檢查新增權限
        $permissionCheck = $this->permissionService->validateCreatePermission(false, $userId);
        if ($permissionCheck['status'] === 'error') {
            return $this->failForbidden($permissionCheck['message']);
        }

        $this->rentalModel->db->transStart();
        $fileManager = new FileManager(self::UPLOAD_PATH);
        $newFileNames = [];

        try {
            $files = $this->request->getFiles();
            $data = $this->request->getPost();

            $fileKeys = ['r_front_image', 'r_side_image', 'r_doc_image'];
            $newFileNames = $fileManager->uploadFiles($fileKeys, $files);

            $result = [
                'r_memo' => $data['r_memo'],
                'r_front_image' => $newFileNames['r_front_image'],
                'r_side_image' => $newFileNames['r_side_image'],
                'r_doc_image' => $newFileNames['r_doc_image'],
                'r_create_by' => $data['userId'],
            ];

            $this->rentalModel->insert($result);
            $this->rentalModel->db->transComplete();

            return $this->respondCreated(null);
        } catch (Exception $e) {
            $this->rentalModel->db->transRollback();
            if (!empty($newFileNames)) {
                $fileManager->deleteFiles($newFileNames);
            }
            log_message('error', $e->getMessage());
            return $this->fail('新增失敗');
        }
    }
}
