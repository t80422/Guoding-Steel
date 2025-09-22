<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?= $isEdit ? '編輯' : '新增' ?>產品</h2>
    </div>

    <!-- 錯誤訊息 -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('ProductController::save') ?>" method="post">
        <?php if ($isEdit): ?>
            <input type="hidden" name="pr_id" value="<?= $data['pr_id'] ?? old('pr_id') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="prName" class="form-label">名稱</label>
            <input type="text" class="form-control" name="pr_name" value="<?= old('pr_name', $data['pr_name'] ?? '') ?>" required>
            <?php if (isset(session()->getFlashdata('errors')['pr_name'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['pr_name'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="mcId" class="form-label">大分類</label>
            <select class="form-select" id="majorCategorySelect" name="pr_mc_id" required>
                <option value="">請選擇</option>
                <?php foreach ($majorCategories as $item): ?>
                    <option value="<?= $item['mc_id'] ?>" <?= old('pr_mc_id', ($isEdit && isset($mcId) ? $mcId : '')) == $item['mc_id'] ? 'selected' : '' ?>>
                        <?= esc($item['mc_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="prMicId" class="form-label">小分類</label>
            <select class="form-select" name="pr_mic_id" id="minorCategorySelect" required>
                <option value="">請選擇</option>
            </select>
            <?php if (isset(session()->getFlashdata('errors')['pr_mic_id'])): ?>
                <div class="text-danger mt-1">
                    <?= session()->getFlashdata('errors')['pr_mic_id'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="prWeight" class="form-label">重量</label>
            <input type="number" class="form-control" name="pr_weight" value="<?= old('pr_weight', $data['pr_weight'] ?? '') ?>" step="any">
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="prIsLength" name="pr_is_length" value="1" <?= (old('pr_is_length', $data['pr_is_length'] ?? 0) == 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="prIsLength">是否需要長度</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">保存</button>
        <a href="<?= url_to('ProductController::index') ?>" class="btn btn-secondary">取消</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const majorCategorySelect = document.getElementById('majorCategorySelect');
        const minorCategorySelect = document.getElementById('minorCategorySelect');

        // Function to load minor categories
        function loadMinorCategories(majorCategoryId, selectedMinorCategoryId = null) {
            minorCategorySelect.innerHTML = '<option value="">請選擇</option>'; // Clear current options
            if (majorCategoryId) {
                fetch(`<?= base_url('api/minorCategory/getMinorCategories') ?>/${majorCategoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.mic_id;
                            option.textContent = item.mic_name;
                            if (selectedMinorCategoryId && item.mic_id == selectedMinorCategoryId) {
                                option.selected = true;
                            }
                            minorCategorySelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading minor categories:', error));
            }
        }

        // Event listener for major category change
        majorCategorySelect.addEventListener('change', function() {
            loadMinorCategories(this.value);
        });

        // Initial load for edit mode
        <?php if ($isEdit): ?>
            majorCategorySelect.value = "<?= $mcId ?? '' ?>";
            loadMinorCategories(<?= $mcId ?? 'null' ?>, <?= $data['pr_mic_id'] ?? 'null' ?>);
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>