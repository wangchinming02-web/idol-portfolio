
<?php
// index.php - 可跑版本
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/app/controllers/HomeController.php';
try {
    $controller = new HomeController();
    $controller->index();
} catch (\Throwable $e) {
    http_response_code(500);
    echo "Oops! 發生錯誤: " . htmlspecialchars($e->getMessage());
}