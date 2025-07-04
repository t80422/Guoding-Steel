<?php

use App\Models\MachineRepairModel;
?>

<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>機械保養</h2>
    </div>

    <!-- 顯示錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('MachineRepairController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="mr_id" value="<?= $data['mr_id'] ?? old('mr_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="mr_date" class="form-label">日期</label>
            <input type="date" class="form-control" id="mr_date" name="mr_date" value="<?= old('mr_date', $data['mr_date'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="mr_m_id" class="form-label">機械</label>
            <select class="form-select" id="mr_m_id" name="mr_m_id" required>
                <option value="">請選擇</option>
                <?php foreach ($machines as $machine): ?>
                    <option value="<?= $machine['m_id'] ?>" <?= old('mr_m_id', $data['mr_m_id'] ?? '') == $machine['m_id'] ? 'selected' : '' ?>><?= $machine['m_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="mr_status" class="form-label">狀態</label>
            <select class="form-select" id="mr_status" name="mr_status" required>
                <option value="">請選擇</option>
                <option value="<?= MachineRepairModel::STATUS_UNRETURNED ?>" <?= old('mr_status', $data['mr_status'] ?? '') == MachineRepairModel::STATUS_UNRETURNED ? 'selected' : '' ?>>未歸還</option>
                <option value="<?= MachineRepairModel::STATUS_RETURNED ?>" <?= old('mr_status', $data['mr_status'] ?? '') == MachineRepairModel::STATUS_RETURNED ? 'selected' : '' ?>>已歸還</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="mr_memo" class="form-label">備註</label>
            <textarea class="form-control" id="mr_memo" name="mr_memo" rows="3"><?= old('mr_memo', $data['mr_memo'] ?? '') ?></textarea>
        </div>

        <?php if ($isEdit): ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">建立者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['creator'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">建立時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['mr_create_at'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">更新者</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['updater'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">更新時間</label>
                    <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['mr_update_at'] ?? 'N/A') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="<?= url_to('MachineRepairController::index') ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>

</div>

<?= $this->endSection() ?>