<?php

use App\Models\LocationModel;
?>

<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>地點</h2>
    </div>

    <form action="<?= url_to('LocationController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="l_id" value="<?= $data['l_id'] ?? old('l_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="name" class="form-label">名稱</label>
            <input type="text" class="form-control" id="name" name="l_name" value="<?= old('l_name', $data['l_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['l_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['l_name'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">類型</label>
            <select class="form-select" name="l_type" id="type" required>
                <option value="">請選擇</option>
                <option value="<?= LocationModel::TYPE_WAREHOUSE ?>" <?= (isset($data['l_type']) && $data['l_type'] == LocationModel::TYPE_WAREHOUSE) ? 'selected' : '' ?>>倉庫</option>
                <option value="<?= LocationModel::TYPE_CONSTRUCTION_SITE ?>" <?= (isset($data['l_type']) && $data['l_type'] == LocationModel::TYPE_CONSTRUCTION_SITE) ? 'selected' : '' ?>>工地</option>
            </select>
            <?php if (isset(session()->getFlashdata('errors')['l_type'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['l_type'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="ma_id" class="form-label">廠商</label>
            <select class="form-select" name="l_ma_id" id="ma_id" required>
                <option value="">請選擇</option>
                <?php foreach ($manufacturers as $manufacturer): ?>
                    <option value="<?= $manufacturer['ma_id'] ?>" <?= (isset($data['l_ma_id']) && $data['l_ma_id'] == $manufacturer['ma_id']) ? 'selected' : '' ?>><?= $manufacturer['ma_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">保存</button>
        <a href="<?= url_to('LocationController::index') ?>" class="btn btn-secondary">取消</a>
    </form>
</div>

<?= $this->endSection() ?>