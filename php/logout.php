<?php
// Clear login status
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Logging out...</title>
    <script>
        // Clear login status from storage
        sessionStorage.removeItem('userLoggedIn');
        localStorage.removeItem('userLoggedIn');
        
        // Redirect to home page
        window.location.href = 'index.php';
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>
