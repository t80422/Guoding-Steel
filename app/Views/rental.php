<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">租賃單管理</h3>
    </div>
    <!-- 搜尋列 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>搜尋篩選</h5>
        </div>
        <div class="card-body">
            <form onsubmit="search('<?= url_to('RentalController::index') ?>'); return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="r_memo" class="form-label">備註</label>
                        <input type="text" class="form-control" id="r_memo" name="r_memo" placeholder="輸入備註" value="<?= esc($filter['r_memo'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> 搜尋
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearSearch('<?= url_to('RentalController::index') ?>')">
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
                    <th>備註</th>
                    <th>車頭照片</th>
                    <th>車側照片</th>
                    <th>租賃單照片</th>
                    <th>建立者</th>
                    <th>建立時間</th>
                    <th class="text-end">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="7" class="text-center">查無資料</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <td><?= esc($item['r_memo']) ?></td>
                            <td>
                                <?php if (!empty($item['r_front_image'])): ?>
                                    <img src="<?= url_to('RentalController::image', $item['r_front_image']) ?>" 
                                         alt="車頭照片" 
                                         class="img-thumbnail" 
                                         style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                                         onclick="showImageModal(this.src, '車頭照片')">
                                <?php else: ?>
                                    <span class="text-muted">無圖片</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['r_side_image'])): ?>
                                    <img src="<?= url_to('RentalController::image', $item['r_side_image']) ?>" 
                                         alt="車側照片" 
                                         class="img-thumbnail" 
                                         style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                                         onclick="showImageModal(this.src, '車側照片')">
                                <?php else: ?>
                                    <span class="text-muted">無圖片</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['r_doc_image'])): ?>
                                    <img src="<?= url_to('RentalController::image', $item['r_doc_image']) ?>" 
                                         alt="租賃單照片" 
                                         class="img-thumbnail" 
                                         style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                                         onclick="showImageModal(this.src, '租賃單照片')">
                                <?php else: ?>
                                    <span class="text-muted">無圖片</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($item['creator']) ?></td>
                            <td><?= esc($item['r_create_at']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url_to('RentalController::delete', $item['r_id']) ?>')" title="刪除">
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
<!-- 圖片彈窗模態框 -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">圖片預覽</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('js/script.js') ?>"></script>

<script>
    // 搜尋
    function search(url) {
        const memo = document.getElementById('r_memo').value.trim();
        
        let queryString = '';
        if (memo) {
            queryString = '?memo=' + encodeURIComponent(memo);
        }
        
        location.href = url + queryString;
    }

    // 清除搜尋
    function clearSearch(url) {
        document.getElementById('r_memo').value = '';
        location.href = url;
    }

    // 顯示圖片彈窗
    function showImageModal(imageSrc, imageTitle) {
        document.getElementById('imageModalLabel').textContent = imageTitle;
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('modalImage').alt = imageTitle;
        
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
    }
</script>

<?= $this->endSection() ?>