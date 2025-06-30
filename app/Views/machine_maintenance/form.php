<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>機械保養</h2>
    </div>

    <!-- 顯示錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('MachineMaintenanceController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="mm_id" value="<?= $data['mm_id'] ?? old('mm_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="mm_date" class="form-label">日期</label>
            <input type="date" class="form-control" id="mm_date" name="mm_date" value="<?= old('mm_date', $data['mm_date'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="mm_m_id" class="form-label">機械</label>
            <select class="form-select" id="mm_m_id" name="mm_m_id" required>
                <option value="">請選擇</option>
                <?php foreach ($machines as $machine): ?>
                    <option value="<?= $machine['m_id'] ?>" <?= old('mm_m_id', $data['mm_m_id'] ?? '') == $machine['m_id'] ? 'selected' : '' ?>><?= $machine['m_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="mm_last_km" class="form-label">上次公里數</label>
            <input type="number" class="form-control" id="mm_last_km" name="mm_last_km" value="<?= old('mm_last_km', $data['mm_last_km'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="mm_next_km" class="form-label">下次公里數</label>
            <input type="number" class="form-control" id="mm_next_km" name="mm_next_km" value="<?= old('mm_next_km', $data['mm_next_km'] ?? '') ?>" required>
        </div>

        <?php if ($isEdit): ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">建立者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['creator'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">建立時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['mm_create_at'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">更新者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['updater'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">更新時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['mm_update_at'] ?? 'N/A') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="<?= url_to('MachineMaintenanceController::index') ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>

</div>

<?= $this->endSection() ?>