<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LocationModel;

class LocationController extends BaseController
{
    private $locationModel;

    public function __construct()
    {
        $this->locationModel = new LocationModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $type = $this->request->getGet('type');

        // 將空字串轉換為 null，以便模型正確處理
        if ($type === '') {
            $type = null;
        }

        $data = $this->locationModel->getList($keyword, $type);
        return view('location/index', [
            'data' => $data,
            'keyword' => $keyword,
            'type' => $type
        ]);
    }

    // 新增
    public function create()
    {
        return view('location/form', ['isEdit' => false]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->locationModel->find($id);
        return view('location/form', ['isEdit' => true, 'data' => $data]);
    }

    // 儲存
    public function save()
    {
        $data = $this->request->getPost();
        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }

        if (empty($data['l_id'])) {
            $data['l_create_by'] = $userId;
        } else {
            $data['l_update_by'] = $userId;
            $data['l_update_at'] = date('Y-m-d H:i:s');
        }

        $this->locationModel->save($data);

        return redirect()->to('location');
    }

    // 刪除
    public function delete($id)
    {
        $this->locationModel->delete($id);
        return redirect()->to('location');
    }
}
