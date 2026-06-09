<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../classes/User.php';

    $userModel = new User();
    $userId = $_SESSION['user_id'];

    if ($userModel->deleteAccount($userId)) {
        session_destroy();
        header("Location: index.php?deleted=1");
        exit;
    } else {
        echo "Error deleting account. Please contact support.";
        exit;
    }
} else {
    header("Location: dashboard.php");
    exit;
}
