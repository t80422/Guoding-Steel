<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MachineRepairModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class MachineRepairController extends BaseController
{
    use ResponseTrait;

    protected $machineRepairModel;

    public function __construct()
    {
        $this->machineRepairModel = new MachineRepairModel();
    }

    // 列表
    public function index()
    {
        try {
            $filter = $this->request->getGet();
            $datas = $this->machineRepairModel->getList($filter, 1, false);
            $result = [];
            foreach ($datas['data'] as $data) {
                $result[] = [
                    'id' => $data['mr_id'],
                    'machine' => $data['m_name'],
                    'date' => $data['mr_date'],
                    'status' => MachineRepairModel::getStatusName($data['mr_status']),
                    'memo' => $data['mr_memo']
                ];
            }
            return $this->respond($result);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('取得列表失敗');
        }
    }
}
