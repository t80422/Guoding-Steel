<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Libraries\OrderService;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;

class OrderServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $orderService;
    protected $orderModel;
    protected $orderDetailModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();

        // 插入測試用的產品和次要類別 (OrderDetailModel 依賴這些)
        $db = \CodeIgniter\Database\Config::connect();
        $db->table('locations')->insertBatch([
            ['l_id' => 1, 'l_name' => '地點A'],
            ['l_id' => 2, 'l_name' => '地點B'],
        ]);
        $db->table('users')->insertBatch([
            ['u_id' => 1, 'u_name' => '測試用戶', 'u_account' => 'testuser', 'u_password' => 'testpass', 'u_level' => 1],
        ]);
        $db->table('major_categories')->insertBatch([
            ['mac_id' => 1, 'mac_name' => '主要類別1'],
            ['mac_id' => 2, 'mac_name' => '主要類別2'],
        ]);
        $db->table('minor_categories')->insertBatch([
            ['mic_id' => 1, 'mic_name' => '次要類別1', 'mic_mac_id' => 1],
            ['mic_id' => 2, 'mic_name' => '次要類別2', 'mic_mac_id' => 1],
        ]);
        $db->table('products')->insertBatch([
            ['pr_id' => 1, 'pr_name' => '產品A', 'pr_mic_id' => 1],
            ['pr_id' => 2, 'pr_name' => '產品B', 'pr_mic_id' => 1],
            ['pr_id' => 3, 'pr_name' => '產品C', 'pr_mic_id' => 2],
            ['pr_id' => 4, 'pr_name' => '產品D', 'pr_mic_id' => 2], // 添加一個新產品用於新增情境
        ]);
        
        // 準備測試用的訂單資料
        $this->orderModel->insert([
            'o_type' => 0,
            'o_from_location' => 1,
            'o_to_location' => 2,
            'o_date' => date('Y-m-d'),
            'o_car_number' => 'ABC-123',
            'o_driver_phone' => '0912345678',
            'o_loading_time' => '08:00:00',
            'o_unloading_time' => '17:00:00',
            'o_gps' => '123.45,67.89',
            'o_oxygen' => 10,
            'o_acetylene' => 5,
            'o_remark' => '測試訂單',
            'o_driver_signature' => null,
            'o_from_signature' => null,
            'o_to_signature' => null,
            'o_create_by' => 1,
            'o_update_by' => 1,
            'o_status' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testUpdateOrderDetails_ComprehensiveScenario()
    {
        $orderId = 1;

        // 1. 準備初始明細數據 (這些會被部分更新，部分刪除)
        $initialDetails = [
            ['od_o_id' => $orderId, 'od_pr_id' => 1, 'od_qty' => 10, 'od_length' => 100, 'od_weight' => 50],  // 將被更新
            ['od_o_id' => $orderId, 'od_pr_id' => 2, 'od_qty' => 20, 'od_length' => 200, 'od_weight' => 100], // 將被刪除
        ];
        $this->orderDetailModel->insertBatch($initialDetails);

        $existingDetailsInDb = $this->orderDetailModel->where('od_o_id', $orderId)->findAll();
        $detailIdToUpdate = $existingDetailsInDb[0]['od_id'];
        $detailIdToDelete = $existingDetailsInDb[1]['od_id'];

        // 2. 構建要傳入 OrderService 的新明細數據
        // 包括：一個更新、一個新增
        $newDetailsFromFrontend = [
            // 更新現有明細 (使用 $detailIdToUpdate)
            ['od_id' => $detailIdToUpdate, 'od_pr_id' => 1, 'od_qty' => 15, 'od_length' => 150, 'od_weight' => 75],
            // 新增一個明細 (沒有 od_id)
            ['od_id' => null, 'od_pr_id' => 4, 'od_qty' => 25, 'od_length' => 250, 'od_weight' => 125],
        ];

        // 3. 執行 updateOrderDetails 方法
        $this->orderService->updateOrderDetails($orderId, $newDetailsFromFrontend);

        // 4. 驗證結果
        $finalDetailsInDb = $this->orderDetailModel->where('od_o_id', $orderId)->findAll();

        // 驗證總數量：初始 3 個，刪除 1 個，新增 1 個，更新 1 個 => 1 (更新) + 1 (新增) = 2 個
        $this->assertCount(2, $finalDetailsInDb, '資料庫中的明細數量不正確。');

        // 驗證更新後的明細
        $foundUpdated = false;
        foreach ($finalDetailsInDb as $detail) {
            if ($detail['od_id'] == $detailIdToUpdate) {
                $this->assertEquals(15, $detail['od_qty'], '更新後的明細數量不正確。');
                $this->assertEquals(75, $detail['od_weight'], '更新後的明細重量不正確。');
                $foundUpdated = true;
                break;
            }
        }
        $this->assertTrue($foundUpdated, '更新後的明細未找到。');

        // 驗證新增的明細
        $foundNew = false;
        foreach ($finalDetailsInDb as $detail) {
            if ($detail['od_pr_id'] == 4 && $detail['od_qty'] == 25) {
                $foundNew = true;
                break;
            }
        }
        $this->assertTrue($foundNew, '新增的明細未找到。');

        // 驗證被刪除的明細 (detailIdToDelete) 是否不存在
        $deletedDetail = $this->orderDetailModel->find($detailIdToDelete);
        $this->assertNull($deletedDetail, '應被刪除的明細仍然存在。');
    }
}
