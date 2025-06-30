<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\MachineMaintenanceModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class MachineMaintenanceController extends Controller
{
    use ResponseTrait;

    protected $machineMaintenanceModel;

    public function __construct()
    {
        $this->machineMaintenanceModel = new MachineMaintenanceModel();
    }

    // 列表
    public function index()
    {
        try {
            $filter = $this->request->getGet();
            $datas = $this->machineMaintenanceModel->getList($filter, 1, false);
            $result = [];
            foreach ($datas as $data) {
                $result[] = [
                    'mm_id' => $data['mm_id'],
                    'mm_date' => $data['mm_date'],
                    'mm_last_km' => $data['mm_last_km'],
                    'mm_next_km' => $data['mm_next_km'],
                    'm_name' => $data['m_name']
                ];
            }
            return $this->respond($result);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('取得列表失敗');
        }
    }
}
