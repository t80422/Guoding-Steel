<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="container py-4">
    <!-- 標題列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1 fw-bold">設定地點權限</h3>
            <p class="text-muted mb-0">使用者：<?= esc($user['u_name']) ?></p>
        </div>
    </div>
    <!-- 錯誤訊息 -->
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- 地點設定表單 -->
    <form method="post" action="<?= url_to('UserController::saveLocationSettings') ?>">
        <input type="hidden" name="u_id" value="<?= $user['u_id'] ?>">
        
        <div class="row">
            <!-- 倉庫 -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary bg-opacity-10 border-primary">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="bi bi-building me-2"></i>倉庫
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($groupedLocations['倉庫'])): ?>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>目前無倉庫資料
                            </p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($groupedLocations['倉庫'] as $location): ?>
                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="location_ids[]" 
                                                   value="<?= $location['l_id'] ?>"
                                                   id="location_<?= $location['l_id'] ?>"
                                                   <?= in_array($location['l_id'], $userLocationIds) ? 'checked' : '' ?>>
                                            <label class="form-check-label w-100" for="location_<?= $location['l_id'] ?>">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-medium"><?= esc($location['l_name']) ?></span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 工地 -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-success">
                    <div class="card-header bg-success bg-opacity-10 border-success">
                        <h5 class="card-title mb-0 text-success">
                            <i class="bi bi-tools me-2"></i>工地
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($groupedLocations['工地'])): ?>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>目前無工地資料
                            </p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($groupedLocations['工地'] as $location): ?>
                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="location_ids[]" 
                                                   value="<?= $location['l_id'] ?>"
                                                   id="location_<?= $location['l_id'] ?>"
                                                   <?= in_array($location['l_id'], $userLocationIds) ? 'checked' : '' ?>>
                                            <label class="form-check-label w-100" for="location_<?= $location['l_id'] ?>">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-medium"><?= esc($location['l_name']) ?></span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 操作按鈕 -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                <button type="button" class="btn btn-outline-secondary" onclick="clearAll()">
                    <i class="bi bi-x-square me-1"></i>清除
                </button>
            </div>
            <div>
                <a href="<?= url_to('UserController::index') ?>" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-x-lg me-1"></i>取消
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>儲存設定
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // 清除功能
    function clearAll() {
        document.querySelectorAll('input[name="location_ids[]"]').forEach(function(checkbox) {
            checkbox.checked = false;
        });
    }

    // 表單提交確認
    document.querySelector('form').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('input[name="location_ids[]"]:checked');
        if (checkedBoxes.length === 0) {
            if (!confirm('您尚未選擇任何地點，這將清除該使用者的所有地點權限。確定要繼續嗎？')) {
                e.preventDefault();
                return false;
            }
        }
    });
</script>

<?= $this->endSection() ?> 