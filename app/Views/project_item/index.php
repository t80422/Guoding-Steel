<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">施工項目管理</h3>
        <a href="<?= url_to('ProjectItemController::create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> 新增
        </a>
    </div>
    <!-- 搜尋列 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>搜尋篩選</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search('<?= url_to('ProjectItemController::index') ?>'); return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="keyword" class="form-label">關鍵字</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="輸入關鍵字" value="<?= esc($keyword ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> 搜尋
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearSearch('<?= url_to('ProjectItemController::index') ?>')">
                            <i class="bi bi-x-circle"></i> 清除
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- 列表 -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>名稱</th>
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
                            <td><?= esc($item['pi_name']) ?></td>
                            <td><?= esc($item['creator']) ?></td>
                            <td><?= esc($item['pi_create_at']) ?></td>
                            <td><?= esc($item['updater']) ?></td>
                            <td><?= esc($item['pi_update_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url_to('ProjectItemController::edit', $item['pi_id']) ?>" class="btn btn-sm btn-outline-info me-1" title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('ProjectItemController::delete', $item['pi_id']) ?>')" title="刪除">
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
    'baseUrl' => url_to('ProjectItemController::index'),
    'params' => $filter
]) ?>

<script src="<?= base_url('js/script.js') ?>"></script>

<script>
    function clearSearch(url) {
        document.getElementById('keyword').value = '';
        location.href = url;
    }
</script>

<?= $this->endSection() ?>