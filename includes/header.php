<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

// Autoload core database and domain classes
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Byines' : 'Byines - Timeless Elegance' ?></title>
    <!-- Google Fonts: Noto Serif -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"/>
    <!-- Material Symbols for collections page -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="site-body">

    <header class="main-header">
        <div class="header-container">
            <div class="logo-area">
                <a class="logo" href="index.php">Byines</a>
            </div>
            <nav class="main-nav">
                <a href="shop.php">New Arrivals</a>
                <a href="shop.php">Collections</a>
                <a href="product.php">About</a>
            </nav>  
            <div class="header-icons">
                <button class="icon-btn" data-purpose="search-trigger">
                    <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
                <?php
                require_once __DIR__ . '/../classes/Cart.php';
                $cart = new Cart();
                $cartCount = $cart->getCount();
                ?>
                <a href="cart.php" class="icon-btn cart-icon-link" data-purpose="cart-trigger">
                    <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span id="cart-badge-count" class="cart-badge" style="display: <?= $cartCount > 0 ? 'flex' : 'none' ?>;">
                        <?= $cartCount ?>
                    </span>
                </a>
                <div class="account-menu-container">
                    <button class="icon-btn" id="accountBtn" data-purpose="account-trigger">
                        <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                    <!-- Account Dropdown Menu -->
                    <div class="account-dropdown" id="accountDropdown">
                        <div class="dropdown-arrow"></div>
                        <ul class="dropdown-list">
                            <!-- Check PHP Session for auth state -->
                            <?php if (!$isLoggedIn): ?>
                                <li><a href="login.php">Sign In</a></li>
                                <li><a href="signup.php">Create Account</a></li>
                            <?php else: ?>
                                <li><span class="welcome-user welcome-user-text">Hello, <?= htmlspecialchars($userName) ?>!</span></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <li><a href="admin_dashboard.php" class="admin-link-alert">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><a href="dashboard.php">My Dashboard</a></li>
                                <li><a href="logout.php">Logout</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script src="../js/script.js?v=<?= time() ?>"></script>
