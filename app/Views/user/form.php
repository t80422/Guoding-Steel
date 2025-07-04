<?= $this->extend('_layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>使用者</h2>
    </div>

    <form action="/user/save" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="u_id" value="<?= $data['u_id'] ?? old('u_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="userName" class="form-label">使用者名稱</label>
            <input type="text" class="form-control" name="u_name" value="<?= old('u_name', $data['u_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['u_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['u_name'] ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">密碼</label>
            <input type="password" class="form-control" name="u_password" value="<?= old('u_password') ?>" <?= $isEdit ? '' : 'required' ?>>
            <?php if (isset(session()->getFlashdata('errors')['u_password'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['u_password'] ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="confirmPassword" class="form-label">確認密碼</label>
            <input type="password" class="form-control" name="u_confirm_password" value="<?= old('u_confirm_password') ?>" <?= $isEdit ? '' : 'required' ?>>
            <?php if (isset(session()->getFlashdata('errors')['u_confirm_password'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['u_confirm_password'] ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="positionName" class="form-label">職位</label>
            <select class="form-control" name="u_p_id" id="positionName" required>
                <option value="">請選擇</option>
                <?php foreach ($positions as $position): ?>
                    <option value="<?= $position['p_id'] ?>" <?= (old('u_p_id', $data['u_p_id'] ?? '') == $position['p_id']) ? 'selected' : '' ?>>
                        <?= $position['p_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset(session()->getFlashdata('errors')['u_p_id'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['u_p_id'] ?>
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">保存</button>
        <a href="/user" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>