<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PositionModel;

// 職位
class PositionController extends BaseController
{
    private $positionModel;

    public function __construct()
    {
        $this->positionModel = new PositionModel();
    }

    // 列表
    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $data = $this->positionModel->getList($keyword);
        return view('position/index', ['data' => $data]);
    }

    // 新增
    public function create()
    {
        return view('position/form', ['isEdit' => false]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->positionModel->find($id);
        return view('position/form', ['isEdit' => true, 'data' => $data]);
    }

    // 儲存
    public function save()
    {
        $data = [
            'p_name' => $this->request->getPost('p_name'),
        ];
        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }
        
        if ($data['p_id']) {
            $data['p_update_by'] = $userId;
            $data['p_update_at'] = date('Y-m-d H:i:s');
        } else {
            $data['p_create_by'] = $userId;
        }

        $this->positionModel->save($data);

        return redirect()->to('position');
    }

    // 刪除
    public function delete($id)
    {
        $this->positionModel->delete($id);
        return redirect()->to('position');
    }
}
