<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">鋪路鋼板</h3>
    </div>
    <!-- 搜尋列 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>搜尋篩選</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search('<?= url_to('RoadPlateController::index') ?>'); return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="keyword" class="form-label">關鍵字</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="輸入地點" value="<?= esc($filter['keyword'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> 搜尋
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearSearch('<?= url_to('RoadPlateController::index') ?>')">
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
                    <th>地點</th>
                    <th>數量</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="2" class="text-center">查無資料</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <td><?= esc($item['l_name']) ?></td>
                            <td><?= esc($item['i_qty']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 分頁控件 -->
    <?= view('components/pagination', [
        'pager' => $pager,
        'baseUrl' => url_to('RoadPlateController::index'),
        'params' => $filter
    ]) ?>
</div>

<script src="<?= base_url('js/script.js') ?>"></script>

<script>
    // 搜尋
    function search(url) {
        const keyword = document.getElementById('keyword').value.trim();

        let queryParams = [];

        if (keyword) {
            queryParams.push('keyword=' + encodeURIComponent(keyword));
        }

        const queryString = queryParams.length > 0 ? '?' + queryParams.join('&') : '';
        location.href = url + queryString;
    }

    // 清除搜尋
    function clearSearch(url) {
        document.getElementById('keyword').value = '';
        location.href = url;
    }
</script>

<?= $this->endSection() ?>