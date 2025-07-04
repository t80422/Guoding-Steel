<?php

use App\Models\LocationModel;
?>

<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">地點管理</h3>
        <a href="<?= url_to('LocationController::create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> 新增
        </a>
    </div>
    <!-- 搜尋列 -->
    <form class="mb-4" onsubmit="search('<?= url_to('LocationController::index') ?>'); return false;">
        <div class="row g-3">
            <div class="col-md-2">
                <select class="form-select" id="type" name="type">
                    <option value="">全部類型</option>
                    <option value="<?= LocationModel::TYPE_WAREHOUSE ?>" <?= (isset($type) && $type == LocationModel::TYPE_WAREHOUSE) ? 'selected' : '' ?>>
                        倉庫
                    </option>
                    <option value="<?= LocationModel::TYPE_CONSTRUCTION_SITE ?>" <?= (isset($type) && $type == LocationModel::TYPE_CONSTRUCTION_SITE) ? 'selected' : '' ?>>
                        工地
                    </option>
                </select>
            </div>
            <div class="col-md-10">
                <div class="input-group">
                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="搜尋名稱" value="<?= esc($keyword ?? '') ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
    <!-- 列表 -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>名稱</th>
                    <th>類型</th>
                    <th>廠商</th>
                    <th>建立人</th>
                    <th>建立時間</th>
                    <th>更新人</th>
                    <th>更新時間</th>
                    <th class="text-end">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="6" class="text-center">查無資料</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <td><?= esc($item['l_name']) ?></td>
                            <td><?= esc($item['typeName']) ?></td>
                            <td><?= esc($item['ma_name']) ?></td>
                            <td><?= esc($item['creator']) ?></td>
                            <td><?= esc($item['l_create_at']) ?></td>
                            <td><?= esc($item['updater']) ?></td>
                            <td><?= esc($item['l_update_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url_to('LocationController::edit', $item['l_id']) ?>" class="btn btn-sm btn-outline-info me-1" title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('LocationController::delete', $item['l_id']) ?>')" title="刪除">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 分頁控件 -->
<?= view('components/pagination', [
    'pager' => $pager,
    'baseUrl' => url_to('LocationController::index'),
    'params' => $filter
]) ?>

<script src="<?= base_url('js/script.js') ?>"></script>

<?= $this->endSection() ?>