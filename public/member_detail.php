<?php
require_once __DIR__ . '/../includes/db_config.php';

// 獲取 URL 的 id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("無效的成員 ID");
}

// 函數：找到轉生鏈的最舊筆（往前追蹤）
function getOldestMemberId($pdo, $startId)
{
    $currentId = $startId;
    $visited = [];

    while (true) {
        if (in_array($currentId, $visited)) break;
        $visited[] = $currentId;

        $prevStmt = $pdo->prepare("SELECT id FROM members WHERE next_member_id = ? LIMIT 1");
        $prevStmt->execute([$currentId]);
        $prevId = $prevStmt->fetchColumn();

        if (!$prevId) break;
        $currentId = (int)$prevId;
    }
    return $currentId;
}

// 函數：從最舊 id 開始，拉完整轉生鏈
function getFullMemberChain($pdo, $oldestId)
{
    $chain = [];
    $currentId = $oldestId;
    $visited = [];

    while (true) {
        if (in_array($currentId, $visited)) break;
        $visited[] = $currentId;

        $stmt = $pdo->prepare("
            SELECT m.*, g.name AS group_name, mp.image_url AS primary_photo
            FROM members m
            LEFT JOIN `groups` g ON m.group_id = g.id
            LEFT JOIN member_photos mp ON mp.member_id = m.id AND mp.is_primary = 1
            WHERE m.id = ?
        ");
        $stmt->execute([$currentId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member) break;

        $chain[] = $member;

        $nextStmt = $pdo->prepare("SELECT next_member_id FROM members WHERE id = ?");
        $nextStmt->execute([$currentId]);
        $nextId = $nextStmt->fetchColumn();

        if (!$nextId) break;
        $currentId = (int)$nextId;
    }
    return $chain;
}

try {
    // 1. 找到鏈的最舊筆
    $oldestId = getOldestMemberId($pdo, $id);

    // 2. 拉完整鏈（用來顯示右側完整歷史）
    $memberChain = getFullMemberChain($pdo, $oldestId);

    if (empty($memberChain)) {
        die("找不到該成員");
    }

    // 左側卡片：用使用者點擊的 $id 來顯示基本資訊
    $stmt = $pdo->prepare("
        SELECT m.*, g.name AS group_name, mp.image_url AS primary_photo
        FROM members m
        LEFT JOIN `groups` g ON m.group_id = g.id
        LEFT JOIN member_photos mp ON mp.member_id = m.id AND mp.is_primary = 1
        WHERE m.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $main_member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$main_member) {
        die("找不到該成員");
    }

    $mid = $main_member['id'];

    // 抓取點擊階段的藝名紀錄（時間線用這個）
    $stmt_aliases = $pdo->prepare("
        SELECT ma.*, g.name as group_name 
        FROM member_aliases ma
        LEFT JOIN `groups` g ON ma.group_id = g.id
        WHERE ma.member_id = :mid
        ORDER BY COALESCE(ma.start_date, '0000-00-00') DESC
    ");
    $stmt_aliases->execute([':mid' => $mid]);
    $aliases = $stmt_aliases->fetchAll(PDO::FETCH_ASSOC);

    $stmt_group_history = $pdo->prepare("
        SELECT mgh.*, g.name as group_name 
        FROM member_group_history mgh
        LEFT JOIN `groups` g ON mgh.group_id = g.id
        WHERE mgh.member_id = :mid
        ORDER BY COALESCE(mgh.join_date, '0000-00-00') DESC
    ");
    $stmt_group_history->execute([':mid' => $mid]);
    $group_history = $stmt_group_history->fetchAll(PDO::FETCH_ASSOC);

    // 抓取點擊階段的照片（相簿用這個作為基準，但下面會顯示所有階段）
    $stmt_photos = $pdo->prepare("
        SELECT * FROM member_photos 
        WHERE member_id = :mid 
        ORDER BY sort_order ASC, id DESC
    ");
    $stmt_photos->execute([':mid' => $mid]);
    $photos = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);

    $profile_pic = './img/default_avatar.jpg';
    foreach ($photos as $p) {
        if ($p['photo_type'] === 'profile' && $p['is_primary'] == 1) {
            $profile_pic = $p['image_url'] ?? $profile_pic;
            break;
        }
    }
    if ($profile_pic === './img/default_avatar.jpg' && !empty($photos)) {
        $profile_pic = $photos[0]['image_url'] ?? $profile_pic;
    }
} catch (PDOException $e) {
    die("讀取失敗: " . htmlspecialchars($e->getMessage()));
}

// 建立統一的歷程陣列（以點擊階段為基準）
$unified_timeline = [];

// 放入藝名紀錄
foreach ($aliases as $a) {
    $unified_timeline[] = [
        'date'       => $a['start_date'] ?? '未知',
        'end_date'   => $a['end_date'] ?? '至今',
        'type'       => 'alias',
        'title'      => "使用藝名：" . htmlspecialchars($a['stage_name'] ?? '未知'),
        'group'      => htmlspecialchars($a['group_name'] ?? '無所屬'),
        'notes'      => htmlspecialchars($a['notes'] ?? ''),
        'is_primary' => $a['is_primary'] ?? 0
    ];
}

// 放入團體紀錄
foreach ($group_history as $gh) {
    $unified_timeline[] = [
        'date'       => $gh['join_date'] ?? '未知',
        'end_date'   => $gh['leave_date'] ?? '至今',
        'type'       => 'group',
        'title'      => "所屬團體變更：" . htmlspecialchars($gh['group_name'] ?? '未知'),
        'group'      => htmlspecialchars($gh['group_name'] ?? '無所屬'),
        'notes'      => htmlspecialchars($gh['notes'] ?? ''),
        'is_primary' => ($gh['leave_date'] === NULL ? 1 : 0)
    ];
}

// 排序：由新到舊
usort($unified_timeline, function ($a, $b) {
    $dateA = $a['date'] === '未知' ? '0000-00-00' : $a['date'];
    $dateB = $b['date'] === '未知' ? '0000-00-00' : $b['date'];
    return strcmp($dateB, $dateA);
});
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($main_member['stage_name'] ?? '未知成員') ?> - 成員檔案</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./sass/all.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/glightbox@3/dist/css/glightbox.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/glightbox@3/dist/js/glightbox.min.js"></script>
    <style>
        .timeline-item {
            border-left: 3px solid #0d6efd;
            padding-left: 20px;
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: "";
            position: absolute;
            left: -9px;
            top: 0;
            width: 15px;
            height: 15px;
            background: #0d6efd;
            border-radius: 50%;
        }

        .member-color-box {
            width: 30px;
            height: 30px;
            display: inline-block;
            vertical-align: middle;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .note-box {
            background: #f8f9fa;
            border-left: 4px solid #dee2e6;
            font-size: 0.9rem;
        }

        .stage-block {
            transition: background-color 0.2s;
        }

        .stage-block:hover {
            background-color: #f8f9fa;
        }

        .photo-item:hover img {
            transform: scale(1.05);
            transition: transform 0.3s;
        }
    </style>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row g-4">
            <!-- 左側：點擊階段的基本資訊（大頭貼加大） -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="mb-4 position-relative">
                            <img src="<?= htmlspecialchars($profile_pic) ?>"
                                alt="<?= htmlspecialchars($main_member['stage_name'] ?? '成員照片') ?>"
                                class="img-fluid shadow-lg"
                                onerror="this.src='./img/default_avatar.jpg';">
                        </div>

                        <h3 class="fw-bold mb-1"><?= htmlspecialchars($main_member['stage_name'] ?? '未知成員') ?></h3>
                        <p class="text-muted small mb-3">系統 ID: <?= $mid ?></p>

                        <hr class="my-4">

                        <div class="text-start">
                            <p class="mb-2">
                                <strong>代表色：</strong>
                                <?php if (!empty($main_member['member_color'])): ?>
                                    <span class="member-color-box d-inline-block"
                                        style="width: 24px; height: 24px; background-color: <?= htmlspecialchars($main_member['member_color']) ?>; border: 2px solid #fff; border-radius: 50%; vertical-align: middle; box-shadow: 0 0 8px rgba(0,0,0,0.2);"></span>
                                    <?= htmlspecialchars($main_member['member_color']) ?>
                                <?php else: ?>
                                    未提供
                                <?php endif; ?>
                            </p>
                            <p class="mb-2">
                                <strong>生日：</strong>
                                <?= $main_member['birth_date'] ? date('Y年m月d日', strtotime($main_member['birth_date'])) : '未提供' ?>
                            </p>
                            <p class="mb-2">
                                <strong>Instagram：</strong>
                                <?php if (!empty($main_member['instagram_handle'])): ?>
                                    <a href="https://www.instagram.com/<?= htmlspecialchars(ltrim($main_member['instagram_handle'], '@')) ?>/"
                                        target="_blank" class="text-primary">
                                        @<?= htmlspecialchars($main_member['instagram_handle']) ?>
                                    </a>
                                <?php else: ?>
                                    未提供
                                <?php endif; ?>
                            </p>
                            <p class="mb-2">
                                <strong>現役狀態：</strong>
                                <span class="badge rounded-pill px-3 py-2 fs-6 <?= $main_member['is_former'] ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $main_member['is_former'] ? '已畢業/前成員' : '現役' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右側：合併的完整轉生歷史 / 時間線 -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>完整時間線</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($memberChain)): ?>
                            <p class="text-muted text-center py-4">目前無轉生歷史</p>
                        <?php else: ?>
                            <?php foreach ($memberChain as $index => $m): ?>
                                <?php
                                // 拉取該階段的時間範圍（優先用 group_history）
                                $stageTimeStmt = $pdo->prepare("
                                    SELECT join_date, leave_date 
                                    FROM member_group_history 
                                    WHERE member_id = :stage_id 
                                    ORDER BY join_date DESC 
                                    LIMIT 1
                                ");
                                $stageTimeStmt->execute([':stage_id' => $m['id']]);
                                $stageTime = $stageTimeStmt->fetch(PDO::FETCH_ASSOC);

                                $joinDate = $stageTime['join_date'] ?? null;
                                $leaveDate = $stageTime['leave_date'] ?? null;

                                if ($joinDate) {
                                    $timeDisplay = date('Y/m/d', strtotime($joinDate)) . ' ~ ' . ($leaveDate ? date('Y/m/d', strtotime($leaveDate)) : '至今');
                                } else {
                                    $aliasTimeStmt = $pdo->prepare("
                                        SELECT start_date, end_date 
                                        FROM member_aliases 
                                        WHERE member_id = :stage_id AND is_primary = 1 
                                        LIMIT 1
                                    ");
                                    $aliasTimeStmt->execute([':stage_id' => $m['id']]);
                                    $aliasTime = $aliasTimeStmt->fetch(PDO::FETCH_ASSOC);
                                    $timeDisplay = ($aliasTime['start_date'] ? date('Y/m/d', strtotime($aliasTime['start_date'])) : '未知') . ' ~ ' . ($aliasTime['end_date'] ? date('Y/m/d', strtotime($aliasTime['end_date'])) : '至今');
                                }

                                $isActiveStage = ($m['id'] == $id);
                                ?>
                                <div class="stage-block p-4 border-bottom <?= $isActiveStage ? 'bg-light' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0">
                                            階段 <?= $index + 1 ?>：<?= htmlspecialchars($m['stage_name'] ?? '未知') ?>
                                            <?php if ($isActiveStage): ?>
                                                <span class="badge bg-primary ms-2">你點擊的階段</span>
                                            <?php endif; ?>
                                        </h5>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($timeDisplay) ?>
                                        </small>
                                    </div>

                                    <div class="row align-items-start">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <img src="<?= htmlspecialchars($m['primary_photo'] ?? './img/default_avatar.jpg') ?>"
                                                alt="<?= htmlspecialchars($m['stage_name'] ?? '階段照片') ?>"
                                                class="img-fluid rounded shadow"
                                                style="max-height: 250px; object-fit: contain;"
                                                onerror="this.src='./img/default_avatar.jpg';">
                                        </div>

                                        <div class="col-md-8">
                                            <div class="row g-3">
                                                <div class="col-6 col-md-4">
                                                    <p class="mb-1"><strong>團體：</strong></p>
                                                    <p><?= htmlspecialchars($m['group_name'] ?? 'Solo') ?></p>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <p class="mb-1"><strong>應援色：</strong></p>
                                                    <p>
                                                        <?php if (!empty($m['member_color'])): ?>
                                                            <span class="member-color-box d-inline-block me-2"
                                                                style="width: 20px; height: 20px; background-color: <?= htmlspecialchars($m['member_color']) ?>; border: 1px solid #ccc; border-radius: 50%; vertical-align: middle;"></span>
                                                            <?= htmlspecialchars($m['member_color']) ?>
                                                        <?php else: ?>
                                                            未提供
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <p class="mb-1"><strong>狀態：</strong></p>
                                                    <p>
                                                        <span class="badge <?= $m['is_former'] ? 'bg-danger' : 'bg-success' ?>">
                                                            <?= $m['is_former'] ? '已離開' : '現役' ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <p class="mb-1"><strong>生日：</strong></p>
                                                    <p><?= $m['birth_date'] ? date('Y/m/d', strtotime($m['birth_date'])) : '未提供' ?></p>
                                                </div>
                                                <div class="col-6 col-md-8">
                                                    <p class="mb-1"><strong>Instagram：</strong></p>
                                                    <p>
                                                        <?php if (!empty($m['instagram_handle'])): ?>
                                                            <a href="https://www.instagram.com/<?= htmlspecialchars(ltrim($m['instagram_handle'], '@')) ?>/" target="_blank">
                                                                @<?= htmlspecialchars($m['instagram_handle']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            未提供
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 相簿卡片（顯示所有階段的照片） -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-images me-2"></i>相簿</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($memberChain)): ?>
                            <p class="text-muted text-center py-4">目前無照片</p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($memberChain as $stage): ?>
                                    <?php
                                    $stagePhotosStmt = $pdo->prepare("
                                        SELECT * FROM member_photos 
                                        WHERE member_id = :stage_id 
                                        ORDER BY sort_order ASC, id DESC
                                    ");
                                    $stagePhotosStmt->execute([':stage_id' => $stage['id']]);
                                    $stagePhotos = $stagePhotosStmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (!empty($stagePhotos)): ?>
                                        <div class="col-12 mb-4">
                                            <h6 class="fw-bold text-center mb-3">
                                                <?= htmlspecialchars($stage['stage_name'] ?? '未知階段') ?> 時期照片
                                            </h6>
                                        </div>
                                        <?php foreach ($stagePhotos as $img): ?>
                                            <div class="col-6 col-sm-4 col-md-3">
                                                <div class="photo-item position-relative overflow-hidden rounded shadow-sm">
                                                    <a href="<?= htmlspecialchars($img['image_url'] ?? './img/placeholder-member.jpg') ?>"
                                                        class="glightbox"
                                                        data-gallery="member-photos"
                                                        data-title="<?= htmlspecialchars($img['title'] ?? '') ?>">

                                                        <img src="<?= htmlspecialchars($img['image_url'] ?? './img/placeholder-member.jpg') ?>"
                                                            class="img-fluid w-100"
                                                            alt="<?= htmlspecialchars($img['title'] ?? '成員照片') ?>"
                                                            style="height: 180px; object-fit: cover;"
                                                            onerror="this.src='./img/placeholder-member.jpg';">
                                                    </a>

                                                    <?php if (!empty($img['title'])): ?>
                                                        <div class="photo-caption position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white p-2 small">
                                                            <?= htmlspecialchars($img['title']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 頁尾 -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true,
            autoplayVideos: true
        });
    </script>
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
</body>

</html>