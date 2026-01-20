<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<style>
    /* 1. 表格容器優化 */
    .material-usage-wrapper {
        position: relative;
        max-height: 700px;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
    }

    /* 2. 基本 Sticky 設定 */
    .sticky-table {
        border-spacing: 0;
        border-collapse: separate; /* 重要：確保 sticky 邊框正確顯示 */
        margin-bottom: 0;
    }

    .sticky-table th, 
    .sticky-table td {
        background-color: #fff;
        z-index: 1;
        border-color: #dee2e6 !important;
    }

    /* 3. 左側固定欄位 (橫向) */
    .col-sticky-1 { position: sticky; left: 0; min-width: 120px; z-index: 10; }
    .col-sticky-2 { position: sticky; left: 120px; min-width: 110px; z-index: 10; }
    .col-sticky-3 { position: sticky; left: 230px; min-width: 150px; z-index: 10; }
    .col-sticky-4 { position: sticky; left: 380px; min-width: 80px; z-index: 10; }

    /* 4. 上方固定表頭 (縱向) */
    /* 這裡的高度偏移需要精確計算或使用 JS 動態計算，先給定預估值 */
    .sticky-table thead tr:nth-child(1) th { position: sticky; top: 0; z-index: 20; }
    .sticky-table thead tr:nth-child(2) th { position: sticky; top: 41px; z-index: 20; }
    .sticky-table thead tr:nth-child(3) th { position: sticky; top: 82px; z-index: 20; }

    /* 5. 下方固定表尾 (縱向) */
    .sticky-table tfoot tr td {
        position: sticky;
        bottom: 0;
        z-index: 20;
        background-color: #fff3cd !important; /* table-warning 顏色 */
        border-top: 2px solid #ffecb5 !important;
    }

    /* 6. 交集處 Z-Index 最高 */
    /* 左上角表頭：確保水平捲動時，產品欄位會從下方滑過 */
    .sticky-table thead th.col-sticky-1,
    .sticky-table thead th.col-sticky-2,
    .sticky-table thead th.col-sticky-3,
    .sticky-table thead th.col-sticky-4 {
        z-index: 100 !important;
        background-color: #EBF1EC !important;
    }
    /* 左下角表尾：確保總計列在水平捲動時也不會被穿透 */
    .sticky-table tfoot td.col-sticky-1,
    .sticky-table tfoot td.col-sticky-2,
    .sticky-table tfoot td.col-sticky-3,
    .sticky-table tfoot td.col-sticky-4 {
        z-index: 100 !important;
        background-color: #fff3cd !important;
    }

    /* 7. 視覺優化 */
    .col-sticky-4 {
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        border-right: 2px solid #dee2e6 !important;
    }
    
    .table-light th { 
        background-color: #EBF1EC !important; 
    }

    .sticky-table tbody tr:hover td { 
        background-color: #f8f9fa !important; 
    }

    /* 調整對齊與字體 */
    .table th { font-weight: 600; font-size: 0.9rem; }
    .table td { font-size: 0.9rem; }
</style>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">工地用料情況-<?= esc($location['l_name']) ?></h3>
        <a href="<?= url_to('LocationController::index') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> 回到地點管理
        </a>
    </div>

    <!-- 搜尋表單 -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fs-6 fw-bold text-muted">搜尋條件</h5>
                <button class="btn btn-sm btn-link text-decoration-none p-0" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#searchFormCollapse" 
                        aria-expanded="false" aria-controls="searchFormCollapse"
                        id="toggleSearchBtn">
                    <i class="bi bi-chevron-down fs-5" id="toggleIcon"></i>
                </button>
            </div>
        </div>
        <div class="collapse" id="searchFormCollapse">
            <div class="card-body">
            <form method="GET" action="<?= url_to('LocationController::materialUsage', $location['l_id']) ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label small">開始日期</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="<?= esc($searchParams['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label small">結束日期</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="<?= esc($searchParams['end_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label small">類型</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">全部</option>
                            <option value="<?= \App\Models\OrderModel::TYPE_IN_WAREHOUSE ?>"
                                <?= ($searchParams['type'] ?? '') == \App\Models\OrderModel::TYPE_IN_WAREHOUSE ? 'selected' : '' ?>>
                                進倉庫
                            </option>
                            <option value="<?= \App\Models\OrderModel::TYPE_OUT_WAREHOUSE ?>"
                                <?= ($searchParams['type'] ?? '') == \App\Models\OrderModel::TYPE_OUT_WAREHOUSE ? 'selected' : '' ?>>
                                出倉庫
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="keyword" class="form-label small">關鍵字</label>
                        <input type="text" class="form-control" id="keyword" name="keyword"
                            placeholder="車號或倉庫名稱" value="<?= esc($searchParams['keyword'] ?? '') ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-search me-1"></i> 搜尋
                        </button>
                        <a href="<?= url_to('LocationController::materialUsage', $location['l_id']) ?>" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-clockwise me-1"></i> 清除
                        </a>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <?php
    // 事前計算總欄位數與總計資料
    $totalCols = 4;
    foreach ($all_projects as $projectName) {
        $totalCols += count($all_products[$projectName] ?? []) * 2;
    }

    $totals = [];
    if (!empty($orders)) {
        foreach ($orders as $order) {
            $multiplier = ($order['is_increase'] ?? false) ? 1 : -1;
            foreach ($order['projects'] as $projectName => $products) {
                foreach ($products as $productKey => $productData) {
                    if (!isset($totals[$projectName][$productKey])) {
                        $totals[$projectName][$productKey] = ['quantity' => 0, 'length' => 0];
                    }
                    $totals[$projectName][$productKey]['quantity'] += (float)($productData['quantity'] ?? 0) * $multiplier;
                    $totals[$projectName][$productKey]['length'] += (float)($productData['length'] ?? 0) * $multiplier;
                }
            }
        }
    }
    ?>

    <!-- 用料統計表 -->
    <div class="material-usage-wrapper shadow-sm">
        <table class="table table-bordered table-hover align-middle sticky-table" style="white-space: nowrap;">
            <thead class="table-light">
                <tr>
                    <th rowspan="3" class="text-center align-middle col-sticky-1">車號</th>
                    <th rowspan="3" class="text-center align-middle col-sticky-2">日期</th>
                    <th rowspan="3" class="text-center align-middle col-sticky-3">倉庫</th>
                    <th rowspan="3" class="text-center align-middle col-sticky-4">類型</th>
                    <?php if (!empty($all_projects)): ?>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php $productCount = count($all_products[$projectName] ?? []); ?>
                            <?php if ($productCount > 0): ?>
                                <th colspan="<?= $productCount * 2 ?>" class="text-center border-bottom"><?= esc($projectName) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                <tr>
                    <?php if (!empty($all_projects)): ?>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php if (!empty($all_products[$projectName])): ?>
                                <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                    <th colspan="2" class="text-center border-bottom" style="min-width: 180px;">
                                        <?= esc($productInfo['display_name']) ?>
                                    </th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                <tr>
                    <?php if (!empty($all_projects)): ?>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php if (!empty($all_products[$projectName])): ?>
                                <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                    <th class="text-center border-end" style="min-width: 80px;">數量</th>
                                    <th class="text-center" style="min-width: 100px;">米數</th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="<?= $totalCols ?>" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                查無用料記錄
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="text-center col-sticky-1"><?= esc($order['vehicle_no']) ?></td>
                            <td class="text-center col-sticky-2"><?= esc($order['date']) ?></td>
                            <td class="col-sticky-3"><?= esc($order['warehouse']) ?></td>
                            <td class="text-center col-sticky-4"><?= esc($order['type']) ?></td>

                            <!-- 動態產品欄位 -->
                            <?php foreach ($all_projects as $projectName): ?>
                                <?php if (!empty($all_products[$projectName])): ?>
                                    <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                        <?php
                                        $productData = $order['projects'][$projectName][$productKey] ?? null;
                                        $isIncrease = $order['is_increase'] ?? false;
                                        $colorClass = $isIncrease ? 'text-success' : 'text-danger';
                                        $prefix = $isIncrease ? '+' : '-';
                                        ?>
                                        <td class="text-center border-end">
                                            <?php if ($productData && (float)($productData['quantity'] ?? 0) > 0): ?>
                                                <span class="fw-bold <?= $colorClass ?>">
                                                    <?= $prefix ?><?= (float)($productData['quantity'] ?? 0) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted opacity-50">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($productData && (float)($productData['quantity'] ?? 0) > 0): ?>
                                                <span class="<?= $colorClass ?>">
                                                    <?= $prefix ?><?= number_format((float)($productData['length'] ?? 0), 2) ?>m
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted opacity-50">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

            <?php if (!empty($orders)): ?>
                <tfoot class="table-warning fw-bold">
                    <tr>
                        <td colspan="4" class="text-center col-sticky-1 col-sticky-2 col-sticky-3 col-sticky-4" style="left: 0;">總計</td>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php if (!empty($all_products[$projectName])): ?>
                                <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                    <td colspan="2" class="text-center">
                                        <?php
                                        $totalQty = (float)($totals[$projectName][$productKey]['quantity'] ?? 0);
                                        $totalLength = (float)($totals[$projectName][$productKey]['length'] ?? 0);
                                        if ($totalQty != 0 || $totalLength != 0):
                                        ?>
                                            <span class="<?= $totalQty > 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $totalQty > 0 ? '+' . $totalQty : $totalQty ?> | <?= number_format($totalLength, 2) ?>m
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted opacity-50">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchFormCollapse = document.getElementById('searchFormCollapse');
        const toggleIcon = document.getElementById('toggleIcon');
        
        searchFormCollapse.addEventListener('show.bs.collapse', function() {
            toggleIcon.classList.replace('bi-chevron-down', 'bi-chevron-up');
        });
        
        searchFormCollapse.addEventListener('hide.bs.collapse', function() {
            toggleIcon.classList.replace('bi-chevron-up', 'bi-chevron-down');
        });
    });
</script>

<?= $this->endSection() ?>