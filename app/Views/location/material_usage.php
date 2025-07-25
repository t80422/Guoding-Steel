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
        <strong>工地名稱：</strong> <?= esc($location['l_name'] ?? '示例工地A') ?>
    </div>

    <!-- 用料統計表 -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th rowspan="2" class="text-center align-middle">車號</th>
                    <th rowspan="2" class="text-center align-middle">日期</th>
                    <th rowspan="2" class="text-center align-middle">倉庫</th>
                    <th rowspan="2" class="text-center align-middle">類型</th>
                    <?php if (!empty($all_projects)): ?>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php $productCount = count($all_products[$projectName] ?? []); ?>
                            <?php if ($productCount > 0): ?>
                                <th colspan="<?= $productCount ?>" class="text-center"><?= esc($projectName) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                <tr>
                    <?php if (!empty($all_projects)): ?>
                        <?php foreach ($all_projects as $projectName): ?>
                            <?php if (!empty($all_products[$projectName])): ?>
                                <?php foreach ($all_products[$projectName] as $productKey => $productInfo): ?>
                                    <th class="text-center" style="min-width: 80px;">
                                        <?= esc($productInfo['display_name']) ?>
                                    </th>
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
                    $totalCols += count($all_products[$projectName] ?? []);
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
                                        <td class="text-center">
                                            <?php 
                                            $productData = $order['projects'][$projectName][$productKey] ?? null;
                                            if ($productData && $productData['quantity'] > 0): 
                                            ?>
                                                <div class="fw-bold text-primary"><?= $productData['quantity'] ?></div>
                                                <small class="text-muted"><?= esc($productData['length']) ?></small>
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
                                    $totals[$projectName][$productKey] = 0;
                                }
                                $totals[$projectName][$productKey] += $productData['quantity'] * $multiplier;
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
                                    <td class="text-center">
                                        <?php 
                                        $total = $totals[$projectName][$productKey] ?? 0;
                                        if ($total != 0): 
                                        ?>
                                            <span class="<?= $total > 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $total > 0 ? '+' . $total : $total ?>
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