<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container my-4">
    <form action="<?= url_to('OrderController::save') ?>" method="post">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><?= $isEdit ? '編輯' : '新增' ?>訂單</h2>
            <div>
                <a href="<?= url_to('OrderController::index') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-lg me-1"></i>取消
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>保存
                </button>
            </div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($isEdit): ?>
            <input type="hidden" name="o_id" value="<?= $data['order']['o_id'] ?? old('o_id') ?>">
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">訂單資訊</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">類型</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="o_type" id="o_type_0" value="0" autocomplete="off" <?= old('o_type', $data['order']['o_type'] ?? '') == '0' ? 'checked' : '' ?> required>
                            <label class="btn btn-outline-primary" for="o_type_0">進倉庫</label>
                            <input type="radio" class="btn-check" name="o_type" id="o_type_1" value="1" autocomplete="off" <?= old('o_type', $data['order']['o_type'] ?? '') == '1' ? 'checked' : '' ?> required>
                            <label class="btn btn-outline-primary" for="o_type_1">出倉庫</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">出發地</label>
                        <input type="hidden" name="o_from_location" value="<?= old('o_from_location', $data['order']['o_from_location'] ?? '') ?>" required>
                        <div class="form-control location-selector d-flex justify-content-between align-items-center" id="fromLocationDisplay" data-bs-toggle="modal" data-bs-target="#locationModal" data-target-field="from" style="cursor: pointer;">
                            <span id="fromLocationText"><?= $isEdit && isset($data['order']['from_location_name']) ? $data['order']['from_location_name'] : '請選擇地點' ?></span>
                            <i class="bi bi-geo-alt text-muted"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">目的地</label>
                        <input type="hidden" name="o_to_location" value="<?= old('o_to_location', $data['order']['o_to_location'] ?? '') ?>" required>
                        <div class="form-control location-selector d-flex justify-content-between align-items-center" id="toLocationDisplay" data-bs-toggle="modal" data-bs-target="#locationModal" data-target-field="to" style="cursor: pointer;">
                            <span id="toLocationText"><?= $isEdit && isset($data['order']['to_location_name']) ? $data['order']['to_location_name'] : '請選擇地點' ?></span>
                            <i class="bi bi-geo-alt text-muted"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="o_date">日期</label>
                        <input type="date" class="form-control" name="o_date" id="o_date" value="<?= old('o_date', $data['order']['o_date'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="o_car_number" class="form-label">車號</label>
                        <input type="text" class="form-control" name="o_car_number" id="o_car_number" value="<?= old('o_car_number', $data['order']['o_car_number'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="o_driver_phone" class="form-label">司機電話</label>
                        <input type="text" class="form-control" name="o_driver_phone" id="o_driver_phone" value="<?= old('o_driver_phone', $data['order']['o_driver_phone'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="o_g_id" class="form-label">GPS</label>
                        <select class="form-select" name="o_g_id" id="o_g_id" required>
                            <option value="">請選擇</option>
                            <?php foreach ($data['gpsOptions'] as $gps): ?>
                                <option value="<?= $gps['g_id'] ?>" <?= old('o_g_id', $data['order']['o_g_id'] ?? '') == $gps['g_id'] ? 'selected' : '' ?>>
                                    <?= esc($gps['g_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="o_loading_time" class="form-label">上料時間</label>
                        <input type="datetime-local" class="form-control" name="o_loading_time" id="o_loading_time" value="<?= old('o_loading_time', $data['order']['o_loading_time'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="o_unloading_time" class="form-label">下料時間</label>
                        <input type="datetime-local" class="form-control" name="o_unloading_time" id="o_unloading_time" value="<?= old('o_unloading_time', $data['order']['o_unloading_time'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="o_oxygen" class="form-label">氧氣</label>
                        <input type="number" class="form-control" name="o_oxygen" id="o_oxygen" value="<?= old('o_oxygen', $data['order']['o_oxygen'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="o_acetylene" class="form-label">乙炔</label>
                        <input type="number" class="form-control" name="o_acetylene" id="o_acetylene" value="<?= old('o_acetylene', $data['order']['o_acetylene'] ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="o_remark" class="form-label">備註</label>
                        <textarea class="form-control" name="o_remark" id="o_remark" rows="3"><?= old('o_remark', $data['order']['o_remark'] ?? '') ?></textarea>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="col-md-3">
                            <label class="form-label">建立者</label>
                            <p class="form-control-plaintext"><?= esc($data['order']['create_name'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">建立時間</label>
                            <p class="form-control-plaintext"><?= esc($data['order']['o_create_at']) ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">更新者</label>
                            <p class="form-control-plaintext"><?= esc($data['order']['update_name'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">更新時間</label>
                            <p class="form-control-plaintext"><?= esc($data['order']['o_update_at'] ?? 'N/A') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">訂單明細</h5>
                    <button type="button" class="btn btn-success btn-sm" id="addDetailBtn">
                        <i class="bi bi-plus-lg"></i> 新增明細
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="detailTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%;">操作</th>
                                <th style="width: 45%;">產品</th>
                                <th style="width: 15%;">數量</th>
                                <th style="width: 15%;">長度</th>
                                <th style="width: 20%;">重量</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            <?php if ($isEdit && isset($data['orderDetails']) && !empty($data['orderDetails'])): ?>
                                <?php foreach ($data['orderDetails'] as $index => $detail): ?>
                                    <tr data-index="<?= $index ?>">
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-detail">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <input type="hidden" name="details[<?= $index ?>][od_id]" value="<?= $detail['od_id'] ?? '' ?>">
                                            <input type="hidden" name="details[<?= $index ?>][od_pr_id]" value="<?= $detail['od_pr_id'] ?? '' ?>">
                                            <input type="hidden" class="product-weight-per-unit" value="<?= $detail['pr_weight_per_unit'] ?? 0 ?>">
                                            <div class="form-control product-selector" data-bs-toggle="modal" data-bs-target="#productModal" data-target-index="<?= $index ?>" style="cursor: pointer;">
                                                <span class="product-text"><?= isset($detail['pr_name']) ? esc($detail['pr_name']) : '請選擇產品' ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantity-input"
                                                name="details[<?= $index ?>][od_qty]"
                                                value="<?= $detail['od_qty'] ?>"
                                                step="0.01" min="0" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control length-input"
                                                name="details[<?= $index ?>][od_length]"
                                                value="<?= $detail['od_length'] ?>"
                                                step="0.01" min="0" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control-plaintext weight-input"
                                                name="details[<?= $index ?>][od_weight]"
                                                value="<?= $detail['od_weight'] ?>"
                                                step="0.01" min="0" readonly>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr data-index="0">
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-detail">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <input type="hidden" name="details[0][od_id]" value="">
                                        <input type="hidden" name="details[0][od_pr_id]" value="">
                                        <input type="hidden" class="product-weight-per-unit" value="0">
                                        <div class="form-control product-selector" data-bs-toggle="modal" data-bs-target="#productModal" data-target-index="0" style="cursor: pointer;">
                                            <span class="product-text">請選擇產品</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control quantity-input"
                                            name="details[0][od_qty]"
                                            step="0.01" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control length-input"
                                            name="details[0][od_length]"
                                            step="0.01" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control-plaintext weight-input"
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

    </form>
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

<!-- 產品選擇 Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="productModalLabel">選擇產品</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- 步驟指示器 -->
                <div class="d-flex justify-content-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="step-indicator active" id="step1Indicator">
                            <span class="step-number">1</span>
                            <span class="step-text">大分類</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" id="step2Indicator">
                            <span class="step-number">2</span>
                            <span class="step-text">小分類</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" id="step3Indicator">
                            <span class="step-number">3</span>
                            <span class="step-text">產品</span>
                        </div>
                    </div>
                </div>

                <!-- 步驟內容 -->
                <div class="step-content">
                    <!-- 步驟 1: 選擇大分類 -->
                    <div class="step-pane active" id="step1">
                        <h6 class="mb-3 text-center text-muted">請選擇大分類</h6>
                        <div class="row g-2" id="majorCategoryList">
                            <div class="col-12 text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">載入中...</span>
                                </div>
                                <div class="mt-2 text-muted">載入大分類中...</div>
                            </div>
                        </div>
                    </div>

                    <!-- 步驟 2: 選擇小分類 -->
                    <div class="step-pane" id="step2">
                        <div class="d-flex align-items-center mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-3" id="backToStep1">
                                <i class="bi bi-arrow-left"></i> 返回
                            </button>
                            <h6 class="mb-0 text-muted">請選擇小分類</h6>
                        </div>
                        <div class="row g-2" id="minorCategoryList">
                            <!-- 動態載入小分類 -->
                        </div>
                    </div>

                    <!-- 步驟 3: 選擇產品 -->
                    <div class="step-pane" id="step3">
                        <div class="d-flex align-items-center mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-3" id="backToStep2">
                                <i class="bi bi-arrow-left"></i> 返回
                            </button>
                            <h6 class="mb-0 text-muted">請選擇產品</h6>
                        </div>
                        <div class="list-group" id="productList">
                            <!-- 動態載入產品 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* 選擇器樣式 */
    .location-selector, .product-selector {
        transition: all 0.2s ease;
    }

    .location-selector:hover, .product-selector:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
    }

    /* 步驟指示器樣式 */
    .step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        width: 80px;
    }

    .step-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: var(--bs-gray-200);
        color: var(--bs-gray-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: 2px solid var(--bs-gray-300);
    }

    .step-indicator.active .step-number {
        background-color: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }

    .step-indicator.completed .step-number {
        background-color: var(--bs-success);
        color: white;
        border-color: var(--bs-success);
    }

    .step-text {
        margin-top: 8px;
        font-size: 0.875rem;
        color: var(--bs-secondary-color);
        font-weight: 500;
        text-align: center;
    }

    .step-indicator.active .step-text {
        color: var(--bs-primary);
        font-weight: 600;
    }

    .step-indicator.completed .step-text {
        color: var(--bs-success);
    }

    .step-connector {
        flex-grow: 1;
        height: 2px;
        background-color: var(--bs-gray-300);
        margin: 0 5px;
        position: relative;
        top: -1.25rem;
    }

    /* 步驟內容樣式 */
    .step-content {
        min-height: 300px;
        position: relative;
    }

    .step-pane {
        display: none;
    }

    .step-pane.active {
        display: block;
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* 分類卡片樣式 */
    .category-card {
        border: 1px solid var(--bs-border-color);
        border-radius: var(--bs-card-border-radius);
        padding: 1rem;
        text-align: center;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        background-color: var(--bs-body-bg);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--bs-box-shadow-sm);
        border-color: var(--bs-primary);
    }

    .category-card h6 {
        margin: 0;
        color: var(--bs-body-color);
        font-weight: 500;
        font-size: 0.95rem;
    }

    .category-card:hover h6 {
        color: var(--bs-primary);
    }

    /* 產品列表樣式 */
    .product-item {
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .product-item:hover {
        background-color: var(--bs-primary-bg-subtle);
        transform: translateX(4px);
    }

    .product-item h6 {
        font-size: 1rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentTargetField = 'from'; // 當前要設定的欄位

        // 處理地點選擇器點擊
        document.querySelectorAll('.location-selector').forEach(selector => {
            selector.addEventListener('click', function() {
                const targetField = this.dataset.targetField;
                currentTargetField = targetField;

                // 更新 modal 標題
                const modalTitle = document.getElementById('locationModalLabel');
                if (targetField === 'from') {
                    modalTitle.textContent = '選擇出發地';
                } else if (targetField === 'to') {
                    modalTitle.textContent = '選擇目的地';
                }

                // 載入地點資料
                loadLocations();
            });
        });

        // 載入所有地點資料
        function loadLocations() {
            const originList = document.getElementById('originList');
            const constructionList = document.getElementById('constructionList');

            originList.innerHTML = '<div class="text-muted text-center py-3">載入中...</div>';
            constructionList.innerHTML = '<div class="text-muted text-center py-3">載入中...</div>';

            // 分別載入倉庫(0)和工地(1)資料
            Promise.all([
                    fetch('<?= url_to('App\Controllers\Api\LocationController::getLocations', 0) ?>'), // 倉庫
                    fetch('<?= url_to('App\Controllers\Api\LocationController::getLocations', 1) ?>') // 工地
                ])
                .then(responses => Promise.all(responses.map(response => response.json())))
                .then(([warehouseData, constructionData]) => {
                    // 處理倉庫資料
                    let originHtml = '';
                    warehouseData.forEach(location => {
                        originHtml +=
                            '<button type="button" class="list-group-item list-group-item-action location-option" ' +
                            'data-location-id="' + location.l_id + '" ' +
                            'data-location-name="' + location.l_name + '">' +
                            location.l_name +
                            '</button>';
                    });

                    if (originHtml === '') {
                        originHtml = '<div class="text-muted text-center py-3">沒有可用的倉庫</div>';
                    }
                    originList.innerHTML = originHtml;

                    // 處理工地資料
                    let constructionHtml = '';
                    constructionData.forEach(location => {
                        constructionHtml +=
                            '<button type="button" class="list-group-item list-group-item-action location-option" ' +
                            'data-location-id="' + location.l_id + '" ' +
                            'data-location-name="' + location.l_name + '">' +
                            location.l_name +
                            '</button>';
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

                            // 根據當前目標欄位設定值
                            if (currentTargetField === 'from') {
                                document.querySelector('input[name="o_from_location"]').value = locationId;
                                document.getElementById('fromLocationText').textContent = locationName;
                            } else {
                                document.querySelector('input[name="o_to_location"]').value = locationId;
                                document.getElementById('toLocationText').textContent = locationName;
                            }

                            // 關閉 modal
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

        // 明細列表功能
        let detailIndex = document.querySelectorAll('#detailTableBody tr').length;

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
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-detail">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
                <td>
                    <input type="hidden" name="details[${index}][od_id]" value="">
                    <input type="hidden" name="details[${index}][od_pr_id]" value="">
                    <input type="hidden" class="product-weight-per-unit" value="0">
                    <div class="form-control product-selector" data-bs-toggle="modal" data-bs-target="#productModal" data-target-index="${index}" style="cursor: pointer;">
                        <span class="product-text">請選擇產品</span>
                    </div>
                </td>
                <td>
                    <input type="number" class="form-control quantity-input" 
                           name="details[${index}][od_qty]" 
                           step="0.01" min="0" required>
                </td>
                <td>
                    <input type="number" class="form-control length-input" 
                           name="details[${index}][od_length]" 
                           step="0.01" min="0" required>
                </td>
                <td>
                    <input type="number" class="form-control-plaintext weight-input" 
                           name="details[${index}][od_weight]" 
                           step="0.01" min="0" readonly>
                </td>
            `;

            bindDetailEvents(row);

            // 更新新行的產品選擇器事件綁定
            const productSelector = row.querySelector('.product-selector');
            if (productSelector) {
                productSelector.dataset.targetIndex = index;
            }

            return row;
        }

        // 綁定明細行事件
        function bindDetailEvents(row) {
            // 刪除按鈕事件
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

        // 產品選擇功能
        let currentTargetIndex = 0;
        let selectedMajorCategory = null;
        let selectedMinorCategory = null;

        // 處理產品選擇器點擊
        document.addEventListener('click', function(e) {
            if (e.target.closest('.product-selector')) {
                const selector = e.target.closest('.product-selector');
                currentTargetIndex = selector.dataset.targetIndex;
                resetProductModal();
                loadMajorCategories();
            }
        });

        // 重置產品選擇Modal
        function resetProductModal() {
            selectedMajorCategory = null;
            selectedMinorCategory = null;

            // 重置步驟指示器
            document.querySelectorAll('.step-indicator').forEach(indicator => {
                indicator.classList.remove('active', 'completed');
            });
            document.getElementById('step1Indicator').classList.add('active');

            // 重置步驟內容
            document.querySelectorAll('.step-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById('step1').classList.add('active');
        }

        // 載入大分類
        function loadMajorCategories() {
            const majorCategoryList = document.getElementById('majorCategoryList');
            majorCategoryList.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                    <p class="mt-2 text-muted">載入大分類中...</p>
                </div>
            `;

            fetch('<?= base_url('api/majorCategory/getMajorCategories') ?>')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.forEach(category => {
                        html += `
                            <div class="col-6 col-md-4 col-lg-3 mb-3">
                                <div class="category-card" data-major-id="${category.mc_id}">
                                    <h6>${category.mc_name}</h6>
                                </div>
                            </div>
                        `;
                    });

                    if (html === '') {
                        html = '<div class="col-12 text-center py-4 text-muted">沒有可用的大分類</div>';
                    }

                    majorCategoryList.innerHTML = html;

                    // 綁定大分類點擊事件
                    document.querySelectorAll('.category-card[data-major-id]').forEach(card => {
                        card.addEventListener('click', function() {
                            selectedMajorCategory = this.dataset.majorId;
                            goToStep(2);
                            loadMinorCategories(selectedMajorCategory);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading major categories:', error);
                    majorCategoryList.innerHTML = '<div class="col-12 text-center py-4 text-danger">載入失敗</div>';
                });
        }

        // 載入小分類
        function loadMinorCategories(majorCategoryId) {
            const minorCategoryList = document.getElementById('minorCategoryList');
            minorCategoryList.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                    <p class="mt-2 text-muted">載入小分類中...</p>
                </div>
            `;

            fetch(`<?= base_url('api/minorCategory/getMinorCategories') ?>/${majorCategoryId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.forEach(category => {
                        html += `
                            <div class="col-6 col-md-4 col-lg-3 mb-3">
                                <div class="category-card" data-minor-id="${category.mic_id}">
                                    <h6>${category.mic_name}</h6>
                                </div>
                            </div>
                        `;
                    });

                    if (html === '') {
                        html = '<div class="col-12 text-center py-4 text-muted">沒有可用的小分類</div>';
                    }

                    minorCategoryList.innerHTML = html;

                    // 綁定小分類點擊事件
                    document.querySelectorAll('.category-card[data-minor-id]').forEach(card => {
                        card.addEventListener('click', function() {
                            selectedMinorCategory = this.dataset.minorId;
                            goToStep(3);
                            loadProducts(selectedMinorCategory);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading minor categories:', error);
                    minorCategoryList.innerHTML = '<div class="col-12 text-center py-4 text-danger">載入失敗</div>';
                });
        }

        // 載入產品
        function loadProducts(minorCategoryId) {
            const productList = document.getElementById('productList');
            productList.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                    <p class="mt-2 text-muted">載入產品中...</p>
                </div>
            `;

            fetch(`<?= base_url('api/product/getProducts') ?>/${minorCategoryId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.forEach(product => {
                        html += `
                            <button type="button" class="list-group-item list-group-item-action product-item" 
                                    data-product-id="${product.pr_id}" 
                                    data-product-name="${product.pr_name}"
                                    data-weight-per-unit="${product.pr_weight_per_unit || 0}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">${product.pr_name}</h6>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </button>
                        `;
                    });

                    if (html === '') {
                        html = '<div class="text-center py-4 text-muted">沒有可用的產品</div>';
                    }

                    productList.innerHTML = html;

                    // 綁定產品點擊事件
                    document.querySelectorAll('.product-item[data-product-id]').forEach(item => {
                        item.addEventListener('click', function() {
                            const productId = this.dataset.productId;
                            const productName = this.dataset.productName;
                            const weightPerUnit = this.dataset.weightPerUnit;

                            // 設定選中的產品到對應的明細行
                            const targetRow = document.querySelector(`tr[data-index="${currentTargetIndex}"]`);
                            if (targetRow) {
                                targetRow.querySelector('input[name*="[od_pr_id]"]').value = productId;
                                targetRow.querySelector('.product-text').textContent = productName;
                                targetRow.querySelector('.product-weight-per-unit').value = weightPerUnit;
                                calculateRowWeight(targetRow);
                            }

                            // 關閉 modal
                            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    productList.innerHTML = '<div class="text-center py-4 text-danger">載入失敗</div>';
                });
        }

        // 步驟切換功能
        function goToStep(stepNumber) {
            // 更新步驟指示器
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                indicator.classList.remove('active', 'completed');
                if (index < stepNumber - 1) {
                    indicator.classList.add('completed');
                } else if (index === stepNumber - 1) {
                    indicator.classList.add('active');
                }
            });

            // 更新步驟內容
            document.querySelectorAll('.step-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById(`step${stepNumber}`).classList.add('active');
        }

        // 返回按鈕事件
        document.getElementById('backToStep1').addEventListener('click', function() {
            goToStep(1);
        });

        document.getElementById('backToStep2').addEventListener('click', function() {
            goToStep(2);
        });
    });
</script>

<?= $this->endSection() ?>