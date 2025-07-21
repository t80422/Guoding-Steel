<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- 頁面標題區 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= $isEdit ? '編輯' : '新增' ?>租賃訂單</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url_to('RentalController::index_order') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>返回列表
            </a>
            <button type="submit" form="rentalForm" class="btn btn-primary">
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

    <form id="rentalForm" action="<?= url_to('RentalController::saveOrder') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="ro_id" value="<?= $data['rental']['ro_id'] ?? old('ro_id') ?>">
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
                            <!-- 類型 -->
                            <div class="col-12">
                                <label class="form-label fw-bold">類型</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="ro_type" id="ro_type_0" value="0"
                                        autocomplete="off" <?= old('ro_type', $data['rental']['ro_type'] ?? '') == '0' ? 'checked' : '' ?> required>
                                    <label class="btn btn-outline-primary" for="ro_type_0">
                                        <i class="bi bi-box-arrow-in-down me-1"></i>進工地
                                    </label>
                                    <input type="radio" class="btn-check" name="ro_type" id="ro_type_1" value="1"
                                        autocomplete="off" <?= old('ro_type', $data['rental']['ro_type'] ?? '') == '1' ? 'checked' : '' ?> required>
                                    <label class="btn btn-outline-primary" for="ro_type_1">
                                        <i class="bi bi-box-arrow-up me-1"></i>出工地
                                    </label>
                                </div>
                            </div>
                            <!-- 廠商 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-building me-1"></i>廠商
                                </label>
                                <select class="form-select" name="ro_ma_id" required>
                                    <option value="">請選擇</option>
                                    <?php foreach ($data['manufacturerOptions'] as $item): ?>
                                        <option value="<?= $item['ma_id'] ?>" <?= old('ro_ma_id', ($isEdit && isset($data['rental']['ro_ma_id']) ? $data['rental']['ro_ma_id'] : '')) == $item['ma_id'] ? 'selected' : '' ?>>
                                            <?= esc($item['ma_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- 工地 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-geo-alt me-1"></i>工地
                                </label>
                                <select class="form-select" name="ro_l_id" required>
                                    <option value="">請選擇</option>
                                    <?php foreach ($data['locationOptions'] as $item): ?>
                                        <option value="<?= $item['l_id'] ?>" <?= old('ro_l_id', ($isEdit && isset($data['rental']['ro_l_id']) ? $data['rental']['ro_l_id'] : '')) == $item['l_id'] ? 'selected' : '' ?>>
                                            <?= esc($item['l_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- 日期時間 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="r_date">
                                    <i class="bi bi-calendar3 me-1"></i>日期
                                </label>
                                <input type="date" class="form-control" name="ro_date" id="ro_date"
                                    value="<?= old('ro_date', $data['rental']['ro_date'] ?? '') ?>" required>
                            </div>
                            <!-- GPS -->
                            <div class="col-md-6">
                                <label for="r_g_id" class="form-label fw-bold">
                                    <i class="bi bi-broadcast me-1"></i>GPS
                                </label>
                                <select class="form-select" name="ro_g_id" id="ro_g_id" required>
                                    <option value="">請選擇</option>
                                    <?php foreach ($data['gpsOptions'] as $gps): ?>
                                        <option value="<?= $gps['g_id'] ?>" <?= old('ro_g_id', $data['rental']['ro_g_id'] ?? '') == $gps['g_id'] ? 'selected' : '' ?>>
                                            <?= esc($gps['g_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- 車號 -->
                            <div class="col-md-6">
                                <label for="r_car_number" class="form-label fw-bold">
                                    <i class="bi bi-truck me-1"></i>車號
                                </label>
                                <input type="text" class="form-control" name="ro_car_number" id="ro_car_number"
                                    value="<?= old('ro_car_number', $data['rental']['ro_car_number'] ?? '') ?>"
                                    placeholder="請輸入車牌號碼" required>
                            </div>
                            <!-- 司機電話 -->
                            <div class="col-md-6">
                                <label for="r_driver_phone" class="form-label fw-bold">
                                    <i class="bi bi-telephone me-1"></i>司機電話
                                </label>
                                <input type="text" class="form-control" name="ro_driver_phone" id="ro_driver_phone"
                                    value="<?= old('ro_driver_phone', $data['rental']['ro_driver_phone'] ?? '') ?>"
                                    placeholder="請輸入聯絡電話" required>
                            </div>
                            <!-- 上料時間 -->
                            <div class="col-md-6">
                                <label for="r_loading_time" class="form-label fw-bold">
                                    <i class="bi bi-clock me-1"></i>上料時間
                                </label>
                                <input type="datetime-local" class="form-control" name="ro_loading_time" id="ro_loading_time"
                                    value="<?= old('ro_loading_time', $data['rental']['ro_loading_time'] ?? '') ?>" required>
                            </div>
                            <!-- 下料時間 -->
                            <div class="col-md-6">
                                <label for="r_unloading_time" class="form-label fw-bold">
                                    <i class="bi bi-clock me-1"></i>下料時間
                                </label>
                                <input type="datetime-local" class="form-control" name="ro_unloading_time" id="ro_unloading_time"
                                    value="<?= old('ro_unloading_time', $data['rental']['ro_unloading_time'] ?? '') ?>" required>
                            </div>
                            <!-- 氧氣 -->
                            <div class="col-md-6">
                                <label for="r_oxygen" class="form-label fw-bold">
                                    <i class="bi bi-droplet me-1"></i>氧氣(公斤)
                                </label>
                                <input type="number" class="form-control" name="ro_oxygen" id="ro_oxygen"
                                    value="<?= old('ro_oxygen', $data['rental']['ro_oxygen'] ?? '') ?>"
                                    placeholder="0" step="0.01" min="0" required>
                            </div>
                            <!-- 乙炔 -->
                            <div class="col-md-6">
                                <label for="r_acetylene" class="form-label fw-bold">
                                    <i class="bi bi-fire me-1"></i>乙炔(公斤)
                                </label>
                                <input type="number" class="form-control" name="ro_acetylene" id="ro_acetylene"
                                    value="<?= old('ro_acetylene', $data['rental']['ro_acetylene'] ?? '') ?>"
                                    placeholder="0" step="0.01" min="0" required>
                            </div>
                            <!-- 備註 -->
                            <div class="col-12">
                                <label for="r_remark" class="form-label fw-bold">
                                    <i class="bi bi-chat-text me-1"></i>備註說明
                                </label>
                                <textarea class="form-control" name="ro_memo" id="ro_memo" rows="3"
                                    placeholder="請輸入備註說明..."><?= old('ro_memo', $data['rental']['ro_memo'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 明細卡片 -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-ul me-2 text-primary"></i>租賃明細
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
                                    <?php if ($isEdit && isset($data['rentalDetails']) && !empty($data['rentalDetails'])): ?>
                                        <?php foreach ($data['rentalDetails'] as $index => $detail): ?>
                                            <tr data-index="<?= $index ?>">
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                                <td class="align-middle">
                                                    <?php if ($isEdit && !empty($detail['rod_id'])): ?>
                                                        <input type="hidden" name="details[<?= $index ?>][rod_id]" value="<?= $detail['rod_id'] ?>">
                                                    <?php endif; ?>
                                                    <input type="hidden" name="details[<?= $index ?>][rod_pr_id]" value="<?= $detail['rod_pr_id'] ?? '' ?>">
                                                    <input type="hidden" class="product-weight-per-unit" value="<?= $detail['pr_weight_per_unit'] ?? 0 ?>">
                                                    <div class="form-control product-selector border-dashed" data-bs-toggle="modal"
                                                        data-bs-target="#productModal" data-target-index="<?= $index ?>" style="cursor: pointer;">
                                                        <span class="product-text"><?= isset($detail['pr_name']) ? esc($detail['pr_name']) : '請選擇產品' ?></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control quantity-input"
                                                        name="details[<?= $index ?>][rod_qty]"
                                                        value="<?= $detail['rod_qty'] ?>"
                                                        step="0.01" min="0" required>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control length-input"
                                                        name="details[<?= $index ?>][rod_length]"
                                                        value="<?= $detail['rod_length'] ?>"
                                                        step="0.01" min="0" required>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control-plaintext weight-input fw-bold text-primary"
                                                        name="details[<?= $index ?>][rod_weight]"
                                                        value="<?= $detail['rod_weight'] ?>"
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
                                                <input type="hidden" name="details[0][rod_pr_id]" value="">
                                                <input type="hidden" class="product-weight-per-unit" value="0">
                                                <div class="form-control product-selector border-dashed" data-bs-toggle="modal"
                                                    data-bs-target="#productModal" data-target-index="0" style="cursor: pointer;">
                                                    <span class="product-text text-muted">請選擇產品</span>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control quantity-input"
                                                    name="details[0][rod_qty]"
                                                    step="0.01" min="0" required>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control length-input"
                                                    name="details[0][rod_length]"
                                                    step="0.01" min="0" required>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control-plaintext weight-input fw-bold text-primary"
                                                    name="details[0][rod_weight]"
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
                                        <p class="fw-bold mb-2"><?= esc($data['rental']['creator'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">建立時間</label>
                                        <p class="fw-bold mb-2"><?= esc($data['rental']['ro_create_at']) ?></p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">更新者</label>
                                        <p class="fw-bold mb-2"><?= esc($data['rental']['updater'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">更新時間</label>
                                        <p class="fw-bold mb-0"><?= esc($data['rental']['ro_update_at'] ?? 'N/A') ?></p>
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
<?= $this->include('components/product_selector', [
    'modalId' => 'productModal',
    'fieldPrefix' => 'rod'
]) ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
            <td class="text-center align-middle">
                <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
            <td class="align-middle">
                <input type="hidden" name="details[${index}][rod_pr_id]" value="">
                <input type="hidden" class="product-weight-per-unit" value="0">
                <div class="form-control product-selector border-dashed" data-bs-toggle="modal" 
                     data-bs-target="#productModal" data-target-index="${index}" style="cursor: pointer;">
                    <span class="product-text text-muted">請選擇產品</span>
                </div>
            </td>
            <td class="align-middle">
                <input type="number" class="form-control quantity-input" 
                       name="details[${index}][rod_qty]" 
                       step="0.01" min="0" required>
            </td>
            <td class="align-middle">
                <input type="number" class="form-control length-input" 
                       name="details[${index}][rod_length]" 
                       step="0.01" min="0" required>
            </td>
            <td class="align-middle">
                <input type="number" class="form-control-plaintext weight-input fw-bold text-primary" 
                       name="details[${index}][rod_weight]" 
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
            fieldPrefix: 'rod'
        });

        // 使重量計算函數全域可用，供產品選擇器調用
        window.calculateRowWeight = calculateRowWeight;
    });
</script>

<?= $this->endSection() ?>