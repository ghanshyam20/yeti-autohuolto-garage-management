<nav class="navbar navbar-expand-lg custom-navbar sticky-top">

    <div class="container">

        <a class="navbar-brand logo-text" href="<?= e(url('/')) ?>">

            YETI
            <span>AUTOHUOLTO</span>

        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu"
            aria-controls="navbarMenu"
            aria-expanded="false"
            aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="navbarMenu">

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">

                    <a class="nav-link <?= e(active_nav('/')) ?>" href="<?= e(url('/')) ?>">Home</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link <?= e(active_nav('/services/')) ?>" href="<?= e(url('/services/')) ?>">Services</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link <?= e(active_nav('/booking/')) ?>" href="<?= e(url('/booking/')) ?>">Booking</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link <?= e(active_nav('/about/')) ?>" href="<?= e(url('/about/')) ?>">About</a>

                </li>

            

                <li class="nav-item">

                    <a class="nav-link <?= e(active_nav('/contact/')) ?>" href="<?= e(url('/contact/')) ?>">Contact</a>

                </li>

            </ul>

            <div class="d-flex gap-2">

                <a
                    href="tel:+358451566199"
                    class="btn btn-outline-dark"
                    aria-label="Call Yeti Autohuolto">

                    <i class="bi bi-telephone"></i>

                </a>

                <a
                    href="<?= e(url('/booking/')) ?>"
                    class="btn btn-warning">

                    Book Appointment

                </a>

            </div>

        </div>

    </div>

</nav>
