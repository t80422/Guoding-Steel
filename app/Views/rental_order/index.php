<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">租賃訂單管理</h3>
        <a href="<?= url_to('RentalController::createOrder') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> 新增
        </a>
    </div>
    <!-- 搜尋列 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>搜尋篩選</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search('<?= url_to('RentalController::index_order') ?>'); return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="rental_date_start" class="form-label">日期起</label>
                        <input type="date" class="form-control" id="rental_date_start" name="rental_date_start" value="<?= esc($rental_date_start ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="rental_date_end" class="form-label">日期迄</label>
                        <input type="date" class="form-control" id="rental_date_end" name="rental_date_end" value="<?= esc($rental_date_end ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">類型</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">所有類型</option>
                            <option value="0" <?= ($type ?? '') == '0' ? 'selected' : '' ?>>進工地</option>
                            <option value="1" <?= ($type ?? '') == '1' ? 'selected' : '' ?>>出工地</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="keyword" class="form-label">關鍵字</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="輸入地點、廠商、車號、GPS" value="<?= esc($keyword ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> 搜尋
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearSearch('<?= url_to('RentalController::index_order') ?>')">
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
                    <th>日期</th>
                    <th>類型</th>
                    <th>廠商</th>
                    <th>地點</th>
                    <th>車號</th>
                    <th>GPS</th>
                    <th>備註</th>
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
                        <td colspan="12" class="text-center">查無資料</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <td><?= esc($item['ro_date']) ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = esc($item['typeName']);
                                switch ($statusText) {
                                    case '進工地':
                                        $statusClass = 'bg-info text-white';
                                        break;
                                    case '出工地':
                                        $statusClass = 'bg-success text-white';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary text-white';
                                        $statusText = '';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td><?= esc($item['ma_name']) ?></td>
                            <td><?= esc($item['l_name']) ?></td>
                            <td><?= esc($item['ro_car_number']) ?></td>
                            <td><?= esc($item['g_name']) ?></td>
                            <td><?= esc($item['ro_memo']) ?></td>
                            <td><?= esc($item['creator']) ?></td>
                            <td><?= esc($item['ro_create_at']) ?></td>
                            <td><?= esc($item['updater']) ?></td>
                            <td><?= esc($item['ro_update_at']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info" onclick="window.location.href='<?= url_to('RentalController::editOrder', $item['ro_id']) ?>'" title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('RentalController::deleteOrder', $item['ro_id']) ?>')" title="刪除">
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

<script src="<?= base_url('js/script.js') ?>"></script>

<script>
    function clearSearch(url) {
        document.getElementById('rental_date_start').value = '';
        document.getElementById('rental_date_end').value = '';
        document.getElementById('type').value = '';
        document.getElementById('keyword').value = '';
        location.href = url;
    }
</script>

<?= $this->endSection() ?> 