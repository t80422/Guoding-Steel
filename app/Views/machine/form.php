<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>機械</h2>
    </div>

    <!-- 顯示錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('MachineController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="m_id" value="<?= $data['m_id'] ?? old('m_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="m_name" class="form-label">名稱</label>
            <input type="text" class="form-control" id="m_name" name="m_name" value="<?= old('m_name', $data['m_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['m_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['m_name'] ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isEdit): ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">建立者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['creator'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">建立時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['m_create_at']) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="<?= url_to('MachineController::index') ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>

</div>

<?= $this->endSection() ?>