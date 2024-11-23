<header>
    <nav class="navbar">
        <a href="index.php" class="nav-logo">
            <h2 class="logo-text">☕ Coffee</h2>
        </a>

        <ul class="nav-menu">
            <button id="menu-close-button" class="fas fa-times"></button>
            <li class="nav-item">
                <a href="index.php" class="nav-link">Home</a>
            </li>
            <li class="nav-item">
                <a href="#about" class="nav-link">About</a>
            </li>
            <li class="nav-item">
                <a href="#menu" class="nav-link">Menu</a>
            </li>
            <li class="nav-item">
                <a href="#gallery" class="nav-link">Gallery</a>
            </li>
            <li class="nav-item">
                <a href="#contact" class="nav-link">Contact</a>
            </li>
            <?php
            if(isset($_SESSION['user_id'])) {
                if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
                    echo '<li class="nav-item"><a href="admin.php" class="nav-link">Admin Dashboard</a></li>';
                }
                echo '<li class="nav-item"><a href="account.php" class="nav-link">My Account</a></li>';
                echo '<li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>';
            } else {
                echo '<li class="nav-item"><a href="login.php" class="nav-link">Login</a></li>';
                echo '<li class="nav-item"><a href="signup.php" class="nav-link">Sign Up</a></li>';
            }
            ?>
        </ul>

        <button id="menu-open-button" class="fas fa-bars"></button>
    </nav>
</header> 