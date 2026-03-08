<?php
// 強制顯示所有 PHP 錯誤（開發時必開）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 用絕對路徑 include，避免相對路徑失效
$db_config_path = __DIR__ . '/../includes/db_config.php';
if (file_exists($db_config_path)) {
    include $db_config_path;
} else {
    die("<p style='color:red;'>❌ 找不到 db_config.php！目前路徑：" . __DIR__ . "</p>");
}

// 檢查 PDO 是否成功載入
if (!isset($pdo) || !$pdo instanceof PDO) {
    die("<p style='color:red;'>❌ db_config.php 載入後 $pdo 不是 PDO 物件，請檢查 db_config.php 內容</p>");
}

$message = "";
$debug_output = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug_output .= "<p style='color:blue;'>✅ 已進入 POST 處理區塊</p>";
    
    $name       = trim($_POST['name'] ?? '');
    $logo_path  = trim($_POST['logo_path'] ?? '');

    $debug_output .= "<pre>接收資料:\nname: '$name'\nlogo_path: '$logo_path'</pre>";

    if (empty($name)) {
        $message = "<p style='color:red;'>❌ 公司名稱為必填</p>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO companies (name, logo_path) VALUES (?, ?)");
            $success = $stmt->execute([$name, $logo_path]);
            
            $debug_output .= "<pre>INSERT 結果:\n成功: " . ($success ? '是' : '否') . "\n影響行數: " . $stmt->rowCount() . "</pre>";
            
            if ($success) {
                $message = "<p style='color:green;'>✅ 成功新增公司：$name</p>";
            } else {
                $message = "<p style='color:orange;'>⚠️ 執行成功但沒新增任何資料</p>";
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>❌ 資料庫錯誤：" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
} else {
    $debug_output .= "<p style='color:orange;'>這次請求是 " . ($_SERVER['REQUEST_METHOD'] ?? '未知') . "，不是 POST</p>";
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>新增公司 - 最終修正版</title>
    <style>
        body { font-family:sans-serif; padding:20px; max-width:600px; margin:auto; }
        .form-group { margin:15px 0; }
        pre { background:#f8f8f8; padding:10px; border:1px solid #ddd; overflow:auto; font-size:0.9em; }
        small { color:#666; }
    </style>
</head>
<body>
    <h2>1. 新增營運公司（最終修正版）</h2>

    <!-- Debug 輸出區塊 -->
    <div style="border:2px solid #ccc; padding:10px; background:#fff; margin-bottom:20px;">
        <strong>Debug 資訊：</strong><br>
        <?= $debug_output ?>
    </div>

    <?= $message ?>

    <form method="POST" action="" novalidate autocomplete="off">
        <div class="form-group">
            <label>公司名稱 <span style="color:red;">*</span></label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Logo 相對路徑（選填）</label>
            <input type="text" name="logo_path" value="<?= htmlspecialchars($_POST['logo_path'] ?? '') ?>">
            <small>例：companies/2026/02/company_abc123.png</small>
        </div>
        <button type="submit">送出</button>
    </form>

    <br><a href="add_group.php">下一步：新增團體 →</a>
    <br><a href="add_.php">← 回到主選單</a>
</body>
</html>