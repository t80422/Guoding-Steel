<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>施工項目</h2>
    </div>

    <!-- 錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('ProjectItemController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="pi_id" value="<?= $data['pi_id'] ?? old('pi_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="piName" class="form-label">名稱</label>
            <input type="text" class="form-control" name="pi_name" value="<?= old('pi_name', $data['pi_name'] ?? '') ?>" required>
        </div>

        <?php if ($isEdit): ?>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label text-muted">建立者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['creator'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">建立時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['pi_create_at'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">更新者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['updater'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">更新時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['pi_update_at'] ?? 'N/A') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">保存</button>
        <a href="<?= url_to('ProjectItemController::index') ?>" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>