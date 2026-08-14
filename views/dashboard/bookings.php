<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings | Yeti Autohuolto</title>
    <link rel="stylesheet" href="<?= e(asset('css/dashboard_home.css')) ?>">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="brand"><span class="brand-dark">YETI</span> <span class="brand-orange">AUTOHUOLTO</span></div>
        <nav>
            <a href="<?= e(url('/dashboard/')) ?>">Dashboard</a>
            <a href="<?= e(url('/dashboard/bookings/')) ?>" class="active">Bookings</a>
            <a href="<?= e(url('/booking/')) ?>">Public Booking Page</a>
            <a href="<?= e(url('/')) ?>">View Website</a>
            <form method="POST" action="<?= e(url('/dashboard/logout/')) ?>"><?= csrf_field() ?><button class="sidebar-link" type="submit">Log Out</button></form>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar">
            <div><h1>Bookings</h1><p class="muted"><?= e($result['total']) ?> booking request<?= $result['total'] === 1 ? '' : 's' ?></p></div>
            <div class="top-right">Welcome, <?= e(owner_username()) ?></div>
        </header>

        <?php foreach ($flashes as $flash): ?>
            <div class="dashboard-alert dashboard-alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <section class="table-card">
            <form class="filter-form" method="GET" action="<?= e(url('/dashboard/bookings/')) ?>">
                <input type="search" name="search" placeholder="Search customer, vehicle or registration" value="<?= e($filters['search']) ?>">
                <select name="status">
                    <option value="">All statuses</option>
                    <?php foreach (status_choices() as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="service">
                    <option value="">All services</option>
                    <?php foreach (service_choices() as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['service'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date" value="<?= e($filters['date']) ?>">
                <button class="dashboard-button" type="submit">Filter</button>
                <a class="dashboard-button secondary" href="<?= e(url('/dashboard/bookings/')) ?>">Clear</a>
            </form>

            <div class="table-responsive">
                <table class="booking-table">
                    <thead>
                    <tr><th>Customer</th><th>Vehicle</th><th>Service</th><th>Requested</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php if ($result['items'] === []): ?>
                        <tr><td colspan="6">No bookings match these filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($result['items'] as $booking): ?>
                            <tr>
                                <td><strong><?= e($booking['full_name']) ?></strong><br><span class="muted"><?= e($booking['phone_number']) ?></span></td>
                                <td><?= e($booking['vehicle_make'] . ' ' . $booking['vehicle_model']) ?><br><span class="muted"><?= e($booking['registration_number'] ?: '—') ?></span></td>
                                <td><?= e(service_label((string) $booking['service_required'])) ?></td>
                                <td><?= e($booking['preferred_date']) ?><br><span class="muted"><?= e(time_label((string) $booking['preferred_time'])) ?></span></td>
                                <td><span class="status status-<?= e($booking['status']) ?>"><?= e(status_label((string) $booking['status'])) ?></span></td>
                                <td><a class="table-action" href="<?= e(url('/dashboard/bookings/' . $booking['id'] . '/')) ?>">Open</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($result['pages'] > 1): ?>
                <nav class="pagination" aria-label="Booking pages">
                    <?php for ($page = 1; $page <= $result['pages']; $page++): ?>
                        <a class="<?= $page === $result['page'] ? 'active' : '' ?>" href="<?= e(query_url(['page' => $page])) ?>"><?= e($page) ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
