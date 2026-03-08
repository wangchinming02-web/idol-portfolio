<?php
// includes/config.php - 集中敏感設定（資料庫 + 郵件），上線勿公開！

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
// ini_set('error_log', '/errors.log');  // 如果主機允許寫入根目錄，可開啟

// === 資料庫設定 ===
$db_host    = 'localhost';           // 或 sqlxxx.infinityfree.com
$db_port    = 3306;
$db_name    = 'your_database_name';
$db_user    = 'your_db_username';
$db_pass    = 'your_db_password';
$db_charset = 'utf8mb4';




try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=$db_charset";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->exec("SET time_zone = '+08:00'");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("DB連線失敗: " . $e->getMessage());
    header('HTTP/1.1 503 Service Unavailable');
    die("網站暫時無法連線資料庫，請稍後再試。");
}

// 郵件設定
$mail_host       = 'smtp.gmail.com';
$mail_port       = 587;
$mail_username   = 'your.email@gmail.com';
$mail_password   = 'your_app_password_here';
$mail_secure     = 'tls';
$mail_from_email = 'your.email@gmail.com';
$mail_from_name  = '地下偶像入口網站';
$mail_to_email   = 'admin@example.com';
$mail_to_name    = '管理員';