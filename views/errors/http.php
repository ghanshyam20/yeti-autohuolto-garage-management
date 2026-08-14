<section class="py-5">
    <div class="container py-5 text-center">
        <span class="section-tag">ERROR <?= e($statusCode) ?></span>
        <h1 class="display-4 fw-bold mt-3"><?= e($title) ?></h1>
        <p class="lead mt-4"><?= e($message) ?></p>
        <a class="btn btn-warning px-4 py-3 mt-3" href="<?= e(url('/')) ?>">Return Home</a>
    </div>
</section>
