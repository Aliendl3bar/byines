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

require_once '../classes/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

$action = $_POST['action'] ?? '';

try {
    if ($action === 'delete_order') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
            exit;
        }

        $pdo->beginTransaction();

        // Delete order items first
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);

        // Delete the order
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Order deleted successfully.']);
        exit;

    } elseif ($action === 'update_status') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';

        $validStatuses = ['pending', 'processing', 'in_transit', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);

        echo json_encode(['success' => true, 'message' => 'Order status updated.']);
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        exit;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
