<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RentalModel;
use App\Libraries\FileManager;

class RentalController extends BaseController
{
    private $rentalModel;

    const UPLOAD_PATH = WRITEPATH . 'uploads/rentals/';

    public function __construct()
    {
        $this->rentalModel = new RentalModel();
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
}
