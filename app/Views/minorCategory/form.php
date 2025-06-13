<?= $this->extend('_layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>小分類</h2>
    </div>

    <form action="<?= url_to('MinorCategoryController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="mic_id" value="<?= $data['mic_id'] ?? old('mic_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="micName" class="form-label">名稱</label>
            <input type="text" class="form-control" name="mic_name" value="<?= old('mic_name', $data['mic_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['mic_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['mic_name'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="micMcId" class="form-label">大分類</label>
            <select class="form-select" name="mic_mc_id" required>
                <option value="">請選擇</option>
                <?php foreach ($majorCategories as $item): ?>
                    <option value="<?= $item['mc_id'] ?>" <?= old('mic_mc_id', $data['mic_mc_id'] ?? '') == $item['mc_id'] ? 'selected' : '' ?>>
                        <?= esc($item['mc_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset(session()->getFlashdata('errors')['mic_mc_id'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['mic_mc_id'] ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">保存</button>
        <a href="<?= url_to('MinorCategoryController::index') ?>" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>