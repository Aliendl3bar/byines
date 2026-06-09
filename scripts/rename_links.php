<?php
$files = [
    '../includes/header.php',
    '../pages/cart.php',
    '../pages/dashboard.php',
    '../pages/index.php',
    '../pages/product.php',
];

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $newContent = str_replace('collections.php', 'shop.php', $content);
        file_put_contents($path, $newContent);
        echo "Updated $f\n";
    }
}
echo "Done replacing collections.php with shop.php.";
