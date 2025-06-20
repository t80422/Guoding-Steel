<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">使用者管理</h3>
        <a href="<?= url_to('UserController::create') ?>" class="btn btn-outline-primary">
            <i class="bi bi-plus-lg me-1"></i> 新增
        </a>
    </div>
    <!-- 搜尋列 -->
    <form class="mb-4" onsubmit="search('<?= url_to('UserController::index') ?>'); return false;">
        <div class="input-group">
            <input type="text" class="form-control" id="keyword" name="keyword" placeholder="搜尋名稱、職位" value="<?= esc($keyword ?? '') ?>">
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
                    <th>職位</th>
                    <th>建立時間</th>
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
                            <td><?= esc($item['u_name']) ?></td>
                            <td><?= esc($item['p_name']) ?></td>
                            <td><?= esc($item['u_create_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url_to('UserController::edit', $item['u_id']) ?>" class="btn btn-sm btn-outline-info me-1" title="編輯">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('UserController::delete', $item['u_id']) ?>')" title="刪除">
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
        'baseUrl' => url_to('UserController::index')
    ]) ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 刪除確認功能
        document.querySelectorAll('.delete-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                if (confirm('確定要刪除此使用者嗎？此操作無法復原。')) {
                    window.location.href = url;
                }
            });
        });
    });
</script>

<script src="<?= base_url('js/script.js') ?>"></script>

<?= $this->endSection() ?>