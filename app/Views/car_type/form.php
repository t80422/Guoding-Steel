<?= $this->extend('_layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>車種</h2>
    </div>

    <form action="<?= url_to('CarTypeController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="ct_id" value="<?= $data['ct_id'] ?? old('ct_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="ctName" class="form-label">名稱</label>
            <input type="text" class="form-control" name="ct_name" value="<?= old('ct_name', $data['ct_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['ct_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['ct_name'] ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">保存</button>
        <a href="<?= url_to('CarTypeController::index') ?>" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>