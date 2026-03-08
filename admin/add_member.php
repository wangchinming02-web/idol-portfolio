<?php
// ==============================================
// 開發用：強制顯示所有錯誤
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==============================================
// 臨時內嵌資料庫連線（先取代 db_config.php，讓你看到頁面）
// 上線後再移回 db_config.php
$host   = 'localhost';
$dbname = 'idol_portal';   // ← 確認這是你的真實資料庫名稱！
$user   = 'root';
$pass   = '';              // XAMPP 預設空密碼

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    // 測試連線
    $pdo->query("SELECT 1");
    // echo "<!-- PDO 連線成功 -->"; // 開發時可開啟
} catch (PDOException $e) {
    die("<h2 style='color:red; text-align:center;'>資料庫連線失敗！<br>" 
        . htmlspecialchars($e->getMessage()) 
        . "<br><small>請檢查：資料庫名稱、帳號、密碼、MySQL 是否啟動</small></h2>");
}

// ==============================================
// 以下是你原本的程式碼，從這裡開始不變
session_start();

$message = "";
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$upload_dir = 'uploads/members/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id         = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
    $stage_name       = trim($_POST['stage_name'] ?? '');
    $member_color     = trim($_POST['member_color'] ?? '#FFFFFF');
    $birth_date       = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $instagram_handle = trim($_POST['instagram_handle'] ?? '');
    $is_former        = isset($_POST['is_former']) && $_POST['is_former'] === '1' ? 1 : 0;
    $join_date        = !empty($_POST['join_date']) ? $_POST['join_date'] : date('Y-m-d');
    $leave_date       = $is_former ? (!empty($_POST['leave_date']) ? $_POST['leave_date'] : null) : null;

    $form_data = [
        'group_id'         => $group_id,
        'stage_name'       => $stage_name,
        'member_color'     => $member_color,
        'birth_date'       => $birth_date,
        'instagram_handle' => $instagram_handle,
        'is_former'        => $is_former,
        'join_date'        => $join_date,
        'leave_date'       => $leave_date,
    ];

    if (empty($stage_name)) {
        $message = "<p style='color:red;'>❌ 藝名/名字 為必填欄位</p>";
    } elseif (empty($join_date)) {
        $message = "<p style='color:red;'>❌ 加入日期為必填欄位</p>";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. 插入 members
            $sql_member = "INSERT INTO members 
                           (group_id, stage_name, member_color, birth_date, instagram_handle, is_former) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_member);
            $stmt->execute([$group_id, $stage_name, $member_color, $birth_date, $instagram_handle, $is_former]);
            $member_id = $pdo->lastInsertId();

            // 2. 插入 member_aliases
            $sql_alias = "INSERT INTO member_aliases 
                          (member_id, stage_name, group_id, is_primary, start_date) 
                          VALUES (?, ?, ?, 1, ?)";
            $stmt_alias = $pdo->prepare($sql_alias);
            $stmt_alias->execute([$member_id, $stage_name, $group_id, $join_date]);

            // 3. 插入 member_group_history
            $sql_history = "INSERT INTO member_group_history 
                            (member_id, group_id, join_date, leave_date, is_former) 
                            VALUES (?, ?, ?, ?, ?)";
            $stmt_history = $pdo->prepare($sql_history);
            $stmt_history->execute([$member_id, $group_id, $join_date, $leave_date, $is_former]);

            // 4. 多張照片上傳
            $success_photos = 0;
            if (!empty($_FILES['photos']['name'][0])) {
                $photos = $_FILES['photos'];
                $photo_count = count($photos['name']);

                for ($i = 0; $i < $photo_count; $i++) {
                    if ($photos['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $photos['tmp_name'][$i];
                        $ext = strtolower(pathinfo($photos['name'][$i], PATHINFO_EXTENSION));

                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $new_name = uniqid('photo_') . '.' . $ext;
                            $target_path = $upload_dir . $new_name;

                            if (move_uploaded_file($tmp_name, $target_path)) {
                                $image_url = $upload_dir . $new_name;

                                $is_primary = ($i === 0) ? 1 : 0;
                                $sql_photo = "INSERT INTO member_photos 
                                              (member_id, image_url, title, sort_order, is_primary, photo_type) 
                                              VALUES (?, ?, ?, ?, ?, 'profile')";
                                $stmt_photo = $pdo->prepare($sql_photo);
                                $stmt_photo->execute([$member_id, $image_url, "照片 " . ($i + 1), 10 + $i * 5, $is_primary]);
                                $success_photos++;
                            }
                        }
                    }
                }
            }

            $pdo->commit();

            // PRG 重定向
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&name=" . urlencode($stage_name) . "&status=" . $is_former);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "<p style='color:red;'>❌ 資料庫錯誤：" . htmlspecialchars($e->getMessage()) . "</p>";
            $_SESSION['form_data'] = $form_data;
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<p style='color:red;'>❌ 上傳錯誤：" . htmlspecialchars($e->getMessage()) . "</p>";
            $_SESSION['form_data'] = $form_data;
        }
    }
}

// 成功訊息
if (isset($_GET['success'])) {
    $added_name = urldecode($_GET['name'] ?? '');
    $is_former  = $_GET['status'] ?? 0;
    $status_text = $is_former ? "（前成員 / 已畢業）" : "（現役）";
    $message = "<p style='color:green;'>✅ 成功新增藝人： " . htmlspecialchars($added_name) . $status_text . "</p>";
}

// 取得團體列表（現在 $pdo 應該正常）
$groups = $pdo->query("
    SELECT id, name, status 
    FROM groups 
    ORDER BY 
        CASE status 
            WHEN 'active' THEN 1 
            WHEN 'hiatus' THEN 2 
            WHEN 'disbanded' THEN 3 
        END, 
        name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增成員 / Solo 藝人</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            padding: 24px;
            max-width: 560px;
            margin: auto;
            line-height: 1.6;
            background: #f8f9fa;
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
        }

        input[type="text"],
        input[type="date"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .color-flex {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        button {
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 6px;
        }

        #submitBtn {
            background: #0d6efd;
            width: 48%;
        }

        #clearBtn {
            background: #6c757d;
            width: 48%;
        }

        .color-preview {
            width: 44px;
            height: 44px;
            border: 1px solid #aaa;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .disbanded {
            color: #d32f2f;
            font-size: 0.9em;
        }

        .hiatus {
            color: #f57c00;
            font-size: 0.9em;
        }

        small {
            color: #666;
            font-size: 0.9em;
        }

        .status-note {
            font-size: 0.95em;
            color: #555;
            margin-top: 4px;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>3. 新增成員 / 個人藝人資料</h2>

    <?php if ($message): ?>
        <div class="<?= strpos($message, '成功') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="memberForm" enctype="multipart/form-data">
        <div class="form-group">
            <label>所屬團體（含已解散團體）</label>
            <select name="group_id">
                <option value="">-- 個人 / Solo（無團體） --</option>
                <?php foreach ($groups as $g): ?>
                    <?php
                    $suffix = '';
                    if ($g['status'] === 'disbanded') $suffix = ' <span class="disbanded">(已解散)</span>';
                    elseif ($g['status'] === 'hiatus') $suffix = ' <span class="hiatus">(活動休止)</span>';
                    $selected = (isset($form_data['group_id']) && $form_data['group_id'] == $g['id']) ? 'selected' : '';
                    ?>
                    <option value="<?= $g['id'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($g['name']) . $suffix ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>狀態</label>
            <select name="is_former" id="isFormer">
                <option value="0" <?= (isset($form_data['is_former']) && $form_data['is_former'] == 0) ? 'selected' : '' ?>>現役 / 所屬中</option>
                <option value="1" <?= (isset($form_data['is_former']) && $form_data['is_former'] == 1) ? 'selected' : '' ?>>前成員 / 已畢業 / 畢業生</option>
            </select>
            <div class="status-note">
                ※ 如果該成員已經離開團體，請選擇「前成員」
            </div>
        </div>

        <div class="form-group">
            <label>加入日期 <span style="color:red;">*</span></label>
            <input type="date" name="join_date" value="<?= htmlspecialchars($form_data['join_date'] ?? date('Y-m-d')) ?>" required>
        </div>

        <div class="form-group">
            <label>離開日期（僅前成員填寫）</label>
            <input type="date" name="leave_date" id="leaveDate"
                value="<?= htmlspecialchars($form_data['leave_date'] ?? '') ?>"
                <?= (isset($form_data['is_former']) && $form_data['is_former'] == 1) ? '' : 'disabled' ?>>
        </div>

        <div class="form-group">
            <label>藝名 / 名字 <span style="color:red;">*</span></label>
            <input type="text" name="stage_name" required
                value="<?= htmlspecialchars($form_data['stage_name'] ?? '') ?>"
                placeholder="例：小美、Mika">
        </div>

        <div class="form-group">
            <label>代表色</label>
            <div class="color-flex">
                <input type="color" id="picker" value="<?= htmlspecialchars($form_data['member_color'] ?? '#FFFFFF') ?>">
                <input type="text" name="member_color" id="hex"
                    value="<?= htmlspecialchars($form_data['member_color'] ?? '#FFFFFF') ?>"
                    maxlength="7" style="width:150px; font-family:monospace; text-transform:uppercase;"
                    placeholder="#RRGGBB 或 綠色、粉紅">
                <span id="preview" class="color-preview" style="background:<?= htmlspecialchars($form_data['member_color'] ?? '#FFFFFF') ?>;"></span>
            </div>
            <small>可直接輸入中文顏色名稱或 #六位色碼</small>

            <div style="margin-top:12px;">
                <label for="historyColors">歷史顏色</label>
                <select id="historyColors" style="width:200px;">
                    <option value="">— 選擇曾用過的顏色 —</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>生日</label>
            <input type="date" name="birth_date" value="<?= htmlspecialchars($form_data['birth_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Instagram ID</label>
            <input type="text" name="instagram_handle"
                value="<?= htmlspecialchars($form_data['instagram_handle'] ?? '') ?>"
                placeholder="例：taipei_idol_0420">
        </div>

        <div class="form-group">
            <label>成員照片（可一次選多張，支援 jpg/png/gif）</label>
            <input type="file" name="photos[]" accept="image/*" multiple>
            <small>第一張會自動設為主要照片，可之後在後台調整</small>
        </div>

        <div style="margin-top:24px; display:flex; gap:16px; justify-content:center;">
            <button type="submit" id="submitBtn">確認新增</button>
            <button type="button" id="clearBtn" onclick="clearForm()">清空表單</button>
        </div>

        <div style="margin-top:20px; text-align:center;">
            <a href="add_.php" style="color:#0d6efd; text-decoration:none;">← 回到主選單</a>
        </div>
    </form>

    <script>
        // 狀態改變時控制離開日期
        document.getElementById('isFormer').addEventListener('change', function() {
            document.getElementById('leaveDate').disabled = (this.value === '0');
            if (this.value === '0') document.getElementById('leaveDate').value = '';
        });

        // 防連按送出
        document.getElementById('memberForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = '新增中...';
        });

        // 顏色選擇器（完整）
        const picker = document.getElementById('picker');
        const hexInput = document.getElementById('hex');
        const preview = document.getElementById('preview');
        const historySelect = document.getElementById('historyColors');

        const chineseColorMap = {
            // 基本色
            '白色': '#FFFFFF',
            '黑色': '#000000',
            '灰色': '#808080',
            '銀色': '#C0C0C0',

            // 紅色系
            '紅色': '#FF0000',
            '正紅': '#FF0000',
            '緋紅': '#DC143C',
            '酒紅': '#800020',
            '櫻花紅': '#FFB6C1',
            '桃紅': '#FF69B4',
            '粉紅': '#FFC0CB',
            '粉色': '#FFC0CB',
            '珊瑚紅': '#FF7F50',

            // 橙色/黃色系
            '橘色': '#FFA500',
            '橙色': '#FFA500',
            '橘紅': '#FF4500',
            '黃色': '#FFFF00',
            '金色': '#FFD700',
            '檸檬黃': '#FFFACD',
            '香蕉黃': '#FFE135',

            // 綠色系
            '綠色': '#008000',
            '正綠': '#008000',
            '草綠': '#32CD32',
            '蘋果綠': '#8FBC8F',
            '薄荷綠': '#98FB98',
            '森林綠': '#228B22',
            '翠綠': '#00FF7F',

            // 藍色系
            '藍色': '#0000FF',
            '深藍': '#00008B',
            '寶藍': '#002366',
            '天空藍': '#87CEEB',
            '湖水藍': '#00BFFF',
            '靛藍': '#4B0082',
            '海軍藍': '#000080',

            // 紫色系
            '紫色': '#800080',
            '薰衣草': '#E6E6FA',
            '紫羅蘭': '#EE82EE',
            '梅子紫': '#DDA0DD',
            '葡萄紫': '#9370DB',

            // 棕色/咖啡色系
            '棕色': '#A52A2A',
            '咖啡色': '#6F4E37',
            '巧克力色': '#D2691E',
            '駝色': '#D2B48C',

            // 其他常見（偶像圈常用）
            '水藍': '#00FFFF',
            '青色': '#00FFFF',
            '青鳥藍': '#00CED1',
            '象牙白': '#FFFFF0',
            '奶茶色': '#D2B48C',
            '玫瑰金': '#B76E79',
            '香檳金': '#F7E7CE',
            '霧面黑': '#2F2F2F',
            '螢光粉': '#FF1493',
            '螢光綠': '#39FF14'
            // 你可以繼續加，例如你家推的代表色什麼的
        };

        function updateColor(color) {
            if (!/^#[0-9A-F]{6}$/i.test(color)) return;
            picker.value = color;
            hexInput.value = color.toUpperCase();
            preview.style.backgroundColor = color;
            addToHistory(color);
        }

        function addToHistory(color) {
            let history = JSON.parse(localStorage.getItem('idolColorHistory') || '[]');
            history = history.filter(c => c !== color);
            history.unshift(color);
            history = history.slice(0, 12);
            localStorage.setItem('idolColorHistory', JSON.stringify(history));
            renderHistory();
        }

        function renderHistory() {
            historySelect.innerHTML = '<option value="">— 選擇曾用過的顏色 —</option>';
            const history = JSON.parse(localStorage.getItem('idolColorHistory') || '[]');
            history.forEach(color => {
                const opt = document.createElement('option');
                opt.value = color;
                opt.textContent = color;
                opt.style.backgroundColor = color;
                opt.style.color = (parseInt(color.slice(1), 16) > 0x888888) ? '#000' : '#fff';
                historySelect.appendChild(opt);
            });
        }

        picker.addEventListener('input', () => updateColor(picker.value));

        hexInput.addEventListener('input', () => {
            let val = hexInput.value.trim().toUpperCase();
            preview.style.backgroundColor = val;
            if (!val.startsWith('#')) {
                const found = chineseColorMap[val] || chineseColorMap[val.toLowerCase()];
                if (found) updateColor(found);
            } else if (/^#[0-9A-F]{6}$/i.test(val)) {
                updateColor(val);
            }
        });

        hexInput.addEventListener('blur', () => {
            let val = hexInput.value.trim().toUpperCase();
            if (val === '') return updateColor('#FFFFFF');
            if (!val.startsWith('#')) {
                const found = chineseColorMap[val] || chineseColorMap[val.toLowerCase()];
                if (found) return updateColor(found);
            }
            if (/^#[0-9A-F]{3}$/i.test(val)) {
                val = '#' + val[1].repeat(2) + val[2].repeat(2) + val[3].repeat(2);
            }
            if (/^#[0-9A-F]{6}$/i.test(val)) {
                updateColor(val);
            } else {
                hexInput.value = picker.value;
                preview.style.backgroundColor = picker.value;
            }
        });

        historySelect.addEventListener('change', () => {
            if (historySelect.value) updateColor(historySelect.value);
        });

        window.addEventListener('load', renderHistory);

        function clearForm() {
            document.querySelectorAll('input[type="text"], input[type="date"], input[type="color"], input[type="file"]').forEach(el => el.value = '');
            document.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            document.getElementById('preview').style.backgroundColor = '#FFFFFF';
            document.getElementById('leaveDate').disabled = true;
            document.getElementById('isFormer').value = '0';
        }
    </script>
</body>

</html>