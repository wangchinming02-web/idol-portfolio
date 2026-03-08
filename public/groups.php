<?php
require_once __DIR__ . '/../includes/db_config.php';

// 設定錯誤顯示（開發用，正式上線可移除）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // 查詢所有團體，按出道日期降序（最新出道在前面）
    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.name,
            g.image_path,
            g.debut_date,
            g.status,
            g.company_id,
            c.name AS company_name,
            c.logo_path AS company_logo
        FROM idol_portal.groups g
        LEFT JOIN idol_portal.companies c ON g.company_id = c.id
        ORDER BY g.debut_date DESC, g.id DESC
    ");
    $stmt->execute();
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($groups)) {
        $message = "目前資料庫中沒有任何團體記錄";
    }
} catch (PDOException $e) {
    $error = "資料庫查詢失敗：" . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地下偶像團體列表 - Groups</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding-top: 80px;
        }

        .group-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 12px;
            overflow: hidden;
        }

        .group-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .group-logo {
            width: 100%;
            height: 220px;
            object-fit: contain;
            background: #fff;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .status-active {
            background: #28a745;
            color: white;
        }

        .status-disbanded {
            background: #dc3545;
            color: white;
        }

        .status-hiatus {
            background: #ffc107;
            color: black;
        }

        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 50%;
            border: 2px solid #fff;
        }
    </style>
    <link rel="stylesheet" href="./sass/all.css">
</head>

<body>

    <!-- 導覽列 -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="text-center mb-5 fw-bold text-primary">地下偶像團體列表</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php elseif (isset($message)): ?>
            <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
        <?php else: ?>
            <!-- 卡片式列表 -->
            <div class="row g-4">
                <?php foreach ($groups as $group): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card group-card h-100 shadow">
                            <!-- 團體圖片：加上 /uploads/groups/ 前綴 -->
                            <a href="group.php?id=<?= htmlspecialchars($group['id']) ?>">
                                <img src="./uploads/groups/<?= htmlspecialchars($group['image_path'] ?? '') ?>"
                                    class="group-logo card-img-top"
                                    alt="<?= htmlspecialchars($group['name']) ?>"
                                    onerror="this.src='./img/placeholder-group.jpg'; this.alt='無團體圖片';">
                            </a>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold mb-2">
                                    <a href="group.php?id=<?= htmlspecialchars($group['id']) ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($group['name']) ?>
                                    </a>
                                </h5>

                                <!-- 狀態標籤 -->
                                <div class="mb-2">
                                    <?php
                                    $statusText = '';
                                    $statusClass = 'bg-secondary';
                                    switch ($group['status']) {
                                        case 'active':
                                            $statusText = '現役';
                                            $statusClass = 'bg-success';
                                            break;
                                        case 'disbanded':
                                            $statusText = '解散';
                                            $statusClass = 'bg-danger';
                                            break;
                                        case 'hiatus':
                                            $statusText = '活動休止';
                                            $statusClass = 'bg-warning text-dark';
                                            break;
                                        default:
                                            $statusText = '未知';
                                    }
                                    ?>
                                    <span class="badge <?= $statusClass ?> px-3 py-2">
                                        <?= $statusText ?>
                                    </span>
                                </div>

                                <!-- 出道日期 -->
                                <p class="text-muted mb-2">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    出道：<?= $group['debut_date'] ? date('Y/m/d', strtotime($group['debut_date'])) : '未知' ?>
                                </p>

                                <!-- 所屬公司 + Logo -->
                                <?php if (!empty($group['company_name'])): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <?php if (!empty($group['company_logo'])): ?>
                                            <img src="./uploads/companies/<?= htmlspecialchars($group['company_logo'] ?? '') ?>"
                                                class="company-logo me-2 rounded-circle"
                                                alt="<?= htmlspecialchars($group['company_name']) ?>"
                                                style="width:40px; height:40px; object-fit:cover;"
                                                onerror="this.src='./img/placeholder-company.jpg';">
                                        <?php else: ?>
                                            <!-- 沒 logo 時顯示 placeholder（可選） -->
                                            <img src="./img/placeholder-company.jpg"
                                                class="company-logo me-2 rounded-circle"
                                                alt="無公司 Logo">
                                        <?php endif; ?>
                                        <span>
                                            <i class="bi bi-building me-1"></i>
                                            <?= htmlspecialchars($group['company_name']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <!-- 進入詳細頁按鈕 -->
                                <a href="group.php?id=<?= htmlspecialchars($group['id']) ?>"
                                    class="btn btn-primary mt-auto">
                                    查看詳細資料
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="index-new-資料庫.php" class="btn btn-outline-secondary btn-lg">回到首頁</a>
        </div>
    </div>
   <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>