<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Professional vehicle maintenance and repair in Espoo. Reliable service, transparent pricing and customer-first care.">
    <title><?= e($title) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <?php foreach ([ 'buttons', 'navbar', 'home', 'services', 'about', 'booking', 'contact', 'footer', 'responsive'] as $stylesheet): ?>
        <link rel="stylesheet" href="<?= e(asset('css/' . $stylesheet . '.css')) ?>">
    <?php endforeach; ?>
</head>
<body>
    <?php view('includes/common/navbar'); ?>

    <main>
        <?php if ($flashes !== []): ?>
            <div class="container mt-4" aria-live="polite">
                <?php foreach ($flashes as $flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <?php view('includes/common/footer'); ?>
    <?php view('includes/common/scripts'); ?>
</body>
</html>
