<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">料單管理</h3>
    </div>
    <!-- 搜尋列 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>搜尋篩選</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search('<?= url_to('OrderController::index') ?>'); return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="order_date_start" class="form-label">訂單日期起</label>
                        <input type="date" class="form-control" id="order_date_start" name="order_date_start" value="<?= esc($order_date_start ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="order_date_end" class="form-label">訂單日期迄</label>
                        <input type="date" class="form-control" id="order_date_end" name="order_date_end" value="<?= esc($order_date_end ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">訂單類型</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">所有類型</option>
                            <option value="0" <?= ($type ?? '') == '0' ? 'selected' : '' ?>>進倉庫</option>
                            <option value="1" <?= ($type ?? '') == '1' ? 'selected' : '' ?>>出倉庫</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="keyword" class="form-label">關鍵字</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="輸入出發地、目的地、車號" value="<?= esc($keyword ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> 搜尋
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearSearch('<?= url_to('OrderController::index') ?>')">
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
                    <th>訂單日期</th>
                    <th>訂單狀態</th>
                    <th>訂單類型</th>
                    <th>出發地</th>
                    <th>目的地</th>
                    <th>車號</th>
                    <th>GPS</th>
                    <th>總噸數</th>
                    <th>氧氣</th>
                    <th>乙炔</th>
                    <th>備註</th>
                    <th class="text-end">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="10" class="text-center">查無資料</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <td><?= esc($item['o_date']) ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = esc($item['o_status']);
                                switch ($statusText) {
                                    case '進行中':
                                        $statusClass = 'bg-info text-white';
                                        break;
                                    case '已完成':
                                        $statusClass = 'bg-success text-white';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary text-white';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <?php
                                $typeClass = '';
                                $typeText = esc($item['typeName']);
                                switch ($typeText) {
                                    case '進倉庫':
                                        $typeClass = 'bg-primary text-white';
                                        break;
                                    case '出倉庫':
                                        $typeClass = 'bg-dark text-white';
                                        break;
                                    default:
                                        $typeClass = 'bg-secondary text-white';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $typeClass ?>"><?= $typeText ?></span>
                            </td>
                            <td><?= esc($item['from_location_name']) ?></td>
                            <td><?= esc($item['to_location_name']) ?></td>
                            <td><?= esc($item['o_car_number']) ?></td>
                            <td><?= esc($item['gps_name']) ?></td>
                            <td><?= esc($item['o_total_tons']) ?></td>
                            <td><?= esc($item['o_oxygen']) ?></td>
                            <td><?= esc($item['o_acetylene']) ?></td>
                            <td><?= esc($item['o_remark']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info" onclick="window.open('<?= url_to('OrderController::print', $item['o_id']) ?>', '_blank')" title="列印">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="checkEditPermission('<?= url_to('OrderController::edit', $item['o_id']) ?>')" title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('OrderController::delete', $item['o_id']) ?>')" title="刪除">
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
        document.getElementById('order_date_start').value = '';
        document.getElementById('order_date_end').value = '';
        document.getElementById('type').value = '';
        document.getElementById('keyword').value = '';
        location.href = url;
    }
</script>

<?= $this->endSection() ?>