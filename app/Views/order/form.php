<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- 頁面標題區 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">訂單管理</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url_to('OrderController::index') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>返回列表
            </a>
            <button type="submit" form="orderForm" class="btn btn-primary">
                <i class="bi bi-check-lg me-2"></i>保存訂單
            </button>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form id="orderForm" action="<?= url_to('OrderController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="o_id" value="<?= $data['order']['o_id'] ?? old('o_id') ?>">
        <?php endif; ?>

        <div class="row g-4">
            <!-- 左側主要內容 -->
            <div class="col-lg-8">
                <!-- 基本資訊卡片 -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2 text-primary"></i>基本資訊
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- 訂單類型 -->
                            <div class="col-12">
                                <label class="form-label fw-bold">訂單類型</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="o_type" id="o_type_0" value="0" 
                                           autocomplete="off" <?= old('o_type', $data['order']['o_type'] ?? '') == '0' ? 'checked' : '' ?> required>
                                    <label class="btn btn-outline-primary" for="o_type_0">
                                        <i class="bi bi-box-arrow-in-down me-1"></i>進倉庫
                                    </label>
                                    <input type="radio" class="btn-check" name="o_type" id="o_type_1" value="1" 
                                           autocomplete="off" <?= old('o_type', $data['order']['o_type'] ?? '') == '1' ? 'checked' : '' ?> required>
                                    <label class="btn btn-outline-primary" for="o_type_1">
                                        <i class="bi bi-box-arrow-up me-1"></i>出倉庫
                                    </label>
                                </div>
                            </div>

                            <!-- 地點資訊 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-geo-alt me-1"></i>出發地
                                </label>
                                <input type="hidden" name="o_from_location" value="<?= old('o_from_location', $data['order']['o_from_location'] ?? '') ?>" required>
                                <div class="form-control location-selector d-flex justify-content-between align-items-center" 
                                     id="fromLocationDisplay" data-bs-toggle="modal" data-bs-target="#locationModal" 
                                     data-target-field="from" style="cursor: pointer; min-height: 45px;">
                                    <span id="fromLocationText" class="text-muted">
                                        <?= $isEdit && isset($data['order']['from_location_name']) ? $data['order']['from_location_name'] : '請選擇出發地點' ?>
                                    </span>
                                    <i class="bi bi-chevron-down text-muted"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-pin-map me-1"></i>目的地
                                </label>
                                <input type="hidden" name="o_to_location" value="<?= old('o_to_location', $data['order']['o_to_location'] ?? '') ?>" required>
                                <div class="form-control location-selector d-flex justify-content-between align-items-center" 
                                     id="toLocationDisplay" data-bs-toggle="modal" data-bs-target="#locationModal" 
                                     data-target-field="to" style="cursor: pointer; min-height: 45px;">
                                    <span id="toLocationText" class="text-muted">
                                        <?= $isEdit && isset($data['order']['to_location_name']) ? $data['order']['to_location_name'] : '請選擇目的地點' ?>
                                    </span>
                                    <i class="bi bi-chevron-down text-muted"></i>
                                </div>
                            </div>

                            <!-- 日期時間 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="o_date">
                                    <i class="bi bi-calendar3 me-1"></i>訂單日期
                                </label>
                                <input type="date" class="form-control" name="o_date" id="o_date" 
                                       value="<?= old('o_date', $data['order']['o_date'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="o_g_id" class="form-label fw-bold">
                                    <i class="bi bi-broadcast me-1"></i>GPS設備
                                </label>
                                <select class="form-select" name="o_g_id" id="o_g_id" required>
                                    <option value="">請選擇GPS設備</option>
                                    <?php foreach ($data['gpsOptions'] as $gps): ?>
                                        <option value="<?= $gps['g_id'] ?>" <?= old('o_g_id', $data['order']['o_g_id'] ?? '') == $gps['g_id'] ? 'selected' : '' ?>>
                                            <?= esc($gps['g_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- 運輸資訊 -->
                            <div class="col-md-6">
                                <label for="o_car_number" class="form-label fw-bold">
                                    <i class="bi bi-truck me-1"></i>車號
                                </label>
                                <input type="text" class="form-control" name="o_car_number" id="o_car_number" 
                                       value="<?= old('o_car_number', $data['order']['o_car_number'] ?? '') ?>" 
                                       placeholder="請輸入車牌號碼" required>
                            </div>
                            <div class="col-md-6">
                                <label for="o_driver_phone" class="form-label fw-bold">
                                    <i class="bi bi-telephone me-1"></i>司機電話
                                </label>
                                <input type="text" class="form-control" name="o_driver_phone" id="o_driver_phone" 
                                       value="<?= old('o_driver_phone', $data['order']['o_driver_phone'] ?? '') ?>" 
                                       placeholder="請輸入聯絡電話" required>
                            </div>

                            <!-- 作業時間 -->
                            <div class="col-md-6">
                                <label for="o_loading_time" class="form-label fw-bold">
                                    <i class="bi bi-clock me-1"></i>上料時間
                                </label>
                                <input type="datetime-local" class="form-control" name="o_loading_time" id="o_loading_time" 
                                       value="<?= old('o_loading_time', $data['order']['o_loading_time'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="o_unloading_time" class="form-label fw-bold">
                                    <i class="bi bi-clock me-1"></i>下料時間
                                </label>
                                <input type="datetime-local" class="form-control" name="o_unloading_time" id="o_unloading_time" 
                                       value="<?= old('o_unloading_time', $data['order']['o_unloading_time'] ?? '') ?>" required>
                            </div>

                            <!-- 氣體資訊 -->
                            <div class="col-md-6">
                                <label for="o_oxygen" class="form-label fw-bold">
                                    <i class="bi bi-droplet me-1"></i>氧氣(公斤)
                                </label>
                                <input type="number" class="form-control" name="o_oxygen" id="o_oxygen" 
                                       value="<?= old('o_oxygen', $data['order']['o_oxygen'] ?? '') ?>" 
                                       placeholder="0" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="o_acetylene" class="form-label fw-bold">
                                    <i class="bi bi-fire me-1"></i>乙炔(公斤)
                                </label>
                                <input type="number" class="form-control" name="o_acetylene" id="o_acetylene" 
                                       value="<?= old('o_acetylene', $data['order']['o_acetylene'] ?? '') ?>" 
                                       placeholder="0" step="0.01" min="0" required>
                            </div>

                            <!-- 備註 -->
                            <div class="col-12">
                                <label for="o_remark" class="form-label fw-bold">
                                    <i class="bi bi-chat-text me-1"></i>備註說明
                                </label>
                                <textarea class="form-control" name="o_remark" id="o_remark" rows="3" 
                                          placeholder="請輸入備註說明..."><?= old('o_remark', $data['order']['o_remark'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 訂單明細卡片 -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-ul me-2 text-primary"></i>訂單明細
                            </h5>
                            <button type="button" class="btn btn-success btn-sm" id="addDetailBtn">
                                <i class="bi bi-plus-lg me-1"></i>新增明細
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="detailTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 5%;">操作</th>
                                        <th style="width: 45%;">產品</th>
                                        <th style="width: 15%;">數量</th>
                                        <th style="width: 15%;">長度(m)</th>
                                        <th style="width: 20%;">重量(kg)</th>
                                    </tr>
                                </thead>
                                <tbody id="detailTableBody">
                                    <?php if ($isEdit && isset($data['orderDetails']) && !empty($data['orderDetails'])): ?>
                                        <?php foreach ($data['orderDetails'] as $index => $detail): ?>
                                            <tr data-index="<?= $index ?>">
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                                <td class="align-middle">
                                                    <?php if ($isEdit && !empty($detail['od_id'])): ?>
                                                    <input type="hidden" name="details[<?= $index ?>][od_id]" value="<?= $detail['od_id'] ?>">
                                                    <?php endif; ?>
                                                    <input type="hidden" name="details[<?= $index ?>][od_pr_id]" value="<?= $detail['od_pr_id'] ?? '' ?>">
                                                    <input type="hidden" class="product-weight-per-unit" value="<?= $detail['pr_weight_per_unit'] ?? 0 ?>">
                                                    <div class="form-control product-selector border-dashed" data-bs-toggle="modal" 
                                                         data-bs-target="#productModal" data-target-index="<?= $index ?>" style="cursor: pointer;">
                                                        <span class="product-text"><?= isset($detail['pr_name']) ? esc($detail['pr_name']) : '請選擇產品' ?></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control quantity-input"
                                                        name="details[<?= $index ?>][od_qty]"
                                                        value="<?= $detail['od_qty'] ?>"
                                                        step="0.01" min="0" required>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control length-input"
                                                        name="details[<?= $index ?>][od_length]"
                                                        value="<?= $detail['od_length'] ?>"
                                                        step="0.01" min="0" required>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control-plaintext weight-input fw-bold text-primary"
                                                        name="details[<?= $index ?>][od_weight]"
                                                        value="<?= $detail['od_weight'] ?>"
                                                        step="0.01" min="0" readonly>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr data-index="0">
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                            <td class="align-middle">
                                                <input type="hidden" name="details[0][od_pr_id]" value="">
                                                <input type="hidden" class="product-weight-per-unit" value="0">
                                                <div class="form-control product-selector border-dashed" data-bs-toggle="modal" 
                                                     data-bs-target="#productModal" data-target-index="0" style="cursor: pointer;">
                                                    <span class="product-text text-muted">請選擇產品</span>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control quantity-input"
                                                    name="details[0][od_qty]"
                                                    step="0.01" min="0" required>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control length-input"
                                                    name="details[0][od_length]"
                                                    step="0.01" min="0" required>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control-plaintext weight-input fw-bold text-primary"
                                                    name="details[0][od_weight]"
                                                    step="0.01" min="0" readonly>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右側資訊 -->
            <div class="col-lg-4">
                <!-- 簽名資訊卡片 -->
                <?php if ($isEdit): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light border-0 py-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pen me-2 text-primary"></i>簽名記錄
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <!-- 司機簽名 -->
                                <div class="col-12">
                                    <div class="signature-item">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-person-badge me-2 text-muted"></i>
                                            <span class="fw-bold text-muted">司機簽名</span>
                                        </div>
                                        <div class="signature-container">
                                            <?php if (!empty($data['order']['o_driver_signature'])): ?>
                                                <div class="signature-preview" onclick="openImageModal('<?= base_url('order/serveSignature/' . $data['order']['o_driver_signature']) ?>', '司機簽名')">
                                                    <img src="<?= base_url('order/serveSignature/' . $data['order']['o_driver_signature']) ?>" 
                                                         alt="司機簽名" class="signature-image">
                                                    <div class="signature-overlay">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="signature-placeholder">
                                                    <i class="bi bi-image text-muted"></i>
                                                    <span class="text-muted">尚未簽名</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 發貨方簽名 -->
                                <div class="col-12">
                                    <div class="signature-item">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-box-arrow-up-right me-2 text-muted"></i>
                                            <span class="fw-bold text-muted">發貨方簽名</span>
                                        </div>
                                        <div class="signature-container">
                                            <?php if (!empty($data['order']['o_from_signature'])): ?>
                                                <div class="signature-preview" onclick="openImageModal('<?= base_url('order/serveSignature/' . $data['order']['o_from_signature']) ?>', '發貨方簽名')">
                                                    <img src="<?= base_url('order/serveSignature/' . $data['order']['o_from_signature']) ?>" 
                                                         alt="發貨方簽名" class="signature-image">
                                                    <div class="signature-overlay">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="signature-placeholder">
                                                    <i class="bi bi-image text-muted"></i>
                                                    <span class="text-muted">尚未簽名</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 收貨方簽名 -->
                                <div class="col-12">
                                    <div class="signature-item">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-box-arrow-in-down me-2 text-muted"></i>
                                            <span class="fw-bold text-muted">收貨方簽名</span>
                                        </div>
                                        <div class="signature-container">
                                            <?php if (!empty($data['order']['o_to_signature'])): ?>
                                                <div class="signature-preview" onclick="openImageModal('<?= base_url('order/serveSignature/' . $data['order']['o_to_signature']) ?>', '收貨方簽名')">
                                                    <img src="<?= base_url('order/serveSignature/' . $data['order']['o_to_signature']) ?>" 
                                                         alt="收貨方簽名" class="signature-image">
                                                    <div class="signature-overlay">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="signature-placeholder">
                                                    <i class="bi bi-image text-muted"></i>
                                                    <span class="text-muted">尚未簽名</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 系統資訊卡片 -->
                <?php if ($isEdit): ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-0 py-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-square me-2 text-primary"></i>系統資訊
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">建立者</label>
                                        <p class="fw-bold mb-2"><?= esc($data['order']['create_name'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">建立時間</label>
                                        <p class="fw-bold mb-2"><?= esc($data['order']['o_create_at']) ?></p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">最後更新者</label>
                                        <p class="fw-bold mb-2"><?= esc($data['order']['update_name'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">最後更新時間</label>
                                        <p class="fw-bold mb-0"><?= esc($data['order']['o_update_at'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- 圖片放大模態框 -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="imageModalLabel">簽名圖片</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modalImage" src="" alt="簽名圖片" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<!-- 地點選擇 Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalLabel">選擇地點</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- 地點類型選擇標籤 -->
                <ul class="nav nav-tabs mb-3" id="locationTypeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="originTab" data-bs-toggle="tab"
                            data-bs-target="#originPane" type="button" role="tab">
                            倉庫
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="constructionTab" data-bs-toggle="tab"
                            data-bs-target="#constructionPane" type="button" role="tab">
                            工地
                        </button>
                    </li>
                </ul>

                <!-- 地點內容 -->
                <div class="tab-content" id="locationTypeContent">
                    <!-- 產地標籤內容 -->
                    <div class="tab-pane fade show active" id="originPane" role="tabpanel">
                        <div class="list-group" id="originList">
                            <div class="text-muted text-center py-3">載入中...</div>
                        </div>
                    </div>

                    <!-- 工地標籤內容 -->
                    <div class="tab-pane fade" id="constructionPane" role="tabpanel">
                        <div class="list-group" id="constructionList">
                            <div class="text-muted text-center py-3">載入中...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('components/product_selector', [
    'modalId' => 'productModal',
    'fieldPrefix' => 'od'
]) ?>

<style>
/* ===== 現代化卡片樣式 ===== */
.card {
    border-radius: 12px;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(87, 145, 87, 0.15) !important;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

.card-title {
    color: #495057;
    font-weight: 600;
}

/* ===== 表單元素現代化樣式 ===== */
.form-control, .form-select {
    border-radius: 8px;
    border: 1.5px solid #dee2e6;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(87, 145, 87, 0.25);
    transform: translateY(-1px);
}

.form-label.fw-bold {
    color: #495057;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

/* ===== 地點選擇器樣式 ===== */
.location-selector {
    transition: all 0.3s ease;
    border-radius: 8px;
    border: 1.5px solid #dee2e6;
}

.location-selector:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(87, 145, 87, 0.15);
    transform: translateY(-1px);
}

/* ===== 地點選擇模態框樣式 ===== */
#locationModal .location-option {
    color: #212529 !important;
    background-color: #ffffff !important;
    border: 1px solid #dee2e6 !important;
}

#locationModal .location-option:hover {
    color: #495057 !important;
    background-color: #f8f9fa !important;
    border-color: var(--bs-primary) !important;
}

#locationModal .location-option span {
    color: inherit !important;
    font-weight: 500;
}

#locationModal .list-group-item-action {
    color: #212529 !important;
}

#locationModal .list-group-item-action:hover {
    color: #495057 !important;
    background-color: #f8f9fa !important;
}



/* ===== 簽名區域樣式 ===== */
.signature-container {
    position: relative;
    width: 100%;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
}

.signature-preview {
    position: relative;
    width: 100%;
    height: 100%;
    cursor: pointer;
    overflow: hidden;
}

.signature-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: all 0.3s ease;
}

.signature-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(87, 145, 87, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    color: white;
    font-size: 24px;
}

.signature-preview:hover .signature-overlay {
    opacity: 1;
}

.signature-preview:hover .signature-image {
    transform: scale(1.05);
}

.signature-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6c757d;
}

.signature-placeholder i {
    font-size: 24px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.signature-placeholder span {
    font-size: 12px;
    font-weight: 500;
}

.signature-item {
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.signature-item:hover {
    background: #e9ecef;
}

/* ===== 資訊項目樣式 ===== */
.info-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid var(--bs-primary);
}

/* ===== 按鈕組現代化樣式 ===== */
.btn-group .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 10px 20px;
}

.btn-group .btn:first-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.btn-group .btn:last-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

/* ===== 表格現代化樣式 ===== */
.table {
    border-radius: 0 0 12px 12px;
    overflow: hidden;
}

.table thead th {
    background: linear-gradient(135deg, #EBF1EC 0%, #d4edda 100%) !important;
    border: none;
    font-weight: 600;
    color: #495057;
    font-size: 13px;
    padding: 16px 12px;
}

.table tbody td {
    padding: 12px;
    vertical-align: middle;
    border-color: #f1f3f4;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}



/* ===== 響應式優化 ===== */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card-body {
        padding: 20px !important;
    }
    
    .signature-container {
        height: 100px;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.gap-2 .btn {
        width: 100%;
    }
}

/* ===== 模態框優化 ===== */
.modal-content {
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.modal-header {
    border-radius: 16px 16px 0 0;
}

#modalImage {
    max-height: 70vh;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentTargetField = 'from';
    let detailIndex = document.querySelectorAll('#detailTableBody tr').length;

    // 圖片放大功能
    window.openImageModal = function(imageSrc, title) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModalLabel').textContent = title;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    };

    // 處理地點選擇器點擊
    document.querySelectorAll('.location-selector').forEach(selector => {
        selector.addEventListener('click', function() {
            const targetField = this.dataset.targetField;
            currentTargetField = targetField;

            const modalTitle = document.getElementById('locationModalLabel');
            if (targetField === 'from') {
                modalTitle.textContent = '選擇出發地';
            } else if (targetField === 'to') {
                modalTitle.textContent = '選擇目的地';
            }

            loadLocations();
        });
    });

    // 載入所有地點資料
    function loadLocations() {
        const originList = document.getElementById('originList');
        const constructionList = document.getElementById('constructionList');

        originList.innerHTML = '<div class="text-muted text-center py-3"><div class="spinner-border spinner-border-sm me-2"></div>載入中...</div>';
        constructionList.innerHTML = '<div class="text-muted text-center py-3"><div class="spinner-border spinner-border-sm me-2"></div>載入中...</div>';

        Promise.all([
                fetch('<?= base_url('api/location/getLocations/0') ?>'),
                fetch('<?= base_url('api/location/getLocations/1') ?>')
            ])
            .then(responses => Promise.all(responses.map(response => response.json())))
            .then(([warehouseData, constructionData]) => {
                let originHtml = '';
                warehouseData.forEach(location => {
                    originHtml += `
                        <button type="button" class="list-group-item list-group-item-action location-option d-flex justify-content-between align-items-center" 
                                data-location-id="${location.l_id}" data-location-name="${location.l_name}">
                            <span>${location.l_name}</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>`;
                });

                if (originHtml === '') {
                    originHtml = '<div class="text-muted text-center py-3">沒有可用的倉庫</div>';
                }
                originList.innerHTML = originHtml;

                let constructionHtml = '';
                constructionData.forEach(location => {
                    constructionHtml += `
                        <button type="button" class="list-group-item list-group-item-action location-option d-flex justify-content-between align-items-center" 
                                data-location-id="${location.l_id}" data-location-name="${location.l_name}">
                            <span>${location.l_name}</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>`;
                });

                if (constructionHtml === '') {
                    constructionHtml = '<div class="text-muted text-center py-3">沒有可用的工地</div>';
                }
                constructionList.innerHTML = constructionHtml;

                // 重新綁定地點選擇事件
                document.querySelectorAll('#originList .location-option, #constructionList .location-option').forEach(button => {
                    button.addEventListener('click', function() {
                        const locationId = this.dataset.locationId;
                        const locationName = this.dataset.locationName;

                        if (currentTargetField === 'from') {
                            document.querySelector('input[name="o_from_location"]').value = locationId;
                            document.getElementById('fromLocationText').textContent = locationName;
                            document.getElementById('fromLocationText').classList.remove('text-muted');
                        } else {
                            document.querySelector('input[name="o_to_location"]').value = locationId;
                            document.getElementById('toLocationText').textContent = locationName;
                            document.getElementById('toLocationText').classList.remove('text-muted');
                        }

                        bootstrap.Modal.getInstance(document.getElementById('locationModal')).hide();
                    });
                });
            })
            .catch(error => {
                console.error('Error loading locations:', error);
                originList.innerHTML = '<div class="text-danger text-center py-3">載入失敗</div>';
                constructionList.innerHTML = '<div class="text-danger text-center py-3">載入失敗</div>';
            });
    }

    // 新增明細行
    document.getElementById('addDetailBtn').addEventListener('click', function() {
        const tbody = document.getElementById('detailTableBody');
        const newRow = createDetailRow(detailIndex);
        tbody.appendChild(newRow);
        detailIndex++;
    });

    // 創建新的明細行
    function createDetailRow(index) {
        const row = document.createElement('tr');
        row.setAttribute('data-index', index);
        row.innerHTML = `
            <td class="text-center align-middle">
                <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
            <td class="align-middle">
                <input type="hidden" name="details[${index}][od_pr_id]" value="">
                <input type="hidden" class="product-weight-per-unit" value="0">
                <div class="form-control product-selector border-dashed" data-bs-toggle="modal" 
                     data-bs-target="#productModal" data-target-index="${index}" style="cursor: pointer;">
                    <span class="product-text text-muted">請選擇產品</span>
                </div>
            </td>
            <td class="align-middle">
                <input type="number" class="form-control quantity-input" 
                       name="details[${index}][od_qty]" 
                       step="0.01" min="0" required>
            </td>
            <td class="align-middle">
                <input type="number" class="form-control length-input" 
                       name="details[${index}][od_length]" 
                       step="0.01" min="0" required>
            </td>
            <td class="align-middle">
                <input type="number" class="form-control-plaintext weight-input fw-bold text-primary" 
                       name="details[${index}][od_weight]" 
                       step="0.01" min="0" readonly>
            </td>
        `;

        bindDetailEvents(row);
        return row;
    }

    // 綁定明細行事件
    function bindDetailEvents(row) {
        row.querySelector('.remove-detail').addEventListener('click', function() {
            if (document.querySelectorAll('#detailTableBody tr').length > 1) {
                row.remove();
            } else {
                alert('至少需要保留一個明細項目');
            }
        });

        row.querySelectorAll('.quantity-input, .length-input').forEach(input => {
            input.addEventListener('input', () => calculateRowWeight(row));
        });
    }

    function calculateRowWeight(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const length = parseFloat(row.querySelector('.length-input').value) || 0;
        const weightPerUnit = parseFloat(row.querySelector('.product-weight-per-unit').value) || 0;
        
        const totalWeight = quantity * length * weightPerUnit;
        
        const weightInput = row.querySelector('.weight-input');
        weightInput.value = totalWeight.toFixed(2);
    }

    // 初始化現有明細行的事件
    document.querySelectorAll('#detailTableBody tr').forEach(row => {
        bindDetailEvents(row);
        calculateRowWeight(row);
    });

    // 初始化產品選擇器
    const productSelector = window.createProductSelector({
        modalId: 'productModal',
        fieldPrefix: 'od'
    });

    // 使重量計算函數全域可用，供產品選擇器調用
    window.calculateRowWeight = calculateRowWeight;

    // 平滑滾動到錨點
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

<?= $this->endSection() ?>