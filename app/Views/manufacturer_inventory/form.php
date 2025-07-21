<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>租賃庫存</h2>
    </div>
    <!-- 顯示錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- 表單 -->
    <form action="<?= url_to('ManufacturerInventoryController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="mi_id" value="<?= $data['mi_id'] ?? old('mi_id') ?>">
        <?php endif; ?>

        <?php if ($isEdit): ?>
            <!-- 編輯模式：顯示為只讀欄位 -->
            <div class="mb-3">
                <label class="form-label text-muted">大分類</label>
                <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['mc_name'] ?? 'N/A') ?></p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">小分類</label>
                <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['mic_name'] ?? 'N/A') ?></p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">品名</label>
                <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['pr_name'] ?? 'N/A') ?></p>
                <input type="hidden" name="mi_pr_id" value="<?= $data['mi_pr_id'] ?? '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">廠商</label>
                <p class="form-control-plaintext border-bottom pb-2"><?= esc($data['ma_name'] ?? 'N/A') ?></p>
                <input type="hidden" name="mi_ma_id" value="<?= $data['mi_ma_id'] ?? '' ?>">
            </div>
        <?php else: ?>
            <!-- 新增模式：正常的下拉選單 -->
            <div class="mb-3">
                <label for="major_category" class="form-label">大分類</label>
                <select class="form-select" id="major_category" name="major_category" required>
                    <option value="">請選擇大分類</option>
                    <?php if (isset($majorCategories)): ?>
                        <?php foreach ($majorCategories as $majorCategory): ?>
                            <option value="<?= $majorCategory['mc_id'] ?>"><?= esc($majorCategory['mc_name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="minor_category" class="form-label">小分類</label>
                <select class="form-select" id="minor_category" name="minor_category" required disabled>
                    <option value="">請先選擇大分類</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="mi_pr_id" class="form-label">品名</label>
                <select class="form-select" id="mi_pr_id" name="mi_pr_id" required disabled>
                    <option value="">請先選擇小分類</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="mi_ma_id" class="form-label">廠商</label>
                <select class="form-select" id="mi_ma_id" name="mi_ma_id" required>
                    <option value="">請選擇廠商</option>
                    <?php if (isset($manufacturers)): ?>
                        <?php foreach ($manufacturers as $manufacturer): ?>
                            <option value="<?= $manufacturer['ma_id'] ?>"
                                <?= (old('mi_ma_id', $data['mi_ma_id'] ?? '') == $manufacturer['ma_id']) ? 'selected' : '' ?>>
                                <?= esc($manufacturer['ma_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="mb-3">
            <label for="mi_initial" class="form-label">初始租賃數量</label>
            <input type="number" class="form-control" id="mi_initial" name="mi_initial" value="<?= old('mi_initial', $data['mi_initial'] ?? 0) ?>" required min="0">
        </div>

        <?php if ($isEdit): ?>
            <div class="mb-3">
                <label for="mi_qty" class="form-label">目前租賃數量</label>
                <input type="number" class="form-control" id="mi_qty" name="mi_qty" value="<?= old('mi_qty', $data['mi_qty'] ?? 0) ?>" required min="0" readonly>
                <div class="form-text">修改初始租賃數量時，目前租賃數量會自動調整</div>
            </div>
        <?php else: ?>
            <!-- 新增時隱藏數量欄位，數量等於初始庫存 -->
            <input type="hidden" id="mi_qty" name="mi_qty" value="<?= old('mi_qty', old('mi_initial', 0)) ?>">
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="<?= url_to('ManufacturerInventoryController::index') ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initialInput = document.getElementById('mi_initial');
        const qtyInput = document.getElementById('mi_qty');

        <?php if ($isEdit && isset($data)): ?>
            // 編輯模式：記錄原始的初始庫存值，用於計算差額
            let originalInitial = <?= $data['mi_initial'] ?? 0 ?>;

            // 初始庫存變更處理（編輯模式）
            initialInput.addEventListener('input', function() {
                const newInitial = parseInt(this.value) || 0;
                const currentQty = parseInt(qtyInput.value) || 0;
                const difference = newInitial - originalInitial;
                const newQty = Math.max(0, currentQty + difference);

                qtyInput.value = newQty;
                originalInitial = newInitial; // 更新原始值
            });
        <?php else: ?>
            // 新增模式：三級聯動選單和數量同步
            const majorCategorySelect = document.getElementById('major_category');
            const minorCategorySelect = document.getElementById('minor_category');
            const productSelect = document.getElementById('mi_pr_id');

            // 頁面載入時初始化數量值
            const initialValue = parseInt(initialInput.value) || 0;
            qtyInput.value = initialValue;

            // 初始庫存變更處理（新增模式）
            initialInput.addEventListener('input', function() {
                const newInitial = parseInt(this.value) || 0;
                qtyInput.value = newInitial;
            });

            // 載入小分類的函數
            function loadMinorCategories(mcId) {
                minorCategorySelect.innerHTML = '<option value="">載入中...</option>';
                minorCategorySelect.disabled = true;
                productSelect.innerHTML = '<option value="">請先選擇小分類</option>';
                productSelect.disabled = true;

                if (mcId) {
                    fetch('<?= base_url('minorCategory/getMinorCategories') ?>' + '/' + mcId, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                minorCategorySelect.innerHTML = '<option value="">請選擇小分類</option>';
                                data.data.forEach(function(item) {
                                    const option = document.createElement('option');
                                    option.value = item.mic_id;
                                    option.textContent = item.mic_name;
                                    minorCategorySelect.appendChild(option);
                                });
                                minorCategorySelect.disabled = false;
                            } else {
                                minorCategorySelect.innerHTML = '<option value="">載入失敗</option>';
                                console.error('Error:', data.message);
                            }
                        })
                        .catch(error => {
                            minorCategorySelect.innerHTML = '<option value="">載入失敗</option>';
                            console.error('Error:', error);
                        });
                } else {
                    minorCategorySelect.innerHTML = '<option value="">請先選擇大分類</option>';
                }
            }

            // 載入產品的函數
            function loadProducts(micId) {
                productSelect.innerHTML = '<option value="">載入中...</option>';
                productSelect.disabled = true;

                if (micId) {
                    fetch('<?= base_url('api/product/getProducts') ?>' + '/' + micId, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                                productSelect.innerHTML = '<option value="">請選擇品名</option>';
                                data.forEach(function(item) {
                                    const option = document.createElement('option');
                                    option.value = item.pr_id;
                                    option.textContent = item.pr_name;
                                    productSelect.appendChild(option);
                                });
                                productSelect.disabled = false;
                        })
                        .catch(error => {
                            productSelect.innerHTML = '<option value="">載入失敗</option>';
                            console.error('Error:', error);
                        });
                } else {
                    productSelect.innerHTML = '<option value="">請先選擇小分類</option>';
                }
            }

            // 大分類變更事件
            majorCategorySelect.addEventListener('change', function() {
                const mcId = this.value;
                loadMinorCategories(mcId);
            });

            // 小分類變更事件
            minorCategorySelect.addEventListener('change', function() {
                const micId = this.value;
                loadProducts(micId);
            });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>