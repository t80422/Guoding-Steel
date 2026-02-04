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
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="itemQuantityTabs" role="tablist">
                    <li class="nav-item" role="presentation" id="source-tab-li">
                        <button class="nav-link active" id="source-tab" data-bs-toggle="tab" data-bs-target="#source-panel" type="button" role="tab" aria-selected="true">
                            <i class="bi bi-box-arrow-up me-1"></i>出發地配置
                        </button>
                    </li>
                    <li class="nav-item" role="presentation" id="target-tab-li">
                        <button class="nav-link" id="target-tab" data-bs-toggle="tab" data-bs-target="#target-panel" type="button" role="tab" aria-selected="false">
                            <i class="bi bi-box-arrow-in-down me-1"></i>目的地配置
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="itemQuantityTabContent">
                    <!-- Source Panel -->
                    <div class="tab-pane fade show active" id="source-panel" role="tabpanel">
                         <div id="itemQuantityTableContainerSource">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">載入中...</span>
                                </div>
                                <div class="mt-2 text-muted">正在載入出發地配置...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Target Panel -->
                    <div class="tab-pane fade" id="target-panel" role="tabpanel">
                         <div id="itemQuantityTableContainerTarget">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">載入中...</span>
                                </div>
                                <div class="mt-2 text-muted">正在載入目的地配置...</div>
                            </div>
                        </div>
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
        max-height: 60vh; /* 稍微縮小以適應 tabs */
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

    /* 第2欄：廠商 (left: 180px) */
    .iqt-table .sticky-col-2 {
        left: 180px;
        z-index: 2;
        min-width: 100px;
        width: 100px;
        background: #fafafa;
    }

    /* 第3欄：數量 (left: 280px) */
    .iqt-table .sticky-col-3 {
        left: 280px;
        z-index: 2;
        min-width: 80px;
        width: 80px;
        background: #fafafa;
    }

    /* 第4欄：長度 (left: 360px) - 加陰影 */
    .iqt-table .sticky-col-4 {
        left: 360px;
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
    .iqt-table thead th.sticky-col-3,
    .iqt-table thead th.sticky-col-4 {
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
    .iqt-table tbody td.sticky-col-3,
    .iqt-table tbody td.sticky-col-4 {
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
    .iqt-table tbody tr:hover td.sticky-col-3,
    .iqt-table tbody tr:hover td.sticky-col-4 {
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

        .iqt-table .sticky-col-4 {
            left: 240px;
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
        const TYPE_SOURCE = 0;
        const TYPE_TARGET = 1;

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

        // 初始化 Tab 顯示狀態
        function initTabs() {
            const sourceTabBtn = document.getElementById('source-tab');
            sourceTabBtn.click();
        }

        // 當項目數量表模態框開啟時載入數據
        if (document.getElementById('itemQuantityModal')) {
            initTabs();
            document.getElementById('itemQuantityModal').addEventListener('show.bs.modal', function() {
                loadItemQuantityTable();
            });
        }

        // 載入項目數量表數據
        function loadItemQuantityTable() {
            // 顯示載入狀態
            const setRunning = (id) => {
                 const el = document.getElementById(id);
                 if(el) el.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">載入中...</span>
                        </div>
                        <div class="mt-2 text-muted">正在載入...</div>
                    </div>`;
            };
            setRunning('itemQuantityTableContainerSource');
            setRunning('itemQuantityTableContainerTarget');

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
                    if (!response.ok) throw new Error('請求施工項目失敗');
                    return response.json();
                })
                .then(projectItemsData => {
                    itemQuantityData.projectItems = projectItemsData;
                    // 2. 取得單據明細
                    return fetch('<?= $detailUrl ?>');
                })
                .then(response => {
                    if (!response.ok) throw new Error('請求單據明細失敗');
                    return response.json();
                })
                .then(orderDetailsData => {
                    itemQuantityData.orderDetails = orderDetailsData;
                    // 3. 取得已有的項目數量資料
                    return fetch('<?= $assignmentGetUrl ?>');
                })
                .then(response => {
                    if (!response.ok) throw new Error('請求項目數量失敗');
                    return response.json();
                })
                .then(quantityResult => {
                    itemQuantityData.existingQuantities = quantityResult.data || [];

                    // 建立原始數量索引 (detailId + "_" + pi_id + "_" + type)
                    const DETAIL_KEY = '<?= $payloadFieldMap['detail'] ?>';
                    const PI_KEY = '<?= $payloadFieldMap['pi'] ?>';
                    const QTY_KEY = '<?= $payloadFieldMap['qty'] ?>';
                    
                    itemQuantityData.existingQuantities.forEach(item => {
                        // 後端回傳資料需包含 odpi_type，若無則預設為 Target(1)
                    const type = item.odpi_type !== undefined ? item.odpi_type : (item.rodpi_type !== undefined ? item.rodpi_type : 1); 
                    const key = `${item[DETAIL_KEY]}_${item[PI_KEY]}_${type}`;
                        originalQuantities[key] = toNumber(item[QTY_KEY]);
                    });

                    // 4. 渲染表格
                    renderTableByType(TYPE_SOURCE);
                    renderTableByType(TYPE_TARGET);
                })
                .catch(error => {
                    console.error('載入數據失敗:', error);
                    const showError = (id) => {
                         const el = document.getElementById(id);
                         if(el) el.innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                載入失敗：${error.message}
                            </div>`;
                    };
                    showError('itemQuantityTableContainerSource');
                    showError('itemQuantityTableContainerTarget');
                });
        }

        // 根據類型渲染表格
        function renderTableByType(type) {
            const containerId = type === TYPE_SOURCE ? 'itemQuantityTableContainerSource' : 'itemQuantityTableContainerTarget';
            const container = document.getElementById(containerId);
            if (!container) return;

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

            let html = `
                <div class="iqt-container">
                    <table class="iqt-table">
                        <thead>
                            <tr>
                                <th class="sticky-col sticky-col-1">產品</th>
                                <th class="sticky-col sticky-col-2">廠商</th>
                                <th class="sticky-col sticky-col-3">數量</th>
                                <th class="sticky-col sticky-col-4">長度</th>
            `;

            projectItems.forEach(projectItem => {
                html += `<th class="category-col">${projectItem.pi_name}</th>`;
            });

            html += `
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (!orderDetails || orderDetails.length === 0) {
                const colSpan = 4 + projectItems.length;
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
                    const manufacturerName = orderDetail.ma_name || '';
                    const totalQty = toNumber(orderDetail['<?= $qtyField ?>']);
                    const totalLength = toNumber(orderDetail['<?= $lengthField ?>']);

                    html += `
                        <tr>
                            <td class="sticky-col sticky-col-1">${displayName}</td>
                            <td class="sticky-col sticky-col-2">${manufacturerName}</td>
                            <td class="sticky-col sticky-col-3">${formatDisplayNumber(totalQty)}</td>
                            <td class="sticky-col sticky-col-4">${formatDisplayNumber(totalLength)}</td>
                    `;

                    projectItems.forEach(projectItem => {
                        const key = `${detailId}_${projectItem.pi_id}_${type}`;
                        const existingQty = toNumber(originalQuantities[key]);
                        const maxQty = totalQty;

                        html += `
                            <td class="category-col">
                                <input type="number" 
                                       class="form-control category-input" 
                                       data-detail-id="${detailId}" 
                                       data-pi-id="${projectItem.pi_id}"
                                       data-type="${type}"
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
            bindQuantityValidation(container);
        }

        // 綁定驗證事件 (Scoped by container)
        function bindQuantityValidation(container) {
            const categoryInputs = container.querySelectorAll('.category-input');
            categoryInputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateRowQuantity(this);
                });
            });
        }

        // 驗證單行數量 (區分 type)
        function validateRowQuantity(changedInput) {
            const detailId = changedInput.dataset.detailId;
            const type = changedInput.dataset.type;
            const maxQty = toNumber(changedInput.dataset.maxQty);

            // 找到該 type 下同一行的所有 input
            const containerId = type == TYPE_SOURCE ? 'itemQuantityTableContainerSource' : 'itemQuantityTableContainerTarget';
            const container = document.getElementById(containerId);
            const rowInputs = container.querySelectorAll(`.category-input[data-detail-id="${detailId}"][data-type="${type}"]`);
            
            let totalAssigned = 0;

            rowInputs.forEach(input => {
                totalAssigned += toNumber(input.value);
                input.classList.remove('is-invalid');
                input.title = '';
            });

            if (totalAssigned - maxQty > FLOAT_TOLERANCE) {
                rowInputs.forEach(input => {
                    input.classList.add('is-invalid');
                    input.title = `總分配數量 ${formatDisplayNumber(totalAssigned)} 超過訂單數量 ${formatDisplayNumber(maxQty)}`;
                });
                showValidationError(`[${type == TYPE_SOURCE ? '出發地' : '目的地'}] 產品分配數量總和 (${formatDisplayNumber(totalAssigned)}) 不能超過訂單數量 (${formatDisplayNumber(maxQty)})`);
                return false;
            } else {
                hideValidationError();
                return true;
            }
        }

        function showValidationError(message) {
            let errorDiv = document.getElementById('quantityValidationError');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'quantityValidationError';
                errorDiv.className = 'alert alert-warning alert-dismissible fade show mt-2';
                // 顯示在目前活動的 tab pane 上方
                const modalBody = document.querySelector('#itemQuantityModal .modal-body');
                modalBody.insertBefore(errorDiv, modalBody.firstChild);
            }
            errorDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
        }

        function hideValidationError() {
            const errorDiv = document.getElementById('quantityValidationError');
            if (errorDiv) errorDiv.remove();
        }

        // 儲存邏輯
        if (document.getElementById('saveItemQuantityBtn')) {
            document.getElementById('saveItemQuantityBtn').addEventListener('click', function() {
                // 1. 全域驗證
                const allInputs = document.querySelectorAll('#itemQuantityModal .category-input');
                let hasError = false;
                
                // 為了避免重複計算，我們用 Set 來記錄已檢查過的 (detailId + type) 組合
                const checkedRows = new Set();

                allInputs.forEach(input => {
                    const detailId = input.dataset.detailId;
                    const type = input.dataset.type;
                    const key = `${detailId}_${type}`;
                    
                    if (!checkedRows.has(key)) {
                        checkedRows.add(key);
                        if (!validateRowQuantity(input)) {
                            hasError = true;
                        }
                    }
                });

                if (hasError) {
                    showValidationError('請修正數量超過限制的項目後再提交');
                    return;
                }

                // 2. 收集變更
                const operations = { create: [], update: [], delete: [] };

                const TYPE_KEY = '<?= $payloadFieldMap['type'] ?? 'odpi_type' ?>';

                allInputs.forEach(input => {
                    const detailId = input.dataset.detailId;
                    const piId = input.dataset.piId;
                    const type = input.dataset.type; // 0 or 1
                    
                    const key = `${detailId}_${piId}_${type}`;
                    const originalQty = toNumber(originalQuantities[key]);
                    const newQty = toNumber(input.value);

                    const payload = {
                        '<?= $payloadFieldMap['detail'] ?>': detailId,
                        '<?= $payloadFieldMap['pi'] ?>': piId,
                        '<?= $payloadFieldMap['qty'] ?>': newQty,
                    };
                    payload[TYPE_KEY] = type;

                    if (isEffectivelyZero(originalQty) && !isEffectivelyZero(newQty)) {
                        operations.create.push(payload);
                    } else if (!isEffectivelyZero(originalQty) && !isEffectivelyZero(newQty) && isDifferentNumber(originalQty, newQty)) {
                        operations.update.push(payload);
                    } else if (!isEffectivelyZero(originalQty) && isEffectivelyZero(newQty)) {
                        // 刪除時也需要 type 才能精確刪除
                        const deletePayload = {
                            '<?= $payloadFieldMap['detail'] ?>': detailId,
                            '<?= $payloadFieldMap['pi'] ?>': piId,
                        };
                        deletePayload[TYPE_KEY] = type;
                        operations.delete.push(deletePayload);
                    }
                });

                const totalChanges = operations.create.length + operations.update.length + operations.delete.length;
                if (totalChanges === 0) {
                    alert('沒有數據變更，無需更新。');
                    bootstrap.Modal.getInstance(document.getElementById('itemQuantityModal')).hide();
                    return;
                }

                // 3. 送出
                const saveButton = document.getElementById('saveItemQuantityBtn');
                const originalText = saveButton.innerHTML;
                saveButton.disabled = true;
                saveButton.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>儲存中...';

                fetch('<?= $assignmentSaveUrl ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(operations)
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('儲存失敗');
                        return response.json();
                    })
                    .then(result => {
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalText;

                        // 更新原始數量
                        allInputs.forEach(input => {
                            const detailId = input.dataset.detailId;
                            const piId = input.dataset.piId;
                            const type = input.dataset.type;
                            const key = `${detailId}_${piId}_${type}`;
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
                        console.error('儲存失敗:', error);
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalText;
                        alert('儲存失敗：' + error.message);
                    });
            });
        }
    });
</script>