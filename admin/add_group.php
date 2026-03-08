<?php
// 強制顯示所有 PHP 錯誤（開發階段用，上線可註解掉）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 用絕對路徑 include，避免相對路徑問題
$db_config_path = __DIR__ . '/../includes/db_config.php';
if (file_exists($db_config_path)) {
    include $db_config_path;
} else {
    die("<p style='color:red;'>找不到 db_config.php！目前路徑：" . __DIR__ . "</p>");
}

// 檢查 PDO 是否正常
if (!isset($pdo) || !$pdo instanceof PDO) {
    die("<p style='color:red;'>db_config.php 載入失敗，$pdo 不是 PDO 物件</p>");
}

$message = "";
$debug_output = "";  // 用來顯示 debug 資訊

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug_output .= "<p style='color:blue; font-weight:bold;'>✅ 已進入 POST 處理區塊</p>";

    $company_id   = !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null;
    $name         = trim($_POST['name'] ?? '');
    $debut_date   = !empty($_POST['debut_date']) ? $_POST['debut_date'] : null;
    $status       = in_array($_POST['status'] ?? 'active', ['active', 'hiatus', 'disbanded']) ? $_POST['status'] : 'active';
    $image_path   = trim($_POST['image_path'] ?? '');

    $debug_output .= "<pre>接收到的 POST 資料:\n" . print_r($_POST, true) . "</pre>";

    if (empty($name)) {
        $message = "<p style='color:red;'>❌ 團體名稱為必填</p>";
    } else {
        try {
            $sql = "INSERT INTO groups (company_id, name, debut_date, status, image_path) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$company_id, $name, $debut_date, $status, $image_path]);

            $debug_output .= "<pre>INSERT 執行結果:\n成功: " . ($success ? '是' : '否') . "\n影響行數: " . $stmt->rowCount() . "</pre>";

            if ($success) {
                $message = "<p style='color:green;'>✅ 成功新增團體：$name</p>";
            } else {
                $message = "<p style='color:orange;'>⚠️ 執行成功但未新增資料（影響行數 0）</p>";
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>❌ 資料庫錯誤：" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
} else {
    $debug_output .= "<p style='color:orange;'>本次請求為 " . ($_SERVER['REQUEST_METHOD'] ?? '未知') . "，非 POST</p>";
}

// 讀取公司列表
try {
    $companies = $pdo->query("SELECT id, name FROM companies ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $companies = [];
    $message .= "<p style='color:red;'>無法讀取公司列表：" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>新增團體</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 600px; margin: auto; }
        .form-group { margin: 15px 0; }
        small { color: #666; font-size: 0.9em; }
        pre { background: #f8f8f8; padding: 10px; border: 1px solid #ddd; overflow: auto; font-size: 0.9em; }
        .debug-box { border: 2px solid #ccc; padding: 10px; background: #fff; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>2. 新增偶像團體</h2>

    <!-- Debug 區塊（開發時保留，上線可註解） -->
    <div class="debug-box">
        <strong>Debug 資訊：</strong><br>
        <?= $debug_output ?>
    </div>

    <?= $message ?>

    <form method="POST" action="" novalidate autocomplete="off">
        <div class="form-group">
            <label>所屬公司</label>
            <select name="company_id">
                <option value="">-- 自營 (無公司) --</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>團體名稱 <span style="color:red;">*</span></label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>出道日期</label>
            <input type="date" name="debut_date" value="<?= htmlspecialchars($_POST['debut_date'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Logo 相對路徑（選填）</label>
            <input type="text" name="image_path" placeholder="例：groups/2026/02/hanasaki.jpg" value="<?= htmlspecialchars($_POST['image_path'] ?? '') ?>">
            <small>請填入相對路徑（例如 groups/年/月/檔名.jpg），系統會用於團體頁面顯示</small>
        </div>
        <div class="form-group">
            <label>團體狀態</label>
            <select name="status">
                <option value="active" selected>現役 / Active</option>
                <option value="hiatus">活動休止 / Hiatus</option>
                <option value="disbanded">已解散 / Disbanded</option>
            </select>
        </div>
        <button type="submit">送出</button>
    </form>

    <br><a href="add_member.php">下一步：新增成員 →</a>
    <br><a href="add_.php">← 回到主選單</a>
</body>
</html>