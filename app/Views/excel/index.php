<?= $this->extend('_layout') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-excel me-2"></i>匯入Excel</h2>
        </div>
        <!-- 檔案上傳區域 -->
        <div class="card mb-4">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 text-dark"><i class="bi bi-file-earmark-excel me-2 text-success"></i>選擇Excel檔案</h5>
            </div>
            <div class="card-body">
                <form id="excelForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="file" class="form-control" id="excelFile" name="excel_file"
                                accept=".xls,.xlsx" required>
                            <div class="form-text">支援格式：.xls, .xlsx</div>
                        </div>
                        <div class="col-md-4">
                            <button type="button" id="importBtn" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i>匯入
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- 匯入結果顯示區域 -->
        <div id="resultArea" class="d-none">
            <div class="row">
                <!-- 左側：統計資料區域 -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-eye me-2"></i>匯入資料預覽</h6>
                        </div>
                        <div class="card-body">
                            <!-- 統計卡片垂直排列 -->
                            <div class="mb-3">
                                <div class="card border-primary">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clipboard-check text-primary fs-3 me-3"></i>
                                            <div>
                                                <h5 class="card-title text-primary mb-0" id="rentalCount">0</h5>
                                                <p class="card-text mb-0">租賃單</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="card border-success">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cart text-success fs-3 me-3"></i>
                                            <div>
                                                <h5 class="card-title text-success mb-0" id="orderCount">0</h5>
                                                <p class="card-text mb-0">訂單</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="card border-info">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-database text-info fs-3 me-3"></i>
                                            <div>
                                                <h5 class="card-title text-info mb-0" id="totalCount">0</h5>
                                                <p class="card-text mb-0">總計筆數</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 右側：租賃單明細區域 -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>租賃單明細</h6>
                        </div>
                        <div class="card-body" id="rentalDetails">
                            <!-- 租賃單詳細內容將由JavaScript填入 -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" id="saveBtn" class="btn btn-success">
                    <i class="bi bi-save me-1"></i>儲存
                </button>
            </div>
        </div>
        <!-- 載入提示 -->
        <div id="loadingIndicator" class="text-center d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">處理中...</span>
            </div>
            <p class="mt-2">正在處理檔案，請稍候...</p>
        </div>
    </div>
</div>

<!-- 結果彈出視窗 -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="resultModalLabel"><i class="bi bi-check-circle me-2"></i>匯入結果</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="resultModalBody">
                <!-- 結果內容將由JavaScript填入 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const importBtn = document.getElementById('importBtn');
        const saveBtn = document.getElementById('saveBtn');
        const excelForm = document.getElementById('excelForm');
        const resultArea = document.getElementById('resultArea');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));

        let importedData = null;

        // 匯入按鈕點擊事件
        importBtn.addEventListener('click', function() {
            const fileInput = document.getElementById('excelFile');
            const file = fileInput.files[0];

            if (!file) {
                alert('請先選擇Excel檔案');
                return;
            }

            // 顯示載入提示
            loadingIndicator.classList.remove('d-none');
            resultArea.classList.add('d-none');
            importBtn.disabled = true;

            // 準備表單資料
            const formData = new FormData();
            formData.append('excel_file', file);

            // 發送AJAX請求
            fetch('<?= url_to('ExcelController::import') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loadingIndicator.classList.add('d-none');
                    importBtn.disabled = false;

                    if (data.success) {
                        importedData = data.data;
                        displayPreview(data.data);
                        resultArea.classList.remove('d-none');
                    } else {
                        alert('匯入失敗：' + data.message);
                    }
                })
                .catch(error => {
                    loadingIndicator.classList.add('d-none');
                    importBtn.disabled = false;
                    alert('處理失敗：' + error.message);
                });
        });

        // 儲存按鈕點擊事件
        saveBtn.addEventListener('click', function() {
            if (!importedData) {
                alert('沒有可儲存的資料');
                return;
            }

            if (!confirm('確定要儲存這些資料嗎？')) {
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>儲存中...';

            // 發送儲存請求
            fetch('<?= url_to('ExcelController::save') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(importedData)
                })
                .then(response => response.json())
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>儲存';

                    if (data.success) {
                        // 顯示成功結果彈出視窗
                        document.getElementById('resultModalBody').innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>${data.message}
                    </div>
                `;
                        resultModal.show();

                        // 清空表單和預覽
                        excelForm.reset();
                        resultArea.classList.add('d-none');
                        importedData = null;
                        
                        // 重置統計數字
                        document.getElementById('rentalCount').textContent = '0';
                        document.getElementById('orderCount').textContent = '0';
                        document.getElementById('totalCount').textContent = '0';
                        document.getElementById('rentalDetails').innerHTML = '';
                    } else {
                        alert('儲存失敗：' + data.message);
                    }
                })
                .catch(error => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>儲存';
                    alert('儲存失敗：' + error.message);
                });
        });

        // 顯示資料預覽
        function displayPreview(data) {
            // 更新統計卡片數字
            document.getElementById('rentalCount').textContent = data.rental_data ? data.rental_data.total_count : 0;
            document.getElementById('orderCount').textContent = data.order_data ? data.order_data.total_count : 0;
            document.getElementById('totalCount').textContent = data.total_records || 0;

            // 更新租賃單詳細資料
            const rentalDetails = document.getElementById('rentalDetails');
            if (data.rental_data && data.rental_data.locations) {
                let rentalHtml = '';
                for (const [location, count] of Object.entries(data.rental_data.locations)) {
                    rentalHtml += `<div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-medium">${location}</span>
                        <span class="badge bg-primary">${count} 筆</span>
                    </div>`;
                }
                rentalDetails.innerHTML = rentalHtml;
            } else {
                rentalDetails.innerHTML = '<p class="text-muted mb-0">無資料</p>';
            }
        }
    });
</script>

<?= $this->endSection() ?>