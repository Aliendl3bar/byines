<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Byines - Timeless Elegance</title>
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
                <a class="logo" href="#">Byines</a>
            </div>
            <nav class="main-nav">
                <a href="index.php">New Arrivals</a>
                <a href="collections.php">Collections</a>
                <a href="product.php">About</a>
            </nav>  
            <div class="header-icons">
                <button class="icon-btn" data-purpose="search-trigger">
                    <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
                <button class="icon-btn" data-purpose="cart-trigger">
                    <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
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
                            <!-- Show if guest -->
                            <li class="guest-only"><a href="login.php">Sign In</a></li>
                            <li class="guest-only"><a href="signup.php">Create Account</a></li>
                            <!-- Show if logged in -->
                            <li class="user-only" style="display: none;"><span class="welcome-user">Welcome!</span></li>
                            <li class="user-only" style="display: none;"><a href="dashboard.php">My Dashboard</a></li>
                            <li class="user-only" style="display: none;"><a href="logout.php" id="logoutLink">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Check login status on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkAuthStatus();
        });

        // Update auth buttons based on session/storage
        function checkAuthStatus() {
            const isLoggedIn = sessionStorage.getItem('userLoggedIn') === 'true' || localStorage.getItem('userLoggedIn') === 'true';
            
            const guestItems = document.querySelectorAll('.guest-only');
            const userItems = document.querySelectorAll('.user-only');
            const welcomeSpan = document.querySelector('.welcome-user');

            if (isLoggedIn) {
                guestItems.forEach(item => item.style.display = 'none');
                userItems.forEach(item => item.style.display = 'block');
                
                // Set name if present
                const userData = localStorage.getItem('userData');
                if (userData) {
                    const user = JSON.parse(userData);
                    welcomeSpan.textContent = `Hello, ${user.firstName}`;
                } else {
                    welcomeSpan.textContent = 'Welcome Back!';
                }
            } else {
                guestItems.forEach(item => item.style.display = 'block');
                userItems.forEach(item => item.style.display = 'none');
            }
        }

        // Handle logout
        document.getElementById('logoutLink')?.addEventListener('click', function(e) {
            sessionStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userLoggedIn');
            checkAuthStatus();
        });
    </script>
