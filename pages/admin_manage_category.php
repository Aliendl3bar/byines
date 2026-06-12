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
require_once '../classes/Category.php';

$categoryModel = new Category();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

// helper to handle image upload
function uploadCategoryImage($fileInputName) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $targetDir = __DIR__ . '/../categories_img/';
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = $_FILES[$fileInputName]['name'];
    $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = 'cat_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
        $destPath = $targetDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            return '../categories_img/' . $newFileName;
        }
    }
    return null;
}

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $slug = createSlug($name);

    if (empty($name)) {
        $_SESSION['admin_error'] = "Category name is required.";
        header("Location: admin_dashboard.php?tab=categories");
        exit;
    }

    $imageUrl = uploadCategoryImage('image');
    if (!$imageUrl) {
        $imageUrl = '';
    }

    try {
        if ($categoryModel->create($name, $slug, $imageUrl)) {
            $_SESSION['admin_success'] = "Category '$name' created successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to create category.";
        }
    } catch (Exception $e) {
        $_SESSION['admin_error'] = "Error: " . $e->getMessage();
    }

    header("Location: admin_dashboard.php?tab=categories");
    exit;
} elseif ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) $slug = createSlug($name);

    if ($id <= 0 || empty($name)) {
        $_SESSION['admin_error'] = "Category name is required.";
        header("Location: admin_dashboard.php?tab=categories");
        exit;
    }

    $existing = $categoryModel->getById($id);
    if ($existing) {
        try {
            $imageUrl = uploadCategoryImage('image');
            if (!$imageUrl) {
                $imageUrl = $existing['image_url'] ?? '';
            }

            if ($categoryModel->update($id, $name, $slug, $imageUrl)) {
                $_SESSION['admin_success'] = "Category updated successfully.";
            } else {
                $_SESSION['admin_error'] = "Failed to update category.";
            }
        } catch (Exception $e) {
            $_SESSION['admin_error'] = "Error: " . $e->getMessage();
        }
    }
    header("Location: admin_dashboard.php?tab=categories");
    exit;
} elseif ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        try {
            if ($categoryModel->delete($id)) {
                $_SESSION['admin_success'] = "Category deleted successfully.";
            } else {
                $_SESSION['admin_error'] = "Failed to delete category.";
            }
        } catch (Exception $e) {
             $_SESSION['admin_error'] = "Error: Cannot delete category. It might be in use.";
        }
    }
    header("Location: admin_dashboard.php?tab=categories");
    exit;
} else {
    header("Location: admin_dashboard.php?tab=categories");
    exit;
}
