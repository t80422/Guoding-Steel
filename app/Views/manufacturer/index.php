<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">廠商管理</h3>
        <a href="<?= url_to('ManufacturerController::create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> 新增
        </a>
    </div>
    <!-- 搜尋列 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>搜尋篩選</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search('<?= url_to('ManufacturerController::index') ?>'); return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="ma_name" class="form-label">廠商名稱</label>
                        <input type="text" class="form-control" id="ma_name" name="ma_name" placeholder="輸入廠商名稱" value="<?= esc($filter['ma_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> 搜尋
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearSearch('<?= url_to('ManufacturerController::index') ?>')">
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
                    <th>廠商名稱</th>
                    <th>建立者</th>
                    <th>建立時間</th>
                    <th>更新者</th>
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
                            <td><?= esc($item['ma_name']) ?></td>
                            <td><?= esc($item['creator']) ?></td>
                            <td><?= esc($item['ma_create_at']) ?></td>
                            <td><?= esc($item['updater']) ?></td>
                            <td><?= esc($item['ma_update_at']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info" onclick="checkEditPermission('<?= url_to('ManufacturerController::edit', $item['ma_id']) ?>')" title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('ManufacturerController::delete', $item['ma_id']) ?>')" title="刪除">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 分頁控件 -->
    <?= view('components/pagination', [
        'pager' => $pager,
        'baseUrl' => url_to('ManufacturerController::index'),
        'params' => $filter
    ]) ?>
</div>

<script src="<?= base_url('js/script.js') ?>"></script>

<script>
    // 搜尋
    function search(url) {
        const maName = document.getElementById('ma_name').value.trim();

        let queryParams = [];
        
        if (maName) {
            queryParams.push('ma_name=' + encodeURIComponent(maName));
        }

        const queryString = queryParams.length > 0 ? '?' + queryParams.join('&') : '';
        location.href = url + queryString;
    }

    // 清除搜尋
    function clearSearch(url) {
        document.getElementById('ma_name').value = '';
        location.href = url;
    }
</script>

<?= $this->endSection() ?>