<?php
$db_config_path = __DIR__ . '/../includes/db_config.php';
require_once $db_config_path;

// ========================================
// 分頁設定
// ========================================
$perPageDesktop = 50;   // 電腦/平板 每頁 50 則
$perPageMobile  = 30;   // 手機 每頁 30 則

// 判斷裝置（簡單用 $_SERVER['HTTP_USER_AGENT'] 或畫面寬度判斷，這裡用最簡單方式）
$isMobile = (isset($_SERVER['HTTP_USER_AGENT']) && 
             preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $_SERVER['HTTP_USER_AGENT']));

$perPage = $isMobile ? $perPageMobile : $perPageDesktop;

// 目前頁碼（從 ?page= 取得，預設第 1 頁）
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $perPage;

// 處理留言提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    $nickname = trim($_POST['nickname'] ?? '匿名');

    if (!empty($message)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages (nickname, message, created_at)
                VALUES (:nickname, :message, NOW())
            ");
            $stmt->execute([
                ':nickname' => $nickname ?: '匿名',
                ':message'  => $message
            ]);
            // 提交成功後導向第一頁（或保持目前頁也可）
            header("Location: board.php?page=1");
            exit;
        } catch (PDOException $e) {
            $error = "留言失敗：" . $e->getMessage();
        }
    } else {
        $error = "請輸入留言內容";
    }
}

// 取得總留言數（用來計算總頁數）
try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM messages");
    $totalMessages = $totalStmt->fetchColumn();
    $totalPages = ceil($totalMessages / $perPage);
} catch (PDOException $e) {
    $totalMessages = 0;
    $totalPages = 1;
    $error = "讀取總數失敗：" . $e->getMessage();
}

// 讀取目前頁的留言（最新在上）
try {
    $stmt = $pdo->prepare("
        SELECT nickname, message, created_at
        FROM messages
        ORDER BY created_at DESC
        LIMIT :offset, :perPage
    ");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messages = [];
    $error = "讀取留言失敗：" . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>留言板 - 地下偶像入口網站</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./sass/all.css">
    <style>
        body { background: #f8f9fa; padding-top: 80px; }
        .message-box {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .message-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .nickname { font-weight: bold; color: #0d6efd; }
        .timestamp { color: #6c757d; font-size: 0.85rem; }
        .textarea { min-height: 120px; }
        .error { color: #dc3545; font-weight: bold; }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-5">
    <h1 class="text-center mb-5 fw-bold">留言板</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- 左邊：輸入留言表單 -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>我要留言</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="board.php?page=<?= $currentPage ?>">
                        <div class="mb-3">
                            <label for="nickname" class="form-label">暱稱（可選）</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" placeholder="匿名" maxlength="30">
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">留言內容 <span class="text-danger">*</span></label>
                            <textarea class="form-control textarea" id="message" name="message" required placeholder="在這裡寫下你的想法..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send me-2"></i>送出留言
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 右邊：顯示留言 + 分頁 -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>所有留言（<?= $totalMessages ?> 則）</h5>
                    <small>每頁 <?= $perPage ?> 則</small>
                </div>
                <div class="card-body" style="max-height: 700px; overflow-y: auto;">
                    <?php if (empty($messages)): ?>
                        <p class="text-center text-muted py-5">目前還沒有留言，快來搶第一則！</p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-box">
                                <div class="message-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="nickname"><?= htmlspecialchars($msg['nickname'] ?: '匿名') ?></span>
                                    </div>
                                    <small class="timestamp"><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></small>
                                </div>
                                <div class="message-content">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- 分頁導航 -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-light text-center">
                        <nav aria-label="留言分頁">
                            <ul class="pagination justify-content-center mb-0">
                                <!-- 上一頁 -->
                                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>" aria-label="上一頁">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>

                                <!-- 頁碼（顯示前後各 2 頁 + 目前頁） -->
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage   = min($totalPages, $currentPage + 2);

                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                    if ($startPage > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }

                                for ($i = $startPage; $i <= $endPage; $i++) {
                                    $active = ($i === $currentPage) ? 'active' : '';
                                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
                                }

                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                                }
                                ?>

                                <!-- 下一頁 -->
                                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>" aria-label="下一頁">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>