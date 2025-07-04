<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>廠商</h2>
    </div>

    <!-- 顯示錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('ManufacturerController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="ma_id" value="<?= $data['ma_id'] ?? old('ma_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="ma_name" class="form-label">廠商名稱</label>
            <input type="text" class="form-control" id="ma_name" name="ma_name" value="<?= old('ma_name', $data['ma_name'] ?? '') ?>" required>
        </div>

        <?php if ($isEdit): ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">建立者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['creator'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">建立時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['ma_create_at'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">更新者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['updater'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">更新時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['ma_update_at'] ?? 'N/A') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="<?= url_to('ManufacturerController::index') ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>

</div>

<?= $this->endSection() ?>