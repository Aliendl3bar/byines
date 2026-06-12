<?php
session_start();
require_once '../classes/User.php';

// redirect logged-in users away from the login page
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $userModel = new User();
        $user = $userModel->login($email, $password);
        
        if ($user) {
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid email address or password.';
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="../css/login.css">

    <main class="auth-container">
        <!-- breadcrumb -->
        <nav class="auth-breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="current">Login</span>
        </nav>

        <div class="auth-wrapper">
            <div class="auth-form">
                <h2>Sign In</h2>
                
                <?php if ($error): ?>
                    <div class="auth-error" style="display: block;"><?= $error ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <!-- email field -->
                    <div class="form-group-auth">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" id="email" name="email" 
                            placeholder="your.email@example.com" required 
                            value="<?= htmlspecialchars($email ?? '') ?>"
                        >
                    </div>

                    <!-- password field -->
                    <div class="form-group-auth password-toggle">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <span class="toggle-icon" onclick="togglePassword('password')">👁</span>
                    </div>

                    <!-- remember & forgot options -->
                    <div class="form-options">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="auth-submit-btn">Sign In</button>
                </form>

                <div class="auth-footer-links">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </div>
            </div>
        </div>
    </main>

    <script src="../scripts/toggle_password.js"></script>

<?php include '../includes/footer.php'; ?>
