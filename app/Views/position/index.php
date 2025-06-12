<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <!-- 標題 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>職位管理</h2>
        <button type="button" class="btn btn-primary" onclick="window.location.href='<?= base_url('position/create') ?>'">新增</button>
    </div>
    <!-- 搜尋 -->
    <div class="mb-3 input-group">
        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="搜尋職位名稱" value="<?= esc($keyword ?? '') ?>">
        <button class="btn btn-primary" onclick="search()">搜尋</button>
    </div>
    <!-- 列表 -->
    <table id="positionTable" class="table table-striped table-hover">
        <!-- 表頭 -->
        <thead>
            <tr>
                <th>名稱</th>
                <th>建立人</th>
                <th>建立時間</th>
                <th>更新人</th>
                <th>更新時間</th>
                <th>操作</th>
            </tr>
        </thead>
        <!-- 內容 -->
        <tbody>
            <?php foreach ($data as $item): ?>
                <tr>
                    <td><?= $item['p_name'] ?></td>
                    <td><?= $item['creator'] ?></td>
                    <td><?= $item['p_create_at'] ?></td>
                    <td><?= $item['updater'] ?></td>
                    <td><?= $item['p_update_at'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="location.href='<?= url_to('PositionController::edit', $item['p_id']) ?>'">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= url_to('PositionController::delete', $item['p_id']) ?>')">
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
        location.href = '<?= base_url('position'); ?>' + "?keyword=" + document.getElementById('keyword').value;
    }

    function confirmDelete(url) {
        if (confirm('確定要刪除這筆資料嗎？')) {
            location.href = url;
        }
    }
</script>

<?= $this->endSection() ?>