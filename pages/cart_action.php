<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../classes/Database.php';

$action = $_POST['action'] ?? '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    $db = Database::getInstance()->getConnection();

    if ($action === 'add' || $action === 'quick_add') {
        $productId = (int)$_POST['product_id'];
        $color = trim($_POST['color'] ?? '');
        $size = trim($_POST['size'] ?? '');
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        // Find the variant
        if ($action === 'quick_add') {
            $stmt = $db->prepare("SELECT v.*, p.price, p.name FROM product_variants v JOIN products p ON v.product_id = p.id WHERE v.product_id = ? LIMIT 1");
            $stmt->execute([$productId]);
            $variant = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($variant) {
                $color = $variant['color'];
                $size = $variant['size'];
            }
        } else {
            $stmt = $db->prepare("SELECT v.*, p.price, p.name FROM product_variants v JOIN products p ON v.product_id = p.id WHERE v.product_id = ? AND v.color = ? AND v.size = ? LIMIT 1");
            $stmt->execute([$productId, $color, $size]);
            $variant = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$variant) {
            echo json_encode(['success' => false, 'message' => 'Variant not found.']);
            exit;
        }

        // Create a unique cart item key
        $cartKey = $productId . '_' . md5($color . '_' . $size);

        if (isset($_SESSION['cart'][$cartKey])) {
            $newQty = $_SESSION['cart'][$cartKey]['quantity'] + $quantity;
            // Check stock
            if ($newQty > $variant['stock_quantity']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
                exit;
            }
            $_SESSION['cart'][$cartKey]['quantity'] = $newQty;
        } else {
            if ($quantity > $variant['stock_quantity']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
                exit;
            }
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'name' => $variant['name'],
                'color' => $color,
                'size' => $size,
                'quantity' => $quantity,
                'price' => $variant['price'] + $variant['price_modifier'],
                'stock' => $variant['stock_quantity']
            ];
        }

        echo json_encode(['success' => true, 'cartCount' => getCartCount(), 'message' => 'Item added to cart!']);
        exit;

    } elseif ($action === 'update') {
        $cartKey = $_POST['cart_key'] ?? '';
        $quantity = (int)$_POST['quantity'];

        if (isset($_SESSION['cart'][$cartKey])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$cartKey]);
            } else {
                // Check stock
                $item = $_SESSION['cart'][$cartKey];
                $stmt = $db->prepare("SELECT stock_quantity FROM product_variants WHERE product_id = ? AND color = ? AND size = ? LIMIT 1");
                $stmt->execute([$item['product_id'], $item['color'], $item['size']]);
                $stock = $stmt->fetchColumn();

                if ($quantity > $stock) {
                    echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds stock.', 'stock' => $stock]);
                    exit;
                }
                $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
            }
            echo json_encode(getCartTotals());
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not in cart.']);
        }
        exit;

    } elseif ($action === 'remove') {
        $cartKey = $_POST['cart_key'] ?? '';
        if (isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
            echo json_encode(getCartTotals());
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
        }
        exit;

    } elseif ($action === 'get_count') {
        echo json_encode(['success' => true, 'cartCount' => getCartCount()]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);

function getCartCount() {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function getCartTotals() {
    $subtotal = 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
        $count += $item['quantity'];
    }
    $tax = round($subtotal * 0.10, 2); // 10% tax
    $total = $subtotal + $tax;

    return [
        'success' => true,
        'cartCount' => $count,
        'subtotal' => number_format($subtotal, 2),
        'tax' => number_format($tax, 2),
        'total' => number_format($total, 2)
    ];
}
