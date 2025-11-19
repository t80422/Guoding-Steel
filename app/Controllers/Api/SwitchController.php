<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SwitchModel;
use CodeIgniter\API\ResponseTrait;

class SwitchController extends BaseController
{
    use ResponseTrait;

    protected $switchModel;

    public function __construct()
    {
        $this->switchModel = new SwitchModel();
    }

    public function getSwitch()
    {
        $switch = $this->switchModel->find(1);
        return $this->respond($switch);
    }

    public function update()
    {
        try {
            $data = $this->request->getJSON(true);
            $this->switchModel->update(1, $data);
            return $this->respondNoContent();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('更新失敗');
        }
    }
}
