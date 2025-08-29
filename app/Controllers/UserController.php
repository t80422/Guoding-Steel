<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PositionModel;
use App\Models\UserLocationModel;
use App\Models\LocationModel;


class UserController extends BaseController
{
    private $userModel;
    private $positionModel;
    private $userLocationModel;
    private $locationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->positionModel = new PositionModel();
        $this->userLocationModel = new UserLocationModel();
        $this->locationModel = new LocationModel();
    }

    // 列表
    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $page = $this->request->getGet('page') ?? 1;

        $result = $this->userModel->getList($keyword, $page);

        $pagerData = [
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages']
        ];

        return view('user/index', [
            'data' => $result['data'],
            'pager' => $pagerData,
            'keyword' => $keyword
        ]);
    }

    // 新增
    public function create()
    {
        $positions = $this->positionModel->findAll();
        return view('user/form', ['isEdit' => false, 'positions' => $positions]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->userModel->find($id);
        $positions = $this->positionModel->findAll();
        return view('user/form', ['isEdit' => true, 'data' => $data, 'positions' => $positions]);
    }

    // 儲存
    public function save()
    {
        $data = $this->request->getPost();
        $rules = [
            'u_name' => 'required'
        ];

        $messages = [
            'u_name' => [
                'required' => '使用者名稱為必填項。',
            ]
        ];

        // 根據是新增還是編輯來設定密碼驗證規則
        $password = $data['u_password'];

        if (empty($data['u_id'])) {
            // 新增時，密碼和確認密碼為必填，且密碼需符合確認密碼
            $rules['u_password'] = 'required|matches[u_confirm_password]';
            $rules['u_confirm_password'] = 'required';
            $messages['u_password'] = [
                'required' => '密碼為必填項。',
                'matches' => '密碼與確認密碼不符。',
            ];
            $messages['u_confirm_password'] = [
                'required' => '確認密碼為必填項。',
            ];
        } else {
            // 編輯時，如果提供了密碼，則驗證密碼和確認密碼
            if (!empty($password)) {
                $rules['u_password'] = 'matches[u_confirm_password]';
                $rules['u_confirm_password'] = 'required';
                $messages['u_password'] = [
                    'matches' => '密碼與確認密碼不符。',
                ];
                $messages['u_confirm_password'] = [
                    'required' => '確認密碼為必填項。',
                ];
            }
        }

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = [
            'u_name' => $data['u_name'],
            'u_is_admin' => $this->request->getVar('u_is_admin') ?? 0,
            'u_is_readonly' => $this->request->getVar('u_is_readonly') ?? 0
        ];

        if (!empty($data['u_p_id'])) {
            $result['u_p_id'] = $data['u_p_id'];
        }

        // 只有在提供了密碼時才更新密碼
        if (!empty($password)) {
            $result['u_password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }

        if (!empty($data['u_id'])) {
            $result['u_id'] = $data['u_id'];
            $result['u_update_by'] = $userId;
            $result['u_update_at'] = date('Y-m-d H:i:s');
        }

        $this->userModel->save($result);

        return redirect()->to('user');
    }

    // 刪除
    public function delete($id)
    {
        $this->userModel->delete($id);
        return redirect()->to('user');
    }

    // 地點設定頁面
    public function locationSettings($userId)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('user')->with('error', '使用者不存在');
        }

        // 取得所有地點並按類型分組
        $allLocations = $this->locationModel->findAll();
        $groupedLocations = [
            '倉庫' => [],
            '工地' => []
        ];

        foreach ($allLocations as $location) {
            $typeName = $this->locationModel->getTypeName($location['l_type']);
            $groupedLocations[$typeName][] = $location;
        }

        // 取得使用者目前的地點權限
        $userLocationIds = $this->userLocationModel->getUserLocationIds($userId);

        return view('user/location_settings', [
            'user' => $user,
            'groupedLocations' => $groupedLocations,
            'userLocationIds' => $userLocationIds
        ]);
    }

    // 儲存地點設定
    public function saveLocationSettings()
    {
        $data = $this->request->getPost();
        $locationIds = $data['location_ids'] ?? [];

        $success = $this->userLocationModel->setUserLocations($data['u_id'], $locationIds);

        if ($success) {
            return redirect()->to('user');
        } else {
            return redirect()->back()->with('error', '地點權限設定失敗');
        }
    }
}
