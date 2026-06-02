<?php include 'header.php'; ?>
    <link rel="stylesheet" href="../css/signup.css">

    <main class="auth-container">
        <!-- Breadcrumb -->
        <nav class="auth-breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="current">Sign Up</span>
        </nav>

        <div class="auth-wrapper">
            <!-- Form -->
            <div class="auth-form">
                <h2>Create Account</h2>
                
                <!-- Success Message -->
                <div id="successMessage" class="auth-success" style="display: none;">
                    Account created successfully! Redirecting...
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="auth-error" style="display: none;"></div>

                <form id="signupForm" onsubmit="handleSignup(event)">
                    <!-- Name Fields -->
                    <div class="form-row-auth">
                        <div class="form-group-auth">
                            <label for="firstName">First Name</label>
                            <input 
                                type="text" 
                                id="firstName" 
                                name="firstName" 
                                placeholder="Your first name" 
                                required
                            >
                        </div>
                        <div class="form-group-auth">
                            <label for="lastName">Last Name</label>
                            <input 
                                type="text" 
                                id="lastName" 
                                name="lastName" 
                                placeholder="Your last name" 
                                required
                            >
                        </div>
                    </div>

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
                            placeholder="Create a password" 
                            required
                            minlength="8"
                        >
                        <span class="toggle-icon" onclick="togglePassword('password')">👁</span>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group-auth password-toggle">
                        <label for="confirmPassword">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirmPassword" 
                            name="confirmPassword" 
                            placeholder="Confirm your password" 
                            required
                            minlength="8"
                        >
                        <span class="toggle-icon" onclick="togglePassword('confirmPassword')">👁</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="auth-submit-btn">Create Account</button>
                </form>

                <!-- Login Link -->
                <div class="auth-footer-links">
                    Already have an account? <a href="login.php">Log in here</a>
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

        function handleSignup(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';

            if (password !== confirmPassword) {
                showError('Passwords do not match');
                return;
            }

            if (password.length < 8) {
                showError('Password must be at least 8 characters long');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Please enter a valid email address');
                return;
            }

            const userData = {
                firstName,
                lastName,
                email
            };

            localStorage.setItem('userData', JSON.stringify(userData));
            sessionStorage.setItem('userLoggedIn', 'true');
            localStorage.setItem('userLoggedIn', 'true');

            showSuccess('Account created successfully! Redirecting...');
            
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
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
