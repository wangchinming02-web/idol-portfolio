<?php
$db_config_path = __DIR__ . '/../includes/db_config.php';
session_start();

$messages = [];           // 所有處理訊息（成功/失敗）
$success_count = 0;
$error_count = 0;

$upload_base = 'uploads/groups/';
$ym = date('Y/m/');
$upload_dir = $upload_base . $ym;

if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        $messages[] = "錯誤：無法建立資料夾 {$upload_dir}，請檢查伺服器權限";
        // 可以直接 return 或繼續，視需求
    }
}

$max_size = 5 * 1024 * 1024; // 5MB

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['logos']['name'][0])) {
    $files = $_FILES['logos'];
    $admin_id = $_SESSION['admin_id'] ?? 1;

    foreach ($files['name'] as $i => $orig_name) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $messages[] = "{$orig_name} → 上傳錯誤 (代碼: {$files['error'][$i]})";
            $error_count++;
            continue;
        }

        if ($files['size'][$i] > $max_size) {
            $messages[] = "{$orig_name} → 檔案太大 (>5MB)";
            $error_count++;
            continue;
        }

        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $messages[] = "{$orig_name} → 不支援的副檔名";
            $error_count++;
            continue;
        }

        // 提取關鍵字（去除常見後綴）
        $keyword = pathinfo($orig_name, PATHINFO_FILENAME);
        $keyword = preg_replace('/[_-](logo|Logo|LOGO|官方|Official|新版|202[0-9]{2}|v\d)$/i', '', $keyword);
        $keyword = trim($keyword);

        if (empty($keyword)) {
            $messages[] = "{$orig_name} → 檔名無法解析關鍵字";
            $error_count++;
            continue;
        }

        // 產生乾淨關鍵字（去除所有可能干擾的符號）
        $clean_keyword = str_replace(
            [':', '/', '・', '（', '）', '【', '】', '(', ')', '[', ']', ' ', '　', '-', '_', '・', '。', '◇', '→', '&'],
            '',
            $keyword
        );
        $clean_keyword = trim($clean_keyword);

        // 優先使用 match_keyword 精準匹配
        $stmt = $pdo->prepare("
            SELECT id, name 
            FROM groups 
            WHERE match_keyword = ?                    -- 精準匹配乾淨版（最優先）
               OR match_keyword LIKE ?                 -- 模糊乾淨版
               OR logo_keyword = ?                     -- 再查 logo_keyword
               OR logo_keyword LIKE ? 
               OR name LIKE ?                          -- 最後用原始 name
            LIMIT 1
        ");
        $stmt->execute([
            $clean_keyword, 
            "%{$clean_keyword}%", 
            $keyword, 
            "%{$keyword}%", 
            "%{$keyword}%"
        ]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            $messages[] = "{$orig_name} → 找不到對應團體（關鍵字：{$keyword} / 乾淨版：{$clean_keyword}）";
            $messages[] = "　　建議：檢查 groups 表 match_keyword 是否正確填寫（應為無符號全名）";
            $error_count++;
            continue;
        }

        $group_id = $group['id'];
        $group_name = $group['name'];

        // 加強檔名過濾
        $temp_name = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|', "\0"], '_', $orig_name);
        $safe_filename = preg_replace('/[^一-龥a-zA-Z0-9._-]/u', '_', $temp_name);
        $safe_filename = trim($safe_filename, '_.- ');
        if (empty($safe_filename) || strlen($safe_filename) < 5) {
            $safe_filename = uniqid('group_logo_') . '.' . $ext;
        }

        $server_path = $upload_dir . $safe_filename;
        $db_path = $ym . $safe_filename;

        // 避免檔名重複
        $counter = 1;
        $original_safe = $safe_filename;
        while (file_exists($server_path)) {
            $safe_filename = pathinfo($original_safe, PATHINFO_FILENAME) . '_' . $counter . '.' . $ext;
            $server_path = $upload_dir . $safe_filename;
            $db_path = $ym . $safe_filename;
            $counter++;
        }

        if (move_uploaded_file($files['tmp_name'][$i], $server_path)) {
            $pdo->beginTransaction();
            try {
                // 關舊 current
                $pdo->prepare("UPDATE group_logos SET is_current = 0 WHERE group_id = ? AND is_current = 1")
                    ->execute([$group_id]);

                // 插入新 Logo
                $pdo->prepare("
                    INSERT INTO group_logos 
                    (group_id, current_logo_path, changed_at, changed_by, is_current, notes)
                    VALUES (?, ?, NOW(), ?, 1, ?)
                ")->execute([
                    $group_id,
                    $db_path,
                    $admin_id,
                    "方式A 自動上傳：原檔名 {$orig_name}"
                ]);

                // 更新 groups.image_path
                $pdo->prepare("UPDATE groups SET image_path = ? WHERE id = ?")
                    ->execute([$db_path, $group_id]);

                $pdo->commit();
                $success_count++;
                $messages[] = "成功：{$group_name} (ID {$group_id}) ← {$orig_name}";
            } catch (Exception $e) {
                $pdo->rollBack();
                $messages[] = "資料庫錯誤：{$orig_name} → {$e->getMessage()}";
                $error_count++;
            }
        } else {
            $messages[] = "失敗：{$orig_name} → 移動檔案失敗（檢查權限或路徑）";
            $messages[] = "　　建議檔名：{$safe_filename}";
            $messages[] = "　　團體：{$group_name} (ID {$group_id})";
            $error_count++;
        }
    }
}

// 最後可以自己處理 $messages，例如寫 log 或 echo
// 這裡只 echo 純文字結果
echo "處理完成\n";
echo "成功：{$success_count} 張\n";
echo "失敗：{$error_count} 張\n";
foreach ($messages as $msg) {
    echo $msg . "\n";
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>方式 A：批量自動上傳團體 Logo</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 900px; margin: auto; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        ul { list-style: none; padding: 0; }
        li { margin: 10px 0; padding: 8px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
<h2>方式 A：檔名自動配對上傳（零確認）</h2>
<p>檔名需包含團體關鍵字，例如：哈比人.jpg、紅綠燈_logo.png</p>
<p>系統會自動配對、寫入 group_logos，並更新 groups.image_path</p>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="logos[]" multiple accept="image/*">
    <br><br>
    <button type="submit">開始上傳並自動儲存</button>
</form>

<?php if (!empty($messages)): ?>
    <h3>處理結果（成功 <?php echo $success_count; ?> 張 / 失敗 <?php echo $error_count; ?> 張）</h3>
    <ul>
        <?php foreach ($messages as $msg): ?>
            <li><?php echo $msg; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>