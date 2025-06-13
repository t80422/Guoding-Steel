<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Light gray background */
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            background-color: #ffffff; /* White background for the form */
        }
        .login-header {
            margin-bottom: 30px;
            text-align: center;
            color: #343a40; /* Darker text for heading */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="login-header">使用者登入</h2>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <form action="<?= url_to('AuthController::login') ?>" method="post">
            <!-- 使用者名稱 -->
            <div class="mb-3">
                <label for="username" class="form-label">使用者名稱</label>
                <select class="form-select" name="userId" required>
                    <option value="" disabled selected>請選擇</option>
                    <?php foreach ($users as $name): ?>
                        <option value="<?= esc($name['u_id']) ?>"><?= esc($name['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- 密碼 -->
            <div class="mb-3">
                <label for="password" class="form-label">密碼</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">登入</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html> 