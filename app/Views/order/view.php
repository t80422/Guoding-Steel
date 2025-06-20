<?= $this->extend('_layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">訂單檢視</h2>
        <div>
            <a href="<?= url_to('OrderController::index') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>返回列表</a>
        </div>
    </div>
    <!-- 訂單資訊 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>訂單資訊</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">日期</p>
                    <p class="mb-0"><?= esc($order['o_date'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">類型</p>
                    <p class="mb-0"><?= esc($order['o_type'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">出發地</p>
                    <p class="mb-0"><?= esc($order['from_location_name'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">目的地</p>
                    <p class="mb-0"><?= esc($order['to_location_name'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">車號</p>
                    <p class="mb-0"><?= esc($order['o_car_number'] ?? '無') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">司機電話</p>
                    <p class="mb-0"><?= esc($order['o_driver_phone'] ?? '無') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">上料時間</p>
                    <p class="mb-0"><?= esc($order['o_load_time'] ?? '無') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">下料時間</p>
                    <p class="mb-0"><?= esc($order['o_unload_time'] ?? '無') ?></p>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">GPS 座標或連結</p>
                    <p class="mb-0"><?= esc($order['o_gps_link'] ?? '無') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">氧氣</p>
                    <p class="mb-0"><?= esc($order['o_oxygen'] ?? '無') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">乙炔</p>
                    <p class="mb-0"><?= esc($order['o_acetylene'] ?? '無') ?></p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <p class="text-muted fw-semibold mb-1">備註</p>
                    <p class="mb-0"><?= esc($order['o_remark'] ?? '無') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- 訂單明細 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>訂單明細</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">品名</th>
                            <th>長度</th>
                            <th>重量</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orderDetails)): ?>
                            <?php foreach ($orderDetails as $detail): ?>
                                <tr>
                                    <td class="ps-3"><?= esc($detail['pr_name'] ?? '') ?></td>
                                    <td><?= esc($detail['od_length'] ?? '0') ?></td>
                                    <td><?= esc($detail['od_weight'] ?? '0') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">無訂單明細</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 簽名 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-signature me-2"></i>簽名</h5>
        </div>
        <div class="card-body">
            <div class="row text-center gy-4">
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">司機簽名</h6>
                    <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                        <?php if (!empty($order['o_driver_signature'])): ?>
                            <img src="<?= url_to('signature_image', esc($order['o_driver_signature'])) ?>" alt="司機簽名" class="img-fluid" style="max-height: 180px; object-fit: contain;">
                        <?php else: ?>
                            <p class="mb-0 text-muted">無簽名</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">出發地簽名</h6>
                    <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                        <?php if (!empty($order['o_from_signature'])): ?>
                            <img src="<?= url_to('signature_image', esc($order['o_from_signature'])) ?>" alt="出發地簽名" class="img-fluid" style="max-height: 180px; object-fit: contain;">
                        <?php else: ?>
                            <p class="mb-0 text-muted">無簽名</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">目的地簽名</h6>
                    <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                        <?php if (!empty($order['o_to_signature'])): ?>
                            <img src="<?= url_to('signature_image', esc($order['o_to_signature'])) ?>" alt="目的地簽名" class="img-fluid" style="max-height: 180px; object-fit: contain;">
                        <?php else: ?>
                            <p class="mb-0 text-muted">無簽名</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 系統資訊 -->
    <div class="text-center text-muted small mt-4">
        <p class="mb-1">
            建立: <?= esc($order['create_name'] ?? 'N/A') ?> 於 <?= esc($order['o_create_at'] ?? 'N/A') ?>
        </p>
        <p class="mb-0">
            更新: <?= esc($order['update_name'] ?? 'N/A') ?> 於 <?= esc($order['o_update_at'] ?? 'N/A') ?>
        </p>
    </div>
</div>

<?= $this->endSection() ?>