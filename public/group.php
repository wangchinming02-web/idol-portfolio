<?php
// 開啟錯誤顯示（開發階段用，上線請移除或設為 0）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db_config_path = __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/../includes/db_config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("無效的團體 ID，請在網址加上 ?id=數字");
}

$group_id = (int)$_GET['id'];

try {
    // 團體基本資料（移除 stage_color）
    $stmt = $pdo->prepare("
        SELECT 
            g.id, g.name, g.debut_date, g.status, g.image_path,
            c.name AS company_name
        FROM groups g
        LEFT JOIN companies c ON g.company_id = c.id
        WHERE g.id = :gid
    ");
    $stmt->execute([':gid' => $group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        die("找不到該團體");
    }

    // 【現役成員】
    $stmt_active = $pdo->prepare("
        SELECT m.id, m.stage_name, m.member_color, h.position, mp.image_url AS profile_photo
        FROM members m
        INNER JOIN member_group_history h ON m.id = h.member_id
        LEFT JOIN member_photos mp ON m.id = mp.member_id AND mp.is_primary = 1 AND mp.photo_type = 'profile'
        WHERE h.group_id = :gid
          AND (h.leave_date IS NULL OR h.leave_date = '0000-00-00')
          AND m.is_former = 0
        ORDER BY h.position ASC, m.stage_name ASC
    ");
    $stmt_active->execute([':gid' => $group_id]);
    $active_members = $stmt_active->fetchAll(PDO::FETCH_ASSOC);

    // 【卒業成員】
    $stmt_graduated = $pdo->prepare("
        SELECT m.id, m.stage_name, m.member_color, h.position, mp.image_url AS profile_photo, h.leave_date
        FROM members m
        INNER JOIN member_group_history h ON m.id = h.member_id
        LEFT JOIN member_photos mp ON m.id = mp.member_id AND mp.is_primary = 1 AND mp.photo_type = 'profile'
        WHERE h.group_id = :gid
          AND (m.is_former = 1 OR (h.leave_date IS NOT NULL AND h.leave_date != '0000-00-00'))
        ORDER BY h.leave_date DESC, m.stage_name ASC
    ");
    $stmt_graduated->execute([':gid' => $group_id]);
    $graduated_members = $stmt_graduated->fetchAll(PDO::FETCH_ASSOC);
    // 1. 抓取所有紀錄
$stmt_history = $pdo->prepare("
    SELECT m.stage_name, h.join_date, h.leave_date, h.position
    FROM member_group_history h
    JOIN members m ON h.member_id = m.id
    WHERE h.group_id = :gid
");
$stmt_history->execute([':gid' => $group_id]);
$raw_logs = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

$timeline = [];

// 2. 將加入與離開拆分為獨立事件
foreach ($raw_logs as $log) {
    // 加入事件
    if (!empty($log['join_date']) && $log['join_date'] != '0000-00-00') {
        $timeline[] = [
            'date' => $log['join_date'],
            'type' => 'join',
            'name' => $log['stage_name'],
            'pos'  => $log['position']
        ];
    }
    // 畢業事件
    if (!empty($log['leave_date']) && $log['leave_date'] != '0000-00-00') {
        $timeline[] = [
            'date' => $log['leave_date'],
            'type' => 'leave',
            'name' => $log['stage_name'],
            'pos'  => $log['position']
        ];
    }
}

// 3. 按日期從新到舊排序 (混合所有人的事件)
usort($timeline, function($a, $b) {
    return strcmp($b['date'], $a['date']);
    });
} catch (PDOException $e) {
    die("資料庫錯誤： " . $e->getMessage());
}
$member_count = count($active_members);
?>


<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($group['name'] ?? '團體資料') ?> - 團體介紹</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./sass/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <div id="searchToast" class="toast align-items-center text-white bg-primary border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    💡 <b>請善用 Ctrl + F</b> 進行搜索
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container my-4">

        <!-- 團體基本資訊 -->
        <div class="row mb-4 align-items-center">
            <div class="col-12 col-md-3 text-center">
    <h3 class="fw-bold"><?= htmlspecialchars($group['name']) ?></h3>
    <div class="mt-3">
        <?php 
        // 定義基礎路徑（建議放在 config 或頂層檔案，之後統一改這裡就好）
        $basePath = '/project01/public/uploads/groups/';
        
        // 組合完整圖片路徑
        $logoSrc = !empty($group['image_path'])
            ? $basePath . ltrim($group['image_path'], '/')  // 去掉可能的開頭斜線，避免重複 //
            : '';
        ?>
        
        <?php if ($logoSrc): ?>
            <img src="<?= htmlspecialchars($logoSrc) ?>"
                 alt="<?= htmlspecialchars($group['name']) ?> 團體 Logo"
                 class="img-fluid rounded mx-auto d-block"
                 style="max-width: 140px; max-height: 140px; object-fit: contain; border: 1px solid #e0e0e0; background-color: #f8f9fa;"
                 onerror="this.onerror=null; this.src='/project01/public/img/placeholder-logo.png'; this.alt='Logo 載入失敗';">
            <div class="mt-2">
                <small class="text-muted">團體 Logo</small>
            </div>
        <?php else: ?>
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto text-muted"
                 style="width:120px; height:120px; border: 2px dashed #ccc;">
                <small>無 Logo</small>
            </div>
            <div class="mt-2">
                <small class="text-muted">尚未上傳團體標誌</small>
            </div>
        <?php endif; ?>
    </div>
</div>

            <div class="col-12 col-md-9">
                <div class="row g-2 small text-muted">
                    <div class="col-auto">出道：<span class="text-dark"><?= htmlspecialchars($group['debut_date'] ?: '－') ?></span></div>
                    <div class="col-auto">團員：<span class="text-dark"><?= $member_count ?>人</span></div>
                    <div class="col-auto">經紀公司：<span class="text-dark"><?= htmlspecialchars($group['company_name'] ?: '－') ?></span></div>
                </div>
            </div>
        </div>

        <!-- 成員列表（每個成員秀自己的顏色） -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4 fw-bold">
            <i class="bi bi-person-check-fill text-primary"></i> 現役成員 (<?= $member_count ?>人)
        </h5>
        <div class="row g-4">
            <?php foreach ($active_members as $member): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="text-center">
                        <a href="member_detail.php?id=<?= $member['id'] ?>" class="text-decoration-none">
                            <div class="ratio ratio-1x1 bg-light rounded-pill border mx-auto mb-2"
                                style="width:120px; position:relative; overflow:visible;">
                                
                                <div class="rounded-pill overflow-hidden h-100 w-100">
                                    <?php if (!empty($member['profile_photo'])): ?>
                                        <img src="<?= htmlspecialchars($member['profile_photo']) ?>" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">No Photo</div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($member['member_color'])): ?>
                                    <div class="position-absolute"
                                        style="width:26px; height:26px; background-color: <?= htmlspecialchars($member['member_color']) ?>; 
                                        border: 2px solid #000; border-radius: 50%; bottom: 5px; right: 5px; 
                                        box-shadow: 0 0 2px rgba(255,255,255,0.8); z-index: 99;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        
                        <div class="fw-bold text-dark"><?= htmlspecialchars($member['stage_name']) ?></div>
                        <div class="text-muted small mb-2"><?= htmlspecialchars($member['position'] ?: 'Member') ?></div>

                        <?php if (!empty($member['instagram_handle'])): ?>
                            <a href="https://instagram.com/<?= htmlspecialchars($member['instagram_handle']) ?>" target="_blank" class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.7rem;">
                                <i class="bi bi-instagram"></i> IG
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!empty($graduated_members)): ?>
    <div class="card mb-4 shadow-sm border-0 bg-light-subtle">
        <div class="card-body">
            <h5 class="card-title mb-4 fw-bold text-secondary">
                <i class="bi bi-person-x-fill"></i> 卒業 / 前成員 (<?= count($graduated_members) ?>人)
            </h5>
            <div class="row g-4">
                <?php foreach ($graduated_members as $member): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="text-center">
                            <a href="member_detail.php?id=<?= $member['id'] ?>" class="text-decoration-none">
                                <div class="ratio ratio-1x1 bg-secondary-subtle rounded-pill border mx-auto mb-2"
                                    style="width:100px; position:relative; overflow:visible;">

                                    <div class="rounded-pill overflow-hidden h-100 w-100">
                                        <?php if (!empty($member['profile_photo'])): ?>
                                            <img src="<?= htmlspecialchars($member['profile_photo']) ?>" class="w-100 h-100 object-fit-cover">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">No Photo</div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($member['member_color'])): ?>
                                        <div class="position-absolute"
                                            style="width:22px; height:22px; background-color: <?= htmlspecialchars($member['member_color']) ?>; 
                                            border: 2px solid #000; border-radius: 50%; bottom: 3px; right: 3px; 
                                            box-shadow: 0 0 2px rgba(255,255,255,0.8); z-index: 99;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="fw-bold text-muted"><?= htmlspecialchars($member['stage_name']) ?></div>
                            <div class="text-secondary small">卒業日：<?= htmlspecialchars($member['leave_date']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>


        <!-- 其他區塊可繼續往下加 ... -->

        <?php if (!empty($group_logos)): ?>
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-4 fw-bold"><i class="bi bi-image-history text-warning"></i> 團體識別紀錄</h5>
                    <div class="d-flex flex-nowrap overflow-auto gap-3 pb-2">
                        <?php foreach ($group_logos as $logo): ?>
                            <div class="text-center" style="min-width: 150px;">
                                <div class="border rounded p-2 mb-2 bg-white shadow-sm" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                                    <img src="<?= htmlspecialchars($logo['logo_url']) ?>" class="img-fluid" style="max-height: 80px;">
                                </div>
                                <div class="small fw-bold">
                                    <?= $logo['is_current'] ? '<span class="badge bg-success">使用中</span>' : '<span class="badge bg-light text-dark border">過往紀錄</span>' ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    <?= $logo['changed_at'] ? date('Y-m-d', strtotime($logo['changed_at'])) : '日期不詳' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <h5 class="card-title mb-4 fw-bold"><i class="bi bi-calendar3 text-warning"></i> 活動時間軸</h5>
        <div class="timeline-container ps-3 border-start border-2 border-light">
            <?php foreach ($timeline as $event): ?>
                <div class="timeline-item position-relative mb-4 ps-4">
                    <?php if ($event['type'] === 'join'): ?>
                        <div class="position-absolute start-0 top-0 bg-success rounded-circle" style="width:12px; height:12px; margin-left:-7px; border: 2px solid #fff;"></div>
                        <div class="small text-muted"><?= date('Y年m月d日', strtotime($event['date'])) ?></div>
                        <div class="fw-bold text-success">新成員加入</div>
                        <div><strong><?= htmlspecialchars($event['name']) ?></strong> 正式加入團體<?= $event['pos'] ? "，擔當：{$event['pos']}" : "" ?></div>
                    <?php else: ?>
                        <div class="position-absolute start-0 top-0 bg-danger rounded-circle" style="width:12px; height:12px; margin-left:-7px; border: 2px solid #fff;"></div>
                        <div class="small text-muted"><?= date('Y年m月d日', strtotime($event['date'])) ?></div>
                        <div class="fw-bold text-danger">成員畢業 / 離開</div>
                        <div><strong><?= htmlspecialchars($event['name']) ?></strong> 結束團體活動正式畢業。</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($timeline)): ?>
                <div class="text-muted small ps-2">暫無活動紀錄</div>
            <?php endif; ?>
        </div>
    </div>
</div>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toastEl = document.getElementById('searchToast');
            if (toastEl) {
                setTimeout(function() {
                    var toast = new bootstrap.Toast(toastEl, {
                        autohide: false
                    });
                    toast.show();
                }, 2000);
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>