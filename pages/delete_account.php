<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../classes/Database.php';
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $userId = $_SESSION['user_id'];

    // Delete user from users table
    // (Orders placed by this user will have user_id set to NULL due to ON DELETE SET NULL constraint)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$userId])) {
        // Destroy session
        session_destroy();
        // Redirect to home page with a goodbye parameter (optional)
        header("Location: index.php?deleted=1");
        exit;
    } else {
        // Fallback error
        echo "Error deleting account. Please contact support.";
        exit;
    }
} else {
    // If accessed via GET, redirect back
    header("Location: dashboard.php");
    exit;
}
