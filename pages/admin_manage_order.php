<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Admin Auth Guard
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

require_once '../classes/Order.php';

$orderModel = new Order();
$action = $_POST['action'] ?? '';

try {
    if ($action === 'delete_order') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
            exit;
        }

        if ($orderModel->deleteOrder($orderId)) {
            echo json_encode(['success' => true, 'message' => 'Order deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete order.']);
        }
        exit;

    } elseif ($action === 'update_status') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';

        $validStatuses = ['pending', 'processing', 'in_transit', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit;
        }

        $orderModel->updateStatus($orderId, $newStatus);
        echo json_encode(['success' => true, 'message' => 'Order status updated.']);
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
