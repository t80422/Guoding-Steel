<?= $this->extend('_layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>大分類</h2>
    </div>

    <form action="<?= url_to('MajorCategoryController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="mc_id" value="<?= $data['mc_id'] ?? old('mc_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="mcName" class="form-label">名稱</label>
            <input type="text" class="form-control" name="mc_name" value="<?= old('mc_name', $data['mc_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['mc_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['mc_name'] ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">保存</button>
        <a href="<?= url_to('MajorCategoryController::index') ?>" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>