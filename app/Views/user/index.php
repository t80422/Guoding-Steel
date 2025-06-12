<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <!-- 標題 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>使用者管理</h2>
        <button type="button" class="btn btn-primary" onclick="window.location.href='<?= base_url('user/create') ?>'">新增</button>
    </div>
    <!-- 搜尋 -->
    <div class="mb-3 input-group">
        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="使用者名稱、職位" value="<?= esc($keyword ?? '') ?>">
        <button class="btn btn-primary" onclick="search()">搜尋</button>
    </div>
    <!-- 列表 -->
    <table id="userTable" class="table table-striped table-hover">
        <!-- 表頭 -->
        <thead>
            <tr>
                <th>名稱</th>
                <th>職位</th>
                <th>建立時間</th>
                <th>操作</th>
            </tr>
        </thead>
        <!-- 內容 -->
        <tbody>
            <?php foreach ($data as $item): ?>
                <tr>
                    <td><?= $item['u_name'] ?></td>
                    <td><?= $item['p_name'] ?></td>
                    <td><?= $item['u_create_at'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="location.href='<?= url_to('UserController::edit', $item['u_id']) ?>'">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= url_to('UserController::delete', $item['u_id']) ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function search() {
        location.href = '<?= base_url('user'); ?>' + "?keyword=" + document.getElementById('keyword').value;
    }

    function confirmDelete(url) {
        if (confirm('確定要刪除這筆資料嗎？')) {
            location.href = url;
        }
    }
</script>

<?= $this->endSection() ?>