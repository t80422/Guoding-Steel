<?php

/**
 * 項目數量表模態框（參數化支援 訂單/租賃）
 *
 * 可用參數（向後相容）：
 * - orderId: 舊版參數（僅訂單），等同 documentId
 * - documentType: 'order' | 'rental'，預設 'order'
 * - documentId: 單據 ID（o_id/ro_id），預設為 $orderId
 * - detailUrl: 取得明細的 API URL
 * - assignmentGetUrl: 取得已有配置的 API URL
 * - assignmentSaveUrl: 儲存配置的 API URL
 * - detailIdKey: 明細主鍵欄位鍵名（訂單: od_id；租賃: rod_id）
 * - qtyField: 明細數量欄位鍵名（訂單: od_qty；租賃: rod_qty）
 * - lengthField: 明細長度欄位鍵名（訂單: od_length；租賃: rod_length）
 * - payloadFieldMap: ['detail' => ..., 'pi' => ..., 'qty' => ...] 儲存 payload 用的鍵名
 */
?>

<!-- 項目數量表模態框 -->
<div class="modal fade" id="itemQuantityModal" tabindex="-1" aria-labelledby="itemQuantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemQuantityModalLabel">
                    <i class="bi bi-table me-2"></i>項目數量表
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="itemQuantityTableContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">載入中...</span>
                        </div>
                        <div class="mt-2 text-muted">正在載入項目數量表...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>關閉
                </button>
                <button type="button" class="btn btn-primary" id="saveItemQuantityBtn">
                    <i class="bi bi-check-lg me-1"></i>確認更新
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #itemQuantityModal .modal-xl {
        max-width: 90%;
    }

    /* ========== 表格容器 ========== */
    .iqt-container {
        max-height: 70vh;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    /* ========== 表格本體 ========== */
    .iqt-table {
        margin: 0;
        font-size: 13px;
        white-space: nowrap;
        /* 關鍵：必須使用 separate 才能讓 sticky 生效 */
        border-collapse: separate;
        border-spacing: 0;
        width: max-content;
        min-width: 100%;
    }

    .iqt-table th,
    .iqt-table td {
        border: 1px solid #dee2e6;
        padding: 10px 8px;
        text-align: center;
        vertical-align: middle;
    }

    /* ========== 凍結欄位 - 共用 ========== */
    .iqt-table .sticky-col {
        position: sticky;
        background: #fff;
    }

    /* 第1欄：產品 (left: 0) */
    .iqt-table .sticky-col-1 {
        left: 0;
        z-index: 2;
        min-width: 180px;
        width: 180px;
        background: #f8f9fa;
    }

    /* 第2欄：數量 (left: 180px) */
    .iqt-table .sticky-col-2 {
        left: 180px;
        z-index: 2;
        min-width: 80px;
        width: 80px;
        background: #fafafa;
    }

    /* 第3欄：長度 (left: 260px) - 加陰影 */
    .iqt-table .sticky-col-3 {
        left: 260px;
        z-index: 2;
        min-width: 80px;
        width: 80px;
        background: #fafafa;
        box-shadow: 4px 0 8px rgba(0, 0, 0, 0.1);
    }

    /* ========== 表頭樣式 ========== */
    .iqt-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        font-weight: 600;
    }

    /* 表頭凍結欄位需要更高的 z-index */
    .iqt-table thead th.sticky-col {
        z-index: 4;
    }

    .iqt-table thead th.sticky-col-1 {
        background: linear-gradient(135deg, #EBF1EC 0%, #d4edda 100%);
    }

    .iqt-table thead th.sticky-col-2,
    .iqt-table thead th.sticky-col-3 {
        background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
    }

    .iqt-table thead th.category-col {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        min-width: 100px;
    }

    /* ========== 資料列樣式 ========== */
    .iqt-table tbody td.sticky-col-1 {
        font-weight: 600;
        color: #495057;
        text-align: left;
        padding-left: 12px;
        background: #f8f9fa;
    }

    .iqt-table tbody td.sticky-col-2,
    .iqt-table tbody td.sticky-col-3 {
        font-weight: 500;
        color: #495057;
        background: #fafafa;
    }

    .iqt-table tbody td.category-col {
        background: #fff;
        padding: 6px;
    }

    /* ========== 輸入框樣式 ========== */
    .iqt-table .form-control {
        font-size: 12px;
        padding: 6px 8px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        width: 80px;
        min-width: 80px;
    }

    .iqt-table .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.15rem rgba(87, 145, 87, 0.25);
        outline: none;
    }

    .iqt-table .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.25);
        background-color: #fff5f5;
    }

    /* ========== Hover 效果 ========== */
    .iqt-table tbody tr:hover td {
        background-color: #f0f7f0 !important;
    }

    .iqt-table tbody tr:hover td.sticky-col-1 {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%) !important;
    }

    .iqt-table tbody tr:hover td.sticky-col-2,
    .iqt-table tbody tr:hover td.sticky-col-3 {
        background: linear-gradient(135deg, #e8f5e9 0%, #dcedc8 100%) !important;
    }

    /* ========== 響應式調整 ========== */
    @media (max-width: 768px) {
        .iqt-table {
            font-size: 11px;
        }

        .iqt-table .sticky-col-1 {
            min-width: 120px;
            width: 120px;
        }

        .iqt-table .sticky-col-2 {
            left: 120px;
            min-width: 60px;
            width: 60px;
        }

        .iqt-table .sticky-col-3 {
            left: 180px;
            min-width: 60px;
            width: 60px;
        }

        .iqt-table th,
        .iqt-table td {
            padding: 6px 4px;
        }

        .iqt-table .form-control {
            font-size: 10px;
            padding: 4px 6px;
            width: 60px;
            min-width: 60px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 項目數量表功能
        let itemQuantityData = [];
        let originalQuantities = {};

        const FLOAT_TOLERANCE = 0.000001;

        function toNumber(value) {
            const parsed = typeof value === 'number' ? value : parseFloat(value);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function isEffectivelyZero(value) {
            return Math.abs(toNumber(value)) < FLOAT_TOLERANCE;
        }

        function isDifferentNumber(a, b) {
            return Math.abs(toNumber(a) - toNumber(b)) > FLOAT_TOLERANCE;
        }

        function formatDisplayNumber(value) {
            const numberValue = toNumber(value);
            if (Number.isInteger(numberValue)) {
                return numberValue.toString();
            }

            return numberValue
                .toFixed(4)
                .replace(/(?:\.0+|0+)$/, '')
                .replace(/\.$/, '');
        }

        // 當項目數量表模態框開啟時載入數據
        if (document.getElementById('itemQuantityModal')) {
            document.getElementById('itemQuantityModal').addEventListener('show.bs.modal', function() {
                loadItemQuantityTable();
            });
        }

        // 載入項目數量表數據
        function loadItemQuantityTable() {
            const container = document.getElementById('itemQuantityTableContainer');

            // 顯示載入狀態
            container.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                    <div class="mt-2 text-muted">正在載入項目數量表...</div>
                </div>
            `;

            // 重置數據
            itemQuantityData = {
                projectItems: [],
                orderDetails: [],
                existingQuantities: []
            };

            // 儲存原始數量，用於追蹤變化
            originalQuantities = {};

            // 1. 先取得施工項目
            fetch('<?= url_to('ProjectItemController::getItems') ?>')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('請求施工項目失敗');
                    }
                    return response.json();
                })
                .then(projectItemsData => {
                    itemQuantityData.projectItems = projectItemsData;

                    // 2. 取得單據明細（訂單/租賃）
                    return fetch('<?= $detailUrl ?>');
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('請求單據明細失敗');
                    }
                    return response.json();
                })
                .then(orderDetailsData => {
                    itemQuantityData.orderDetails = orderDetailsData;

                    // 3. 取得已有的項目數量資料
                    return fetch('<?= $assignmentGetUrl ?>');
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('請求項目數量失敗');
                    }
                    return response.json();
                })
                .then(quantityResult => {
                    itemQuantityData.existingQuantities = quantityResult.data || [];

                    // 建立原始數量索引 (detailId + "_" + pi_id)
                    const DETAIL_KEY = '<?= $payloadFieldMap['detail'] ?>';
                    const PI_KEY = '<?= $payloadFieldMap['pi'] ?>';
                    const QTY_KEY = '<?= $payloadFieldMap['qty'] ?>';
                    itemQuantityData.existingQuantities.forEach(item => {
                        const key = `${item[DETAIL_KEY]}_${item[PI_KEY]}`;
                        originalQuantities[key] = toNumber(item[QTY_KEY]);
                    });

                    // 4. 所有數據都載入完成，開始渲染
                    renderItemQuantityTable();
                })
                .catch(error => {
                    console.error('載入數據失敗:', error);

                    // 錯誤處理：顯示錯誤訊息
                    const container = document.getElementById('itemQuantityTableContainer');
                    container.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            載入項目數量表失敗：${error.message}
                        </div>
                    `;
                });
        }

        // 渲染項目數量表
        function renderItemQuantityTable() {
            const container = document.getElementById('itemQuantityTableContainer');
            if (!itemQuantityData || !itemQuantityData.projectItems || itemQuantityData.projectItems.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        目前沒有可用的項目數據。
                    </div>
                `;
                return;
            }

            const {
                projectItems,
                orderDetails
            } = itemQuantityData;

            // 建立單一表格 HTML（使用 CSS sticky 實現凍結）
            let html = `
                <div class="iqt-container">
                    <table class="iqt-table">
                        <thead>
                            <tr>
                                <th class="sticky-col sticky-col-1">產品</th>
                                <th class="sticky-col sticky-col-2">數量</th>
                                <th class="sticky-col sticky-col-3">長度</th>
            `;

            // 渲染施工項目表頭
            projectItems.forEach(projectItem => {
                html += `<th class="category-col">${projectItem.pi_name}</th>`;
            });

            html += `
                            </tr>
                        </thead>
                        <tbody>
            `;

            // 渲染資料列
            if (!orderDetails || orderDetails.length === 0) {
                const colSpan = 3 + projectItems.length;
                html += `
                    <tr>
                        <td colspan="${colSpan}" class="text-center text-muted py-4">
                            <i class="bi bi-info-circle me-2"></i>此單據暫無明細資料
                        </td>
                    </tr>
                `;
            } else {
                orderDetails.forEach(orderDetail => {
                    const detailId = orderDetail['<?= $detailIdKey ?>'];
                    const displayName = orderDetail.mic_name && orderDetail.mic_name !== orderDetail.pr_name ?
                        `${orderDetail.mic_name} ${orderDetail.pr_name}` :
                        (orderDetail.od_pr_name || orderDetail.pr_name);
                    const totalQty = toNumber(orderDetail['<?= $qtyField ?>']);
                    const totalLength = toNumber(orderDetail['<?= $lengthField ?>']);

                    html += `
                        <tr>
                            <td class="sticky-col sticky-col-1">${displayName}</td>
                            <td class="sticky-col sticky-col-2">${formatDisplayNumber(totalQty)}</td>
                            <td class="sticky-col sticky-col-3">${formatDisplayNumber(totalLength)}</td>
                    `;

                    // 渲染每個施工項目的數量輸入欄位
                    projectItems.forEach(projectItem => {
                        const key = `${detailId}_${projectItem.pi_id}`;
                        const existingQty = toNumber(originalQuantities[key]);
                        const maxQty = totalQty;

                        html += `
                            <td class="category-col">
                                <input type="number" 
                                       class="form-control category-input" 
                                       data-detail-id="${detailId}" 
                                       data-pi-id="${projectItem.pi_id}"
                                       data-max-qty="${formatDisplayNumber(maxQty)}"
                                       value="${formatDisplayNumber(existingQty)}" 
                                       max="${formatDisplayNumber(maxQty)}"
                                       step="any">
                            </td>
                        `;
                    });

                    html += `</tr>`;
                });
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;

            // 綁定數量驗證事件
            bindQuantityValidation();
        }

        // 數量驗證函數
        function bindQuantityValidation() {
            const categoryInputs = document.querySelectorAll('#itemQuantityModal .category-input');

            categoryInputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateRowQuantity(this);
                });
            });
        }

        // 驗證單行數量總和
        function validateRowQuantity(changedInput) {
            const detailId = changedInput.dataset.detailId;
            const maxQty = toNumber(changedInput.dataset.maxQty);

            // 找到同一行的所有input
            const rowInputs = document.querySelectorAll(`#itemQuantityModal .category-input[data-detail-id="${detailId}"]`);
            let totalAssigned = 0;

            rowInputs.forEach(input => {
                const value = toNumber(input.value);
                totalAssigned += value;

                // 移除之前的錯誤樣式
                input.classList.remove('is-invalid');
                input.title = '';
            });

            // 檢查是否超過限制
            if (totalAssigned - maxQty > FLOAT_TOLERANCE) {
                // 標記所有相關input為錯誤
                rowInputs.forEach(input => {
                    input.classList.add('is-invalid');
                    input.title = `總分配數量 ${formatDisplayNumber(totalAssigned)} 超過訂單數量 ${formatDisplayNumber(maxQty)}`;
                });

                // 顯示錯誤訊息
                showValidationError(`產品分配數量總和 (${formatDisplayNumber(totalAssigned)}) 不能超過訂單數量 (${formatDisplayNumber(maxQty)})`);
                return false;
            } else {
                // 清除錯誤訊息
                hideValidationError();
                return true;
            }
        }

        // 顯示驗證錯誤
        function showValidationError(message) {
            let errorDiv = document.getElementById('quantityValidationError');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'quantityValidationError';
                errorDiv.className = 'alert alert-warning alert-dismissible fade show mt-2';

                const modalBody = document.querySelector('#itemQuantityModal .modal-body');
                modalBody.insertBefore(errorDiv, modalBody.firstChild);
            }

            errorDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
        }

        // 隱藏驗證錯誤
        function hideValidationError() {
            const errorDiv = document.getElementById('quantityValidationError');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        // 處理確認更新按鈕
        if (document.getElementById('saveItemQuantityBtn')) {
            document.getElementById('saveItemQuantityBtn').addEventListener('click', function() {
                // 先進行最終驗證
                const categoryInputs = document.querySelectorAll('#itemQuantityModal .category-input');
                let hasValidationError = false;

                // 檢查每一行是否有驗證錯誤
                const processedRows = new Set();
                categoryInputs.forEach(input => {
                    const detailId = input.dataset.detailId;
                    if (!processedRows.has(detailId)) {
                        processedRows.add(detailId);
                        if (!validateRowQuantity(input)) {
                            hasValidationError = true;
                        }
                    }
                });

                if (hasValidationError) {
                    showValidationError('請修正數量超過限制的項目後再提交');
                    return;
                }

                // 比較原始值和當前值，分類操作
                const operations = {
                    create: [],
                    update: [],
                    delete: []
                };

                categoryInputs.forEach(input => {
                    const detailId = input.dataset.detailId;
                    const piId = input.dataset.piId;
                    const key = `${detailId}_${piId}`;
                    const originalQty = toNumber(originalQuantities[key]);
                    const newQty = toNumber(input.value);

                    if (isEffectivelyZero(originalQty) && !isEffectivelyZero(newQty)) {
                        // 新增：原本沒有，現在有值
                        operations.create.push({
                            '<?= $payloadFieldMap['detail'] ?>': detailId,
                            '<?= $payloadFieldMap['pi'] ?>': piId,
                            '<?= $payloadFieldMap['qty'] ?>': newQty
                        });
                    } else if (!isEffectivelyZero(originalQty) && !isEffectivelyZero(newQty) && isDifferentNumber(originalQty, newQty)) {
                        // 更新：原本有值，現在也有值，但數量不同
                        operations.update.push({
                            '<?= $payloadFieldMap['detail'] ?>': detailId,
                            '<?= $payloadFieldMap['pi'] ?>': piId,
                            '<?= $payloadFieldMap['qty'] ?>': newQty
                        });
                    } else if (!isEffectivelyZero(originalQty) && isEffectivelyZero(newQty)) {
                        // 刪除：原本有值，現在變成0
                        operations.delete.push({
                            '<?= $payloadFieldMap['detail'] ?>': detailId,
                            '<?= $payloadFieldMap['pi'] ?>': piId
                        });
                    }
                });

                // 檢查是否有任何變更
                const totalChanges = operations.create.length + operations.update.length + operations.delete.length;
                if (totalChanges === 0) {
                    alert('沒有數據變更，無需更新。');
                    bootstrap.Modal.getInstance(document.getElementById('itemQuantityModal')).hide();
                    return;
                }

                // 設置按鈕載入狀態
                const saveButton = document.getElementById('saveItemQuantityBtn');
                const originalText = saveButton.innerHTML;
                saveButton.disabled = true;
                saveButton.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>儲存中...';

                // 發送數據到後端
                fetch('<?= $assignmentSaveUrl ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(operations)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('儲存失敗');
                        }
                        return response.json();
                    })
                    .then(result => {
                        // 恢復按鈕狀態
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalText;

                        // 更新原始數量記錄
                        categoryInputs.forEach(input => {
                            const detailId = input.dataset.detailId;
                            const piId = input.dataset.piId;
                            const key = `${detailId}_${piId}`;
                            const newQty = toNumber(input.value);

                            if (!isEffectivelyZero(newQty)) {
                                originalQuantities[key] = newQty;
                            } else {
                                delete originalQuantities[key];
                            }
                        });

                        bootstrap.Modal.getInstance(document.getElementById('itemQuantityModal')).hide();
                    })
                    .catch(error => {
                        console.error('儲存項目數量表失敗:', error);

                        // 恢復按鈕狀態
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalText;

                        // 顯示錯誤訊息
                        alert('儲存項目數量表失敗：' + error.message);
                    });
            });
        }
    });
</script>