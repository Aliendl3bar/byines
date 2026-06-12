<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// admin auth guard
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Product.php';

$productModel = new Product();
$db = Database::getInstance();
$pdo = $db->getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// helper to create clean slugs
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

// helper to handle file uploads
function uploadProductImages($productId, $fileInputName, $productModel) {
    if (!isset($_FILES[$fileInputName]) || empty($_FILES[$fileInputName]['name'][0])) {
        return false;
    }

    $targetDir = __DIR__ . '/../products/' . $productId . '/img/';
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $totalFiles = count($_FILES[$fileInputName]['name']);
    $uploadedCount = 0;

    for ($i = 0; $i < $totalFiles; $i++) {
        if ($_FILES[$fileInputName]['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $fileName = $_FILES[$fileInputName]['name'][$i];
        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'][$i];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $destPath = $targetDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // if it's the first image, mark as primary
                $currentImages = $productModel->getImages($productId);
                $isMain = empty($currentImages) ? 1 : 0;
                
                $productModel->addImage($productId, $newFileName, null, $isMain);
                $uploadedCount++;
            }
        }
    }
    return $uploadedCount > 0;
}

// --- route actions ---

if ($action === 'save_product_assets') {
    header('Content-Type: application/json');
    $productId = (int)($_POST['product_id'] ?? 0);
    $imagesJson = $_POST['images'] ?? '[]';
    $variantsJson = $_POST['variants'] ?? '[]';

    $imagesList = json_decode($imagesJson, true);
    $variantsList = json_decode($variantsJson, true);

    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. process images
        // fetch current images from database to check for deleted ones
        $currentImages = $productModel->getImages($productId);
        $remainingIds = [];
        
        foreach ($imagesList as $imgData) {
            $imgId = (int)($imgData['id'] ?? 0);
            if ($imgId > 0) {
                $remainingIds[] = $imgId;
                // update color, sort_order, and is_main
                $stmtImg = $pdo->prepare("UPDATE product_images SET color = ?, sort_order = ?, is_main = ? WHERE id = ? AND product_id = ?");
                $colorVal = ($imgData['color'] === '' || $imgData['color'] === null) ? null : $imgData['color'];
                $stmtImg->execute([$colorVal, (int)$imgData['sort_order'], (int)$imgData['is_main'], $imgId, $productId]);
            }
        }

        // delete images that were in current db but are missing in the submitted list
        foreach ($currentImages as $dbImg) {
            if (!in_array($dbImg['id'], $remainingIds)) {
                $productModel->deleteImage($dbImg['id']);
            }
        }

        // 2. process variants
        $currentVariants = $productModel->getVariants($productId);
        $remainingVariantIds = [];

        foreach ($variantsList as $vData) {
            $vId = (int)($vData['id'] ?? 0);
            $color = trim($vData['color'] ?? '');
            $size = trim($vData['size'] ?? '');
            $stock = (int)($vData['stock_quantity'] ?? 0);
            $priceMod = (float)($vData['price_modifier'] ?? 0.00);

            if (empty($color) || empty($size)) {
                continue;
            }

            if ($vId > 0) {
                // update existing
                $remainingVariantIds[] = $vId;
                $productModel->updateVariant($vId, $color, $size, $stock, $priceMod);
            } else {
                // add new
                $newId = $productModel->addVariant($productId, $color, $size, $stock, $priceMod);
                if ($newId) {
                    $remainingVariantIds[] = $newId;
                }
            }
        }

        // delete variants that were removed
        foreach ($currentVariants as $dbV) {
            if (!in_array($dbV['id'], $remainingVariantIds)) {
                $productModel->deleteVariant($dbV['id']);
            }
        }

        $pdo->commit();

        // return updated lists to sync frontend
        $updatedImages = $productModel->getImages($productId);
        $updatedVariants = $productModel->getVariants($productId);

        echo json_encode([
            'success' => true,
            'images' => $updatedImages,
            'variants' => $updatedVariants
        ]);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }

} elseif ($action === 'ajax_upload_images') {
    header('Content-Type: application/json');
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
        exit;
    }

    if (uploadProductImages($productId, 'new_images', $productModel)) {
        $updatedImages = $productModel->getImages($productId);
        echo json_encode(['success' => true, 'images' => $updatedImages]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload images.']);
    }
    exit;

} elseif ($action === 'add') {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $price = (float)($_POST['price'] ?? 0.00);
    $description = trim($_POST['description'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name) || empty($sku) || $categoryId <= 0 || $price <= 0) {
        $_SESSION['admin_error'] = "All fields (except images) are required, and price must be greater than 0.";
        header("Location: admin_dashboard.php?tab=products");
        exit;
    }

    // check unique sku
    if ($productModel->skuExists($sku)) {
        $_SESSION['admin_error'] = "Product SKU '$sku' already exists.";
        header("Location: admin_dashboard.php?tab=products");
        exit;
    }

    $slug = createSlug($name);
    
    // check unique slug
    if ($productModel->slugExists($slug)) {
        $slug .= '-' . time();
    }

    $productId = $productModel->create($categoryId, $name, $slug, $sku, $description, $price, null, $isActive);

    if ($productId) {
        // automatically insert a default variant (color: default, size: m, stock)
        $productModel->addVariant($productId, 'Default', 'M', $stock, 0.00);

        // process uploads
        uploadProductImages($productId, 'images', $productModel);

        $_SESSION['admin_success'] = "Product '$name' added successfully.";
    } else {
        $_SESSION['admin_error'] = "Failed to create product record.";
    }

    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $price = (float)($_POST['price'] ?? 0.00);
    $description = trim($_POST['description'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($id <= 0 || empty($name) || empty($sku) || $categoryId <= 0 || $price <= 0) {
        $_SESSION['admin_error'] = "Required fields cannot be empty.";
        header("Location: admin_dashboard.php?tab=products");
        exit;
    }

    $slug = createSlug($name);
    $success = $productModel->update($id, $categoryId, $name, $slug, $sku, $description, $price, null, $isActive);

    if ($success) {
        $_SESSION['admin_success'] = "Product details updated.";
    } else {
        $_SESSION['admin_error'] = "Failed to update product details.";
    }

    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'upload_image') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId > 0) {
        if (uploadProductImages($productId, 'new_images', $productModel)) {
            $_SESSION['admin_success'] = "Images uploaded successfully.";
        } else {
            $_SESSION['admin_error'] = "No valid files uploaded.";
        }
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'set_main_image') {
    $productId = (int)($_GET['product_id'] ?? 0);
    $imageId = (int)($_GET['image_id'] ?? 0);

    if ($productId > 0 && $imageId > 0) {
        if ($productModel->setMainImage($productId, $imageId)) {
            $_SESSION['admin_success'] = "Primary thumbnail updated.";
        } else {
            $_SESSION['admin_error'] = "Failed to update primary thumbnail.";
        }
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'delete_image') {
    $imageId = (int)($_GET['image_id'] ?? 0);

    if ($imageId > 0) {
        if ($productModel->deleteImage($imageId)) {
            $_SESSION['admin_success'] = "Image deleted successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete image record.";
        }
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'reorder_image') {
    $imageId = (int)($_GET['image_id'] ?? 0);
    $direction = $_GET['direction'] ?? '';

    if ($imageId > 0 && ($direction === 'prev' || $direction === 'next')) {
        if ($productModel->reorderImage($imageId, $direction)) {
            $_SESSION['admin_success'] = "Image order updated.";
        } else {
            $_SESSION['admin_error'] = "Failed to change image order.";
        }
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

// --- variant management ---

} elseif ($action === 'add_variant') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $priceMod = (float)($_POST['price_modifier'] ?? 0.00);

    if ($productId > 0 && !empty($color) && !empty($size)) {
        $result = $productModel->addVariant($productId, $color, $size, $stock, $priceMod);
        if ($result) {
            $_SESSION['admin_success'] = "Variant ($color / $size) added.";
        } else {
            $_SESSION['admin_error'] = "Failed to add variant. It may already exist.";
        }
    } else {
        $_SESSION['admin_error'] = "Color and Size are required.";
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'edit_variant') {
    $variantId = (int)($_POST['variant_id'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $priceMod = (float)($_POST['price_modifier'] ?? 0.00);

    if ($variantId > 0 && !empty($color) && !empty($size)) {
        if ($productModel->updateVariant($variantId, $color, $size, $stock, $priceMod)) {
            $_SESSION['admin_success'] = "Variant updated.";
        } else {
            $_SESSION['admin_error'] = "Failed to update variant.";
        }
    } else {
        $_SESSION['admin_error'] = "Color and Size are required.";
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'delete_variant') {
    $variantId = (int)($_GET['variant_id'] ?? 0);

    if ($variantId > 0) {
        if ($productModel->deleteVariant($variantId)) {
            $_SESSION['admin_success'] = "Variant deleted.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete variant. It may be referenced by an order.";
        }
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

// --- image color assignment ---

} elseif ($action === 'update_image_color') {
    $imageId = (int)($_POST['image_id'] ?? 0);
    $color = trim($_POST['color'] ?? '');

    if ($imageId > 0) {
        if ($productModel->updateImageColor($imageId, $color)) {
            $_SESSION['admin_success'] = "Image color updated.";
        } else {
            $_SESSION['admin_error'] = "Failed to update image color.";
        }
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

} elseif ($action === 'delete_product') {
    $productId = (int)($_GET['product_id'] ?? 0);

    if ($productId > 0) {
        if ($productModel->deleteProduct($productId)) {
            $_SESSION['admin_success'] = "Product deleted successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete product. It may be referenced by existing orders.";
        }
    } else {
        $_SESSION['admin_error'] = "Invalid product ID.";
    }
    header("Location: admin_dashboard.php?tab=products");
    exit;

} else {
    header("Location: admin_dashboard.php?tab=products");
    exit;
}
