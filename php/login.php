<?php include 'header.php'; ?>
    <link rel="stylesheet" href="../css/login.css">

    <main class="auth-container">
        <!-- Breadcrumb -->
        <nav class="auth-breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="current">Login</span>
        </nav>

        <div class="auth-wrapper">
            <!-- Form -->
            <div class="auth-form">
                <h2>Sign In</h2>
                
                <!-- Success Message -->
                <div id="successMessage" class="auth-success" style="display: none;">
                    Login successful! Redirecting...
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="auth-error" style="display: none;"></div>

                <form id="loginForm" onsubmit="handleLogin(event)">
                    <!-- Email Field -->
                    <div class="form-group-auth">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="your.email@example.com" 
                            required
                        >
                    </div>

                    <!-- Password Field -->
                    <div class="form-group-auth password-toggle">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password" 
                            required
                        >
                        <span class="toggle-icon" onclick="togglePassword('password')">👁</span>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="form-options">
                        <div class="checkbox-wrapper">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                            >
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="auth-submit-btn">Sign In</button>
                </form>

                <!-- Sign Up Link -->
                <div class="auth-footer-links">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
            } else {
                field.type = 'password';
            }
        }

        function handleLogin(event) {
            event.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;

            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Please enter a valid email address');
                return;
            }

            if (password.length < 8) {
                showError('Password must be at least 8 characters long');
                return;
            }

            const userData = localStorage.getItem('userData');
            if (!userData) {
                showError('User not found. Please sign up first.');
                return;
            }

            const user = JSON.parse(userData);
            if (user.email !== email) {
                showError('Invalid email address or password');
                return;
            }

            sessionStorage.setItem('userLoggedIn', 'true');
            if (remember) {
                localStorage.setItem('userLoggedIn', 'true');
            }

            showSuccess('Login successful! Redirecting...');
            
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
        }
    </script>

<?php include 'footer.php'; ?>
