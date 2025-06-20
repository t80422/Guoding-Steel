<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>國鼎鋼鐵-後台管理系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        #wrapper {
            display: flex;
            width: 100%;
            flex-grow: 1;
            /* Make wrapper take remaining height */
            transition: all 0.3s ease-in-out;
            /* For smooth transition of content area */
        }

        #sidebar-wrapper {
            width: 250px;
            /* Initial width */
            background: #343a40;
            /* Dark background for sidebar */
            color: #fff;
            transition: all 0.3s ease-in-out;
            flex-shrink: 0;
            /* Prevent sidebar from shrinking */
            overflow-x: hidden;
            /* Hide content when width reduces */
        }

        #wrapper.toggled #sidebar-wrapper {
            width: 0;
            /* Hide sidebar when toggled */
        }

        #page-content-wrapper {
            flex-grow: 1;
            /* Allow content to expand */
            padding: 20px;
        }

        @media (max-width: 768px) {
            #sidebar-wrapper {
                width: 0;
                /* Hidden by default on mobile */
            }

            #wrapper.toggled #sidebar-wrapper {
                width: 250px;
                /* Show sidebar when toggled on mobile */
            }
        }

        .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
            color: #fff;
        }

        .list-group-item {
            background-color: #343a40;
            color: #adb5bd;
            border: none;
            padding: 10px 20px;
        }

        .list-group-item:hover {
            background-color: #495057;
            color: #fff;
        }

        .navbar-brand img {
            height: 30px;
            /* Adjust as needed */
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <!-- header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="btn btn-outline-light" id="sidebarToggle">
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
        <div class="bg-dark border-right" id="sidebar-wrapper">
            <div class="sidebar-heading">功能列</div>
            <div class="list-group list-group-flush">
                <a href="<?= url_to('UserController::index') ?>" class="list-group-item list-group-item-action">使用者管理</a>
                <a href="<?= url_to('PositionController::index') ?>" class="list-group-item list-group-item-action">職位管理</a>
                <a href="<?= url_to('MajorCategoryController::index') ?>" class="list-group-item list-group-item-action">大分類管理</a>
                <a href="<?= url_to('MinorCategoryController::index') ?>" class="list-group-item list-group-item-action">小分類管理</a>
                <a href="<?= url_to('ProductController::index') ?>" class="list-group-item list-group-item-action">產品管理</a>
                <a href="<?= url_to('LocationController::index') ?>" class="list-group-item list-group-item-action">地點管理</a>
                <a href="<?= url_to('AuthController::authLogs') ?>" class="list-group-item list-group-item-action">登入登出紀錄</a>
                <a href="<?= url_to('OrderController::index') ?>" class="list-group-item list-group-item-action">訂單管理</a>
                <a href="<?= url_to('GpsController::index') ?>" class="list-group-item list-group-item-action">GPS管理</a>
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

        <?php if (session()->has('error')): ?>
            var errorMessages = [];
            <?php foreach (session('error') as $error): ?>
                errorMessages.push("<?= $error; ?>");
            <?php endforeach; ?>
            alert(errorMessages.join("\n"));
        <?php endif; ?>
    </script>
</body>

</html>