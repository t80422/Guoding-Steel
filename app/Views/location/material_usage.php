<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">工地用料情況</h3>
        <a href="<?= url_to('LocationController::index') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> 回到地點管理
        </a>
    </div>

    <!-- 工地資訊 -->
    <div class="alert alert-info mb-4">
        <strong>工地名稱：</strong> <?= esc($location['l_name']) ?>
    </div>

    <!-- 搜尋表單 -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">搜尋條件</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= url_to('LocationController::materialUsage', $location['l_id']) ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">開始日期</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="<?= esc($searchParams['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">結束日期</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="<?= esc($searchParams['end_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">類型</label>
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
                        <label for="keyword" class="form-label">關鍵字</label>
                        <input type="text" class="form-control" id="keyword" name="keyword"
                            placeholder="車號或倉庫名稱" value="<?= esc($searchParams['keyword'] ?? '') ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i> 搜尋
                        </button>
                        <a href="<?= url_to('LocationController::materialUsage', $location['l_id']) ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> 清除
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 用料統計表 -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th rowspan="3" class="text-center align-middle">車號</th>
                    <th rowspan="3" class="text-center align-middle">日期</th>
                    <th rowspan="3" class="text-center align-middle">倉庫</th>
                    <th rowspan="3" class="text-center align-middle">類型</th>
                    <?php if (!empty($all_projects)): ?>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php $productCount = count($all_products[$projectName] ?? []); ?>
                            <?php if ($productCount > 0): ?>
                                <th colspan="<?= $productCount * 2 ?>" class="text-center"><?= esc($projectName) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                <tr>
                    <?php if (!empty($all_projects)): ?>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php if (!empty($all_products[$projectName])): ?>
                                <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                    <th colspan="2" class="text-center" style="min-width: 120px;">
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
                                    <th class="text-center border-end" style="min-width: 60px;">數量</th>
                                    <th class="text-center" style="min-width: 60px;">米數</th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // 計算總欄位數（固定欄位 + 動態產品欄位）
                $totalCols = 4; // 車號、日期、倉庫、類型
                foreach ($all_projects as $projectName) {
                    $totalCols += count($all_products[$projectName] ?? []) * 2; // 每個產品佔用2欄（數量+米數）
                }
                ?>

                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="<?= $totalCols ?>" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                查無用料記錄
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="text-center"><?= esc($order['vehicle_no']) ?></td>
                            <td class="text-center"><?= esc($order['date']) ?></td>
                            <td><?= esc($order['warehouse']) ?></td>
                            <td class="text-center"><?= esc($order['type']) ?></td>

                            <!-- 動態產品欄位 -->
                            <?php foreach ($all_projects as $projectName): ?>
                                <?php if (!empty($all_products[$projectName])): ?>
                                    <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                        <?php
                                        $productData = $order['projects'][$projectName][$productKey] ?? null;
                                        ?>
                                        <td class="text-center border-end">
                                            <?php if ($productData && (float)($productData['quantity'] ?? 0) > 0): ?>
                                                <span class="fw-bold text-primary"><?= (float)($productData['quantity'] ?? 0) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($productData && (float)($productData['quantity'] ?? 0) > 0): ?>
                                                <span class="text-muted small"><?= number_format((float)($productData['length'] ?? 0), 2) ?>m</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- 總計行 -->
                <?php if (!empty($orders)): ?>
                    <?php
                    // 計算各產品總計 (進倉庫 - 出倉庫)
                    $totals = [];
                    foreach ($orders as $order) {
                        $multiplier = ($order['type'] == '進倉庫') ? 1 : -1;

                        foreach ($order['projects'] as $projectName => $products) {
                            foreach ($products as $productKey => $productData) {
                                if (!isset($totals[$projectName])) {
                                    $totals[$projectName] = [];
                                }
                                if (!isset($totals[$projectName][$productKey])) {
                                    $totals[$projectName][$productKey] = [
                                        'quantity' => 0,
                                        'length' => 0
                                    ];
                                }
                                $quantity = (float)($productData['quantity'] ?? 0);
                                $length = (float)($productData['length'] ?? 0);

                                $totals[$projectName][$productKey]['quantity'] += $quantity * $multiplier;
                                $totals[$projectName][$productKey]['length'] += ($quantity * $length) * $multiplier;
                            }
                        }
                    }
                    ?>
                    <tr class="table-warning fw-bold">
                        <td colspan="4" class="text-center">總計</td>

                        <!-- 動態總計欄位 -->
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php if (!empty($all_products[$projectName])): ?>
                                <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                    <td colspan="2" class="text-center">
                                        <?php
                                        $totalQty = (float)($totals[$projectName][$productKey]['quantity'] ?? 0);
                                        $totalLength = (float)($totals[$projectName][$productKey]['length'] ?? 0);

                                        if ($totalQty != 0 || $totalLength != 0):
                                        ?>
                                            <span class="<?= $totalQty > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                                <?= $totalQty > 0 ? '+' . $totalQty : $totalQty ?> | <?= number_format($totalLength, 2) ?>m
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?= $this->endSection() ?>