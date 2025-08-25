<?php

namespace App\Controllers\Api;

use App\Models\UserLocationModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class UserLocationController extends Controller
{
    use ResponseTrait;

    private $userLocationModel;

    public function __construct()
    {
        $this->userLocationModel = new UserLocationModel();
    }

    public function index()
    {
        $userId = $this->request->getHeaderLine('X-User-ID');
        $userLocations = $this->userLocationModel->getUserLocations($userId);
        return $this->response->setJSON($userLocations);
    }
}
