<?= $this->extend('_layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>使用者</h2>
    </div>
    <!-- 顯示錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <form action="<?= url_to('UserController::save') ?>" method="post">
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
        </div>
        <div class="mb-3">
            <label for="positionName" class="form-label">職位</label>
            <select class="form-control" name="u_p_id" id="positionName">
                <option value="">請選擇</option>
                <?php foreach ($positions as $position): ?>
                    <option value="<?= $position['p_id'] ?>" <?= (old('u_p_id', $data['u_p_id'] ?? '') == $position['p_id']) ? 'selected' : '' ?>>
                        <?= $position['p_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="isAdmin" class="form-label">是否使用後台</label>
            <input type="checkbox" class="form-check-input" id="isAdmin" name="u_is_admin" value="1" <?= (old('u_is_admin', $data['u_is_admin'] ?? 0) == 1) ? 'checked' : '' ?>>
        </div>
        <button type="submit" class="btn btn-primary">保存</button>
        <a href="/user" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>