<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>國鼎鋼鐵-後台管理系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('css/custom.css') ?>">
</head>

<body>
    <!-- header -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-bg-color">
        <div class="container-fluid">
            <button class="btn btn-outline-dark" id="sidebarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand ms-3" href="">
                <img src="/images/國鼎LOGO.jpg" alt="Company Logo">
                國鼎鋼鐵
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= session()->get('userName') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?= url_to('AuthController::logout') ?>">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- content -->
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="border-right" id="sidebar-wrapper">
            <div class="sidebar-heading">功能列</div>
            <div class="list-group list-group-flush">
                <!-- 產品群組 -->
                <div class="nav-dropdown">
                    <div class="list-group-item list-group-item-action">
                        <i class="bi bi-box"></i>產品 <i class="bi bi-chevron-right float-end"></i>
                    </div>
                    <div class="dropdown-menu-custom">
                        <a href="<?= url_to('MajorCategoryController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-folder"></i>大分類管理
                        </a>
                        <a href="<?= url_to('MinorCategoryController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-folder2"></i>小分類管理
                        </a>
                        <a href="<?= url_to('ProductController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-box"></i>產品管理
                        </a>
                    </div>
                </div>
                <!-- 機械群組 -->
                <div class="nav-dropdown">
                    <div class="list-group-item list-group-item-action">
                        <i class="bi bi-gear"></i>機械 <i class="bi bi-chevron-right float-end"></i>
                    </div>
                    <div class="dropdown-menu-custom">
                        <a href="<?= url_to('MachineController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-gear"></i>機械管理
                        </a>
                        <a href="<?= url_to('MachineMaintenanceController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-tools"></i>機械保養管理
                        </a>
                        <a href="<?= url_to('MachineRepairController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-wrench"></i>機械維修管理
                        </a>
                    </div>
                </div>
                <!-- 租賃群組 -->
                <div class="nav-dropdown">
                    <div class="list-group-item list-group-item-action">
                        <i class="bi bi-clipboard-check"></i>租賃 <i class="bi bi-chevron-right float-end"></i>
                    </div>
                    <div class="dropdown-menu-custom">
                        <a href="<?= url_to('RentalController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-clipboard-check"></i>租賃單管理
                        </a>
                        <a href="<?= url_to('ManufacturerInventoryController::index') ?>" class="dropdown-item-custom">
                            <i class="bi bi-boxes"></i>租賃庫存管理
                        </a>
                        <a href="<?= url_to('RentalController::index_order') ?>" class="dropdown-item-custom">
                            <i class="bi bi-cart"></i>租賃訂單管理
                        </a>
                    </div>
                </div>
                <a href="<?= url_to('UserController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-people"></i>使用者管理
                </a>
                <a href="<?= url_to('PositionController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-person-badge"></i>職位管理
                </a>
                <a href="<?= url_to('InventoryController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-boxes"></i>庫存管理
                </a>
                <a href="<?= url_to('LocationController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-geo-alt"></i>地點管理
                </a>
                <a href="<?= url_to('AuthController::authLogs') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-file-text"></i>登入登出紀錄
                </a>
                <a href="<?= url_to('OrderController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-cart"></i>訂單管理
                </a>
                <a href="<?= url_to('GpsController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-pin-map"></i>GPS管理
                </a>
                <a href="<?= url_to('ManufacturerController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-building"></i>廠商管理
                </a>
                <a href="<?= url_to('RoadPlateController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-grid-3x3"></i>鋪路鋼板
                </a>
                <a href="<?= url_to('ExcelController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-file-earmark-excel"></i>匯入Excel
                </a>
                <a href="<?= url_to('ProjectItemController::index') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-list-ul"></i>施工項目
                </a>
            </div>
        </div>
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });

        // 動態調整子選單位置
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.nav-dropdown');

            dropdowns.forEach(function(dropdown) {
                const dropdownMenu = dropdown.querySelector('.dropdown-menu-custom');

                if (dropdownMenu) {
                    dropdown.addEventListener('mouseenter', function() {
                        const rect = this.getBoundingClientRect();
                        dropdownMenu.style.top = rect.top + 'px';
                    });
                }
            });
        });
    </script>
</body>

</html>