<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LocationModel;
use App\Models\OrderModel;
use Throwable;

class LocationController extends BaseController
{
    private $locationModel;
    private $orderModel;

    public function __construct()
    {
        $this->locationModel = new LocationModel();
        $this->orderModel = new OrderModel();
    }

    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $data = $this->locationModel->getList($filter, $page);

        $pagerData = [
            'currentPage' => $data['currentPage'],
            'totalPages' => $data['totalPages']
        ];

        return view('location/index', [
            'data' => $data['data'],
            'pager' => $pagerData,
            'filter' => $filter
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
        try {
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

            return redirect()->to(url_to('LocationController::index'));
        } catch (Throwable $th) {
            $redirectUrl = !empty($data['l_id'])
                ? url_to('LocationController::edit', $data['l_id'])
                : url_to('LocationController::create');

            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', $th->getMessage());
        }
    }

    // 刪除
    public function delete($id)
    {
        $this->locationModel->delete($id);
        return redirect()->to(url_to('LocationController::index'));
    }

    // 工地用料情況
    public function materialUsage($id)
    {
        $location = $this->locationModel->find($id);
        
        // 取得詳細用料情況（包含工地項目和產品明細）
        $materialData = $this->orderModel->getMaterialDetailsWithProjectsByLocation($id);
        
        return view('location/material_usage', [
            'location' => $location,
            'orders' => $materialData['orders'],
            'all_projects' => $materialData['all_projects'],
            'all_products' => $materialData['all_products']
        ]);
    }
}
