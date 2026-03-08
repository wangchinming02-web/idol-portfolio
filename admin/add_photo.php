<?php
$db_config_path = __DIR__ . '/../includes/db_config.php';
$message = "";

$upload_dir = 'uploads/members/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

/// 處理上傳
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_id'])) {
    $member_id = (int)$_POST['member_id'];
    $title = trim($_POST['title'] ?? '');

    if ($member_id <= 0 || empty($_FILES['photos']['name'][0])) {
        $message = "<div class='alert alert-danger'>請選擇成員並上傳至少一張照片</div>";
    } else {
        try {
            $photos = $_FILES['photos'];
            $count = count($photos['name']);
            $success_count = 0;

            // 先查該成員目前最大的 sort_order（沒有照片則為 0）
            $max_stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) AS max_sort FROM member_photos WHERE member_id = ?");
            $max_stmt->execute([$member_id]);
            $max_sort = $max_stmt->fetchColumn();

            // 取得是否有主要照片（改用 prepare 防 SQL injection）
            $has_stmt = $pdo->prepare("SELECT COUNT(*) FROM member_photos WHERE member_id = ? AND is_primary = 1");
            $has_stmt->execute([$member_id]);
            $has_primary = $has_stmt->fetchColumn();

            for ($i = 0; $i < $count; $i++) {
                if ($photos['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $photos['tmp_name'][$i];
                    $ext = strtolower(pathinfo($photos['name'][$i], PATHINFO_EXTENSION));

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $new_name = uniqid('photo_') . '.' . $ext;
                        $target_path = $upload_dir . $new_name;

                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $image_url = $upload_dir . $new_name;

                            $is_primary = ($has_primary == 0 && $success_count == 0) ? 1 : 0;

                            // 從目前最大值開始，每次 +1
                            $sort_order = $max_sort + ($success_count + 1);

                            $sql = "INSERT INTO member_photos 
                                    (member_id, image_url, title, sort_order, is_primary, photo_type) 
                                    VALUES (?, ?, ?, ?, ?, 'profile')";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$member_id, $image_url, $title ?: "照片 " . ($success_count + 1), $sort_order, $is_primary]);
                            $success_count++;
                        }
                    }
                }
            }

            if ($success_count > 0) {
                $message = "<div class='alert alert-success'>成功新增 $success_count 張照片！</div>";
            } else {
                $message = "<div class='alert alert-danger'>上傳失敗，請檢查檔案格式或權限</div>";
            }
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>錯誤：" . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// 取得已選成員 ID（用來預設顯示）
$selected_member_id = (int)($_POST['member_id'] ?? 0);
$photos = [];
if ($selected_member_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM member_photos WHERE member_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$selected_member_id]);
    $photos = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>補充成員照片</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .photo-preview { max-width: 120px; height: auto; border-radius: 6px; }
        .table th, .table td { vertical-align: middle; }
        .alert { margin-bottom: 1.5rem; }
        .ts-wrapper { min-width: 300px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">為已存在成員補充照片</h2>

        <?php if ($message): ?>
            <?= $message ?>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">選擇成員 <span class="text-danger">*</span></label>
                <select name="member_id" id="member_select" required>
                    <!-- 選項由 Tom Select + AJAX 動態載入 -->
                </select>
            </div>

            <?php if ($selected_member_id > 0): ?>
                <div class="mb-3">
                    <label class="form-label">上傳照片（可一次選多張）</label>
                    <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                    <small class="form-text text-muted">支援 jpg / png / gif，第一張會自動設為主要照片（若還沒有）</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">照片標題（選填，所有照片共用）</label>
                    <input type="text" name="title" class="form-control" placeholder="例：2024 演唱會側拍">
                </div>

                <button type="submit" class="btn btn-primary">上傳照片</button>
            <?php endif; ?>
        </form>

        <!-- 顯示現有照片（原樣） -->
        <?php if ($selected_member_id > 0): ?>
            <h4 class="mt-5">目前照片（<?= count($photos) ?> 張）</h4>
            <?php if (empty($photos)): ?>
                <p class="text-muted">該成員還沒有照片</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>預覽</th>
                                <th>標題</th>
                                <th>說明</th>
                                <th>排序</th>
                                <th>主要照片</th>
                                <th>類型</th>
                                <th>上傳時間</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($photos as $photo): ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars($photo['image_url']) ?>" class="photo-preview" alt="照片">
                                    </td>
                                    <td><?= htmlspecialchars($photo['title'] ?: '無標題') ?></td>
                                    <td><?= htmlspecialchars(substr($photo['description'] ?? '', 0, 50)) . (strlen($photo['description'] ?? '') > 50 ? '...' : '') ?></td>
                                    <td><?= $photo['sort_order'] ?></td>
                                    <td>
                                        <?php if ($photo['is_primary']): ?>
                                            <span class="badge bg-success">主要</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">一般</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($photo['photo_type']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($photo['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="mt-4">
            <a href="add_member.php" class="btn btn-secondary">回到新增成員</a>
            <a href="add_.php" class="btn btn-secondary">回到主選單</a>
        </div>
    </div>

    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = new TomSelect('#member_select', {
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            create: false,
            maxOptions: 30,
            preload: false,
            loadThrottle: 300,
            load: function(query, callback) {
                if (!query.length) return callback();

                fetch(`search_members.php?q=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (!response.ok) throw new Error('網路錯誤');
                        return response.json();
                    })
                    .then(json => {
                        callback(json);
                    })
                    .catch(() => {
                        callback();
                    });
            },
            placeholder: '輸入成員藝名搜尋...',
            noResultsText: '沒有符合的成員',
            loadingText: '搜尋中...',
            shouldLoad: function(query) {
                return query.length >= 1;
            }
        });

        // 處理頁面重新載入後的預設值（已選成員）
        const selectedId = <?= json_encode($selected_member_id) ?>;
        if (selectedId > 0) {
            // 先加一個臨時 option 避免空白
            select.addOption({
                value: selectedId.toString(),
                text: '載入中...'
            });
            select.setValue(selectedId.toString());

            // 從後端抓正確名稱（可選，但建議）
            fetch(`search_members.php?q=id:${selectedId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        select.updateOption(selectedId.toString(), data[0]);
                        select.refreshOptions(false);
                    }
                })
                .catch(() => {});
        }

        // 當選擇改變時，自動提交表單（模仿原 onchange）
        select.on('change', function() {
            if (this.getValue()) {
                document.querySelector('form').submit();
            }
        });
    });
    </script>
</body>
</html>