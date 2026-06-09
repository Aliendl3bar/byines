<?php
session_start();
require_once '../classes/User.php';

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Basic Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        $userModel = new User();
        $userId = $userModel->register($firstName, $lastName, $email, $password);
        
        if ($userId) {
            header("Location: login.php");
            exit();
        } else {
            $error = 'An account with this email already exists.';
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="../css/signup.css">

    <main class="auth-container">
        <!-- Breadcrumb -->
        <nav class="auth-breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="current">Sign Up</span>
        </nav>

        <div class="auth-wrapper">
            <div class="auth-form">
                <h2>Create Account</h2>
                
                <?php if ($success): ?>
                    <div class="auth-success" style="display: block;"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="auth-error" style="display: block;"><?= $error ?></div>
                <?php endif; ?>

                <form action="signup.php" method="POST">
                    <!-- Name Fields -->
                    <div class="form-row-auth">
                        <div class="form-group-auth">
                            <label for="firstName">First Name</label>
                            <input 
                                type="text" id="firstName" name="firstName" 
                                placeholder="Your first name" required 
                                value="<?= htmlspecialchars($firstName ?? '') ?>"
                            >
                        </div>
                        <div class="form-group-auth">
                            <label for="lastName">Last Name</label>
                            <input 
                                type="text" id="lastName" name="lastName" 
                                placeholder="Your last name" required 
                                value="<?= htmlspecialchars($lastName ?? '') ?>"
                            >
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="form-group-auth">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" id="email" name="email" 
                            placeholder="your.email@example.com" required 
                            value="<?= htmlspecialchars($email ?? '') ?>"
                        >
                    </div>

                    <!-- Password Field -->
                    <div class="form-group-auth password-toggle">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required minlength="8">
                        <span class="toggle-icon" onclick="togglePassword('password')">👁</span>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group-auth password-toggle">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required minlength="8">
                        <span class="toggle-icon" onclick="togglePassword('confirmPassword')">👁</span>
                    </div>

                    <button type="submit" class="auth-submit-btn">Create Account</button>
                </form>

                <div class="auth-footer-links">
                    Already have an account? <a href="login.php">Log in here</a>
                </div>
            </div>
        </div>
    </main>

    <script src="../scripts/toggle_password.js"></script>

<?php include '../includes/footer.php'; ?>
