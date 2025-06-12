<?= $this->extend('_layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>職位</h2>
    </div>

    <form action="/position/save" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="p_id" value="<?= $data['p_id'] ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="positionName" class="form-label">職位名稱</label>
            <input type="text" class="form-control" name="p_name" value="<?= $data['p_name'] ?? '' ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">保存</button>
        <a href="/position" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>