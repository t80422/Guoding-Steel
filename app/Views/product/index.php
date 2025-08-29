<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">產品管理</h3>
        <button class="btn btn-primary" onclick="checkCreatePermission('<?= url_to('ProductController::create') ?>')">
            <i class="bi bi-plus-lg me-1"></i> 新增
        </button>
    </div>
    <!-- 搜尋列 -->
    <form class="mb-4" onsubmit="search('<?= url_to('ProductController::index') ?>'); return false;">
        <div class="input-group">
            <input type="text" class="form-control" id="keyword" name="keyword" placeholder="搜尋名稱、大分類、小分類" value="<?= esc($keyword ?? '') ?>">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>
    <!-- 列表 -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>名稱</th>
                    <th>大分類</th>
                    <th>小分類</th>
                    <th>重量</th>
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
                        <td colspan="9" class="text-center">查無資料</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <td><?= esc($item['pr_name']) ?></td>
                            <td><?= esc($item['mc_name']) ?></td>
                            <td><?= esc($item['mic_name']) ?></td>
                            <td><?= esc($item['pr_weight']) ?></td>
                            <td><?= esc($item['creator']) ?></td>
                            <td><?= esc($item['pr_create_at']) ?></td>
                            <td><?= esc($item['updater']) ?></td>
                            <td><?= esc($item['pr_update_at']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info me-1" onclick="checkEditPermission('<?= url_to('ProductController::edit', $item['pr_id']) ?>')" title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('ProductController::delete', $item['pr_id']) ?>')" title="刪除">
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
    'baseUrl' => url_to('ProductController::index'),
    'params' => $filter
]) ?>

<script src="<?= base_url('js/script.js') ?>"></script>

<?= $this->endSection() ?>