<?php

/**
 * 產品選擇器組件
 * @param string $modalId 模態框ID (預設: productModal)
 * @param string $fieldPrefix 字段前綴 (如: od, rod)
 */

$modalId = $modalId ?? 'productModal';
$fieldPrefix = $fieldPrefix ?? 'od';
?>

<!-- 產品選擇 Modal -->
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="<?= $modalId ?>Label">選擇產品</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- 步驟指示器 -->
                <div class="d-flex justify-content-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="step-indicator active" id="<?= $modalId ?>Step1Indicator">
                            <span class="step-number">1</span>
                            <span class="step-text">大分類</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" id="<?= $modalId ?>Step2Indicator">
                            <span class="step-number">2</span>
                            <span class="step-text">小分類</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" id="<?= $modalId ?>Step3Indicator">
                            <span class="step-number">3</span>
                            <span class="step-text">產品</span>
                        </div>
                    </div>
                </div>

                <!-- 步驟內容 -->
                <div class="step-content">
                    <!-- 步驟 1: 選擇大分類 -->
                    <div class="step-pane active" id="<?= $modalId ?>Step1">
                        <h6 class="mb-3 text-center text-muted">請選擇大分類</h6>
                        <div class="row g-2" id="<?= $modalId ?>MajorCategoryList">
                            <div class="col-12 text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">載入中...</span>
                                </div>
                                <div class="mt-2 text-muted">載入大分類中...</div>
                            </div>
                        </div>
                    </div>

                    <!-- 步驟 2: 選擇小分類 -->
                    <div class="step-pane" id="<?= $modalId ?>Step2">
                        <div class="d-flex align-items-center mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-3" id="<?= $modalId ?>BackToStep1">
                                <i class="bi bi-arrow-left"></i> 返回
                            </button>
                            <h6 class="mb-0 text-muted">請選擇小分類</h6>
                        </div>
                        <div class="row g-2" id="<?= $modalId ?>MinorCategoryList">
                            <!-- 動態載入小分類 -->
                        </div>
                    </div>

                    <!-- 步驟 3: 選擇產品 -->
                    <div class="step-pane" id="<?= $modalId ?>Step3">
                        <div class="d-flex align-items-center mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-3" id="<?= $modalId ?>BackToStep2">
                                <i class="bi bi-arrow-left"></i> 返回
                            </button>
                            <h6 class="mb-0 text-muted">請選擇產品</h6>
                        </div>
                        <div class="list-group" id="<?= $modalId ?>ProductList">
                            <!-- 動態載入產品 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* 產品選擇器組件樣式 */
    .product-selector {
        transition: all 0.3s ease;
        border-radius: 8px;
        border: 1.5px solid #dee2e6;
    }

    .product-selector:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(87, 145, 87, 0.15);
        transform: translateY(-1px);
    }

    .border-dashed {
        border-style: dashed !important;
        border-color: #adb5bd !important;
    }

    .product-selector.border-dashed:hover {
        border-style: solid !important;
        border-color: var(--bs-primary) !important;
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
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        border: 2px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .step-indicator.active .step-number {
        background: linear-gradient(135deg, var(--bs-primary) 0%, #4a7c4a 100%);
        color: white;
        border-color: var(--bs-primary);
        transform: scale(1.1);
    }

    .step-indicator.completed .step-number {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-color: #28a745;
    }

    .step-text {
        margin-top: 8px;
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
        text-align: center;
    }

    .step-indicator.active .step-text {
        color: var(--bs-primary);
        font-weight: 600;
    }

    .step-connector {
        flex-grow: 1;
        height: 2px;
        background: linear-gradient(90deg, #dee2e6 0%, #f8f9fa 100%);
        margin: 0 8px;
        position: relative;
        top: -1.25rem;
    }

    /* 步驟內容動畫 */
    .step-pane {
        display: none;
    }

    .step-pane.active {
        display: block;
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* 分類卡片樣式 */
    .category-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        color: #212529 !important;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(87, 145, 87, 0.1), transparent);
        transition: left 0.5s;
    }

    .category-card:hover::before {
        left: 100%;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(87, 145, 87, 0.2);
        border-color: var(--bs-primary);
    }

    .category-card h6 {
        margin: 0;
        color: #495057 !important;
        font-weight: 600;
        font-size: 14px;
        z-index: 1;
        position: relative;
    }

    .category-card:hover h6 {
        color: var(--bs-primary) !important;
    }

    /* 產品列表樣式 */
    .product-item {
        transition: all 0.3s ease;
        border-radius: 8px;
        margin-bottom: 4px;
        color: #212529 !important;
        background-color: #ffffff !important;
    }

    .product-item:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        transform: translateX(8px);
        border-color: var(--bs-primary) !important;
        color: #495057 !important;
    }

    .product-item h6 {
        font-size: 14px;
        font-weight: 500;
        margin: 0;
        color: inherit !important;
    }
</style>

<script>
    /**
     * 產品選擇器JavaScript類
     */
    class ProductSelector {
        constructor(config) {
            this.modalId = config.modalId || 'productModal';
            this.fieldPrefix = config.fieldPrefix || 'od';

            // 依據 fieldPrefix 自動推導欄位名稱
            this.productIdField = this.fieldPrefix + '_pr_id';
            this.qtyField = this.fieldPrefix + '_qty';
            this.lengthField = this.fieldPrefix + '_length';
            this.weightField = this.fieldPrefix + '_weight';

            this.currentTargetIndex = 0;
            this.selectedMajorCategory = null;
            this.selectedMinorCategory = null;
            this.selectedMinorCategoryName = null; // 暫存小分類名稱

            this.init();
        }

        init() {
            // 綁定產品選擇器點擊事件
            document.addEventListener('click', (e) => {
                if (e.target.closest('.product-selector[data-bs-target="#' + this.modalId + '"]')) {
                    const selector = e.target.closest('.product-selector');
                    this.currentTargetIndex = selector.dataset.targetIndex;
                    this.resetModal();
                    this.loadMajorCategories();
                }
            });

            // 綁定返回按鈕事件
            document.getElementById(this.modalId + 'BackToStep1')?.addEventListener('click', () => {
                this.goToStep(1);
            });

            document.getElementById(this.modalId + 'BackToStep2')?.addEventListener('click', () => {
                this.goToStep(2);
            });
        }

        // 重置Modal
        resetModal() {
            this.selectedMajorCategory = null;
            this.selectedMinorCategory = null;
            this.selectedMinorCategoryName = null;

            document.querySelectorAll(`#${this.modalId} .step-indicator`).forEach(indicator => {
                indicator.classList.remove('active', 'completed');
            });
            document.getElementById(this.modalId + 'Step1Indicator').classList.add('active');

            document.querySelectorAll(`#${this.modalId} .step-pane`).forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById(this.modalId + 'Step1').classList.add('active');
        }

        // 載入大分類
        loadMajorCategories() {
            const majorCategoryList = document.getElementById(this.modalId + 'MajorCategoryList');
            majorCategoryList.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">載入中...</span>
                </div>
                <p class="mt-2 text-muted">載入大分類中...</p>
            </div>`;

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

                    document.querySelectorAll(`#${this.modalId}MajorCategoryList .category-card[data-major-id]`).forEach(card => {
                        card.addEventListener('click', () => {
                            this.selectedMajorCategory = card.dataset.majorId;
                            this.goToStep(2);
                            this.loadMinorCategories(this.selectedMajorCategory);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading major categories:', error);
                    majorCategoryList.innerHTML = '<div class="col-12 text-center py-4 text-danger">載入失敗</div>';
                });
        }

        // 載入小分類
        loadMinorCategories(majorCategoryId) {
            const minorCategoryList = document.getElementById(this.modalId + 'MinorCategoryList');
            minorCategoryList.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">載入中...</span>
                </div>
                <p class="mt-2 text-muted">載入小分類中...</p>
            </div>`;

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

                    document.querySelectorAll(`#${this.modalId}MinorCategoryList .category-card[data-minor-id]`).forEach(card => {
                        card.addEventListener('click', () => {
                            this.selectedMinorCategory = card.dataset.minorId;
                            this.selectedMinorCategoryName = card.querySelector('h6').textContent; // 暫存小分類名稱
                            this.goToStep(3);
                            this.loadProducts(this.selectedMinorCategory);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading minor categories:', error);
                    minorCategoryList.innerHTML = '<div class="col-12 text-center py-4 text-danger">載入失敗</div>';
                });
        }

        // 載入產品
        loadProducts(minorCategoryId) {
            const productList = document.getElementById(this.modalId + 'ProductList');
            productList.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">載入中...</span>
                </div>
                <p class="mt-2 text-muted">載入產品中...</p>
            </div>`;

            fetch(`<?= base_url('api/product/getProducts') ?>/${minorCategoryId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.forEach(product => {
                        html += `
                        <button type="button" class="list-group-item list-group-item-action product-item" 
                                data-product-id="${product.pr_id}" 
                                data-product-name="${product.pr_name}"
                                data-weight-per-unit="${product.pr_weight || 0}">
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

                    document.querySelectorAll(`#${this.modalId}ProductList .product-item[data-product-id]`).forEach(item => {
                        item.addEventListener('click', () => {
                            this.selectProduct(item);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    productList.innerHTML = '<div class="text-center py-4 text-danger">載入失敗</div>';
                });
        }

        // 選擇產品
        selectProduct(item) {
            const productId = item.dataset.productId;
            const productName = item.dataset.productName;
            const weightPerUnit = item.dataset.weightPerUnit;

            const targetRow = document.querySelector(`tr[data-index="${this.currentTargetIndex}"]`);
            if (targetRow) {
                targetRow.querySelector(`input[name*="[${this.productIdField}]"]`).value = productId;
                const productText = targetRow.querySelector('.product-text');
                
                // 在表單中顯示組合名稱：小分類名稱 + 產品名稱
                const displayName = this.selectedMinorCategoryName && this.selectedMinorCategoryName !== productName 
                    ? `${this.selectedMinorCategoryName} ${productName}` 
                    : productName;
                productText.textContent = displayName;
                productText.classList.remove('text-muted');
                targetRow.querySelector('.product-weight-per-unit').value = weightPerUnit;
                
                // 標記為已變更（產品有變更）
                targetRow.dataset.hasChanged = 'true';

                // 觸發重量計算（強制重新計算）
                if (window.calculateRowWeight && typeof window.calculateRowWeight === 'function') {
                    window.calculateRowWeight(targetRow, true);
                }
            }

            bootstrap.Modal.getInstance(document.getElementById(this.modalId)).hide();
        }

        // 步驟切換
        goToStep(stepNumber) {
            document.querySelectorAll(`#${this.modalId} .step-indicator`).forEach((indicator, index) => {
                indicator.classList.remove('active', 'completed');
                if (index < stepNumber - 1) {
                    indicator.classList.add('completed');
                } else if (index === stepNumber - 1) {
                    indicator.classList.add('active');
                }
            });

            document.querySelectorAll(`#${this.modalId} .step-pane`).forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById(`${this.modalId}Step${stepNumber}`).classList.add('active');
        }
    }

    // 創建產品選擇器實例的輔助函數
    window.createProductSelector = function(config) {
        return new ProductSelector(config);
    };
</script>