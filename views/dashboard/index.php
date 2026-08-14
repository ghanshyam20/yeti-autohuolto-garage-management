<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Yeti Autohuolto</title>
    <link rel="stylesheet" href="<?= e(asset('css/dashboard_home.css')) ?>">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="brand"><span class="brand-dark">YETI</span> <span class="brand-orange">AUTOHUOLTO</span></div>
        <nav>
            <a href="<?= e(url('/dashboard/')) ?>" class="active">Dashboard</a>
            <a href="<?= e(url('/dashboard/bookings/')) ?>">Bookings</a>
            <a href="<?= e(url('/booking/')) ?>">Public Booking Page</a>
            <a href="<?= e(url('/')) ?>">View Website</a>
            <form method="POST" action="<?= e(url('/dashboard/logout/')) ?>">
                <?= csrf_field() ?>
                <button class="sidebar-link" type="submit">Log Out</button>
            </form>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar">
            <h1>Dashboard</h1>
            <div class="top-right">Welcome, <?= e(owner_username()) ?></div>
        </header>

        <section class="cards">
            <div class="stats-grid">
                <div class="stat-card"><h2><?= e($totalBookings) ?></h2><p>Total Bookings</p></div>
                <div class="stat-card"><h2><?= e($pendingBookings) ?></h2><p>Pending</p></div>
                <div class="stat-card"><h2><?= e($confirmedBookings) ?></h2><p>Confirmed</p></div>
                <div class="stat-card"><h2><?= e($completedBookings) ?></h2><p>Completed</p></div>
            </div>
        </section>

        <section class="recent">
            <div class="table-card">
                <div class="table-card-header">
                    <h2>Recent Booking Requests</h2>
                    <a class="dashboard-button" href="<?= e(url('/dashboard/bookings/')) ?>">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="booking-table">
                        <thead><tr><th>Name</th><th>Vehicle</th><th>Service</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if ($recentBookings === []): ?>
                            <tr><td colspan="4">No bookings yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><a href="<?= e(url('/dashboard/bookings/' . $booking['id'] . '/')) ?>"><?= e($booking['full_name']) ?></a></td>
                                    <td><?= e($booking['vehicle_make'] . ' ' . $booking['vehicle_model']) ?></td>
                                    <td><?= e(service_label((string) $booking['service_required'])) ?></td>
                                    <td><span class="status status-<?= e($booking['status']) ?>"><?= e(status_label((string) $booking['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
