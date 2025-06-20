<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">登入登出紀錄</h3>
    </div>
    <!-- 搜尋列 -->
    <form class="mb-4" onsubmit="search('<?= url_to('AuthController::authLogs') ?>'); return false;">
        <div class="row g-3">
            <div class="col-md-12">
                <div class="input-group">
                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="搜尋使用者" value="<?= esc($keyword ?? '') ?>">
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
                    <th>使用者</th>
                    <th>登入時間</th>
                    <th>登出時間</th>
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
                            <td><?= esc($item['u_name']) ?></td>
                            <td><?= esc($item['us_login_time']) ?></td>
                            <td><?= esc($item['us_logout_time']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= view('components/pagination', [
        'pager' => $pager,
        'baseUrl' => url_to('AuthController::authLogs')
    ]) ?>
</div>

<script src="<?= base_url('js/script.js') ?>"></script>

<?= $this->endSection() ?>