<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/dashboard.css')) ?>">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <h1 class="logo">YETI <span>AUTOHUOLTO</span></h1>
            <p class="subtitle">Garage Owner Dashboard</p>

            <?php foreach (pull_flashes() as $flash): ?>
                <div class="alert"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>

            <?php if ($error !== ''): ?>
                <div class="alert" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= e(url('/dashboard/login/')) ?>">
                <?= csrf_field() ?>
                <input class="form-control" type="text" name="username" placeholder="Username" value="<?= e($username) ?>" required autocomplete="username">

                <div class="password-wrapper">
                    <input id="password" class="password-input" type="password" name="password" placeholder="Password" required autocomplete="current-password">
                    <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Show or hide password">👁</button>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <p class="footer-text">Authorized personnel only</p>
            <p class="footer-text"><a href="<?= e(url('/')) ?>">Return to website</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            password.type = password.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
