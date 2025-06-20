<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>GPS</h2>
    </div>

    <!-- 顯示成功/錯誤訊息 -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('GpsController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="g_id" value="<?= $data['g_id'] ?? old('g_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="gName" class="form-label">名稱</label>
            <input type="text" class="form-control" name="g_name" value="<?= old('g_name', $data['g_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['g_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['g_name'] ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">保存</button>
        <a href="<?= url_to('GpsController::index') ?>" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>