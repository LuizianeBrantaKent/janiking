<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/assets/images/logo_white.png" alt="JaniKing" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'about_us.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="/about_us.php">About Us</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'join_us.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="/join_us.php">Join Us</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'contact_us.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="/contact_us.php">Contact Us</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-light text-primary px-3 mr-2" href="/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-light text-primary px-3" href="/book_appointment.php">Book Appointment</a>
                </li>
            </ul>
        </div>
    </div>
</nav>