<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking #<?= e($booking['id']) ?> | Yeti Autohuolto</title>
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
            <div><a class="back-link" href="<?= e(url('/dashboard/bookings/')) ?>">← All bookings</a><h1>Booking #<?= e($booking['id']) ?></h1></div>
            <div class="top-right">Created <?= e($booking['created_at']) ?></div>
        </header>

        <?php foreach ($flashes as $flash): ?>
            <div class="dashboard-alert dashboard-alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?php if ($errors !== []): ?>
            <div class="dashboard-alert dashboard-alert-danger"><strong>Please correct the form:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <section class="table-card">
            <form class="admin-form" method="POST" action="<?= e(url('/dashboard/bookings/' . $booking['id'] . '/')) ?>">
                <?= csrf_field() ?>

                <div class="form-grid">
                    <div><label for="edit_full_name">Full Name *</label><input id="edit_full_name" name="full_name" maxlength="100" value="<?= e(form_value($values, 'full_name')) ?>" required></div>
                    <div><label for="edit_phone">Phone *</label><input id="edit_phone" name="phone_number" maxlength="25" value="<?= e(form_value($values, 'phone_number')) ?>" required></div>
                    <div><label for="edit_email">Email *</label><input id="edit_email" type="email" name="email" maxlength="254" value="<?= e(form_value($values, 'email')) ?>" required></div>
                    <div><label for="edit_registration">Registration</label><input id="edit_registration" name="registration_number" maxlength="20" value="<?= e(form_value($values, 'registration_number')) ?>"></div>
                    <div><label for="edit_make">Vehicle Make *</label><input id="edit_make" name="vehicle_make" maxlength="100" value="<?= e(form_value($values, 'vehicle_make')) ?>" required></div>
                    <div><label for="edit_model">Vehicle Model *</label><input id="edit_model" name="vehicle_model" maxlength="100" value="<?= e(form_value($values, 'vehicle_model')) ?>" required></div>
                    <div><label for="edit_service">Service *</label><select id="edit_service" name="service_required" required><?php foreach (service_choices() as $value => $label): ?><option value="<?= e($value) ?>" <?= form_value($values, 'service_required') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                    <div><label for="edit_status">Status *</label><select id="edit_status" name="status" required><?php foreach (status_choices() as $value => $label): ?><option value="<?= e($value) ?>" <?= form_value($values, 'status') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                    <div><label for="edit_date">Preferred Date *</label><input id="edit_date" type="date" name="preferred_date" value="<?= e(form_value($values, 'preferred_date')) ?>" required></div>
                    <div><label for="edit_time">Preferred Time</label><select id="edit_time" name="preferred_time"><?php foreach (time_choices() as $value => $label): ?><option value="<?= e($value) ?>" <?= form_value($values, 'preferred_time') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                    <div class="full-width"><label for="edit_problem">Problem Description</label><textarea id="edit_problem" name="problem_description" rows="6" maxlength="5000"><?= e(form_value($values, 'problem_description')) ?></textarea></div>
                </div>

                <div class="form-actions"><button class="dashboard-button" type="submit">Save Changes</button><a class="dashboard-button secondary" href="mailto:<?= e($booking['email']) ?>">Email Customer</a><a class="dashboard-button secondary" href="tel:<?= e($booking['phone_number']) ?>">Call Customer</a></div>
            </form>

            <form class="delete-form" method="POST" action="<?= e(url('/dashboard/bookings/' . $booking['id'] . '/delete/')) ?>" onsubmit="return confirm('Delete this booking permanently?');">
                <?= csrf_field() ?>
                <button class="danger-button" type="submit">Delete Booking</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
