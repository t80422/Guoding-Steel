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

    #itemQuantityModal .table {
        margin-bottom: 0;
    }

    #itemQuantityModal .table th {
        background: linear-gradient(135deg, #EBF1EC 0%, #d4edda 100%) !important;
        border: none;
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        padding: 12px 8px;
        text-align: center;
        vertical-align: middle;
    }

    #itemQuantityModal .table td {
        padding: 8px;
        vertical-align: middle;
        border-color: #f1f3f4;
        text-align: center;
    }

    #itemQuantityModal .quantity-input-field {
        border: 1.5px solid #dee2e6;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 14px;
        text-align: center;
        transition: all 0.3s ease;
        width: 100px;
    }

    #itemQuantityModal .quantity-input-field:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(87, 145, 87, 0.25);
    }

    #itemQuantityModal .item-name {
        text-align: left;
        font-weight: 500;
        color: #495057;
    }

    #itemQuantityModal .table-responsive {
        max-height: 70vh;
        overflow: auto;
    }

    /* 矩陣表格樣式 */
    .quantity-matrix-table {
        font-size: 13px;
        white-space: nowrap;
    }

    .quantity-matrix-table .product-header {
        background: linear-gradient(135deg, #EBF1EC 0%, #d4edda 100%) !important;
        font-weight: 700;
        text-align: center;
        vertical-align: middle;
        width: 100px;
        min-width: 80px;
    }

    .quantity-matrix-table .category-header {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        font-size: 12px;
        padding: 8px 4px;
        border: 1px solid #dee2e6;
    }

    .quantity-matrix-table .basic-header {
        background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%) !important;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        font-size: 12px;
        padding: 8px 6px;
        width: 80px;
        border: 1px solid #dee2e6;
    }

    .quantity-matrix-table .product-name {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        min-width: 80px;
    }

    .quantity-matrix-table .total-quantity,
    .quantity-matrix-table .total-length {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        width: 80px;
        padding: 8px 4px;
    }

    .quantity-matrix-table .category-cell {
        padding: 4px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        width: 80px;
    }

    .quantity-matrix-table .form-control {
        font-size: 12px;
        padding: 4px 6px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        width: 100%;
        min-width: 60px;
    }

    .quantity-matrix-table .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.1rem rgba(87, 145, 87, 0.25);
    }

    .quantity-matrix-table .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.25);
        background-color: #fff5f5;
    }

    .quantity-matrix-table .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.25);
    }

    /* 響應式調整 */
    @media (max-width: 768px) {
        .quantity-matrix-table {
            font-size: 11px;
        }

        .quantity-matrix-table .form-control {
            font-size: 10px;
            padding: 2px 4px;
        }

        .quantity-matrix-table .category-header,
        .quantity-matrix-table .basic-header {
            font-size: 10px;
            padding: 4px 2px;
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

            const { projectItems, orderDetails } = itemQuantityData;

            let tableHtml = `
                <div class="table-responsive">
                    <table class="table table-bordered table-hover quantity-matrix-table">
                        <thead>
                            <tr>
                                <th class="product-header">產品</th>
                                <th class="basic-header">數量</th>
                                <th class="basic-header">長度</th>
            `;

            // 渲染分類表頭（每個分類只有一個欄位）
            projectItems.forEach(projectItem => {
                tableHtml += `<th class="category-header">${projectItem.pi_name}</th>`;
            });

            tableHtml += `
                            </tr>
                        </thead>
                        <tbody>
            `;

            // 處理產品數據 - 如果沒有明細，顯示提示訊息
            if (!orderDetails || orderDetails.length === 0) {
                tableHtml += `
                    <tr>
                        <td colspan="${3 + projectItems.length}" class="text-center text-muted py-4">
                            <i class="bi bi-info-circle me-2"></i>
                            此單據暫無明細資料
                        </td>
                    </tr>`;
            } else {
                // 渲染產品行
                orderDetails.forEach(orderDetail => {
                    // 組合顯示名稱：小分類名稱 + 產品名稱
                    const displayName = orderDetail.mic_name && orderDetail.mic_name !== orderDetail.pr_name 
                        ? `${orderDetail.mic_name} ${orderDetail.pr_name}` 
                        : (orderDetail.od_pr_name || orderDetail.pr_name);

                    const detailId = orderDetail['<?= $detailIdKey ?>'];
                    const totalQty = toNumber(orderDetail['<?= $qtyField ?>']);
                    const totalLength = toNumber(orderDetail['<?= $lengthField ?>']);
                    
                    tableHtml += `
                        <tr>
                            <td class="product-name">${displayName}</td>
                            <td class="total-quantity">${formatDisplayNumber(totalQty)}</td>
                            <td class="total-length">${formatDisplayNumber(totalLength)}</td>
                    `;

                    // 渲染每個分類的數量欄位
                    projectItems.forEach(projectItem => {
                        const key = `${detailId}_${projectItem.pi_id}`;
                        const existingQty = toNumber(originalQuantities[key]);
                        const maxQty = totalQty;
                        
                        tableHtml += `
                            <td class="category-cell">
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

                    tableHtml += `</tr>`;
                });
            }

            tableHtml += `
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = tableHtml;
            
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