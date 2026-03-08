<?php
require_once __DIR__ . '/../includes/db_config.php';

// 設定錯誤顯示（開發用，正式上線可移除）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // 查詢所有成員，按 id 由小到大，並 LEFT JOIN 主要照片
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            mp.image_url AS photo_url
        FROM idol_portal.members m
        LEFT JOIN idol_portal.member_photos mp 
            ON mp.member_id = m.id 
            AND mp.is_primary = 1
        ORDER BY m.id ASC
    ");
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($members)) {
        echo "<h2 class='text-center mt-5'>目前 members 資料表中沒有任何記錄</h2>";
        exit;
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger container mt-5'>
            資料庫連線或查詢失敗：<br>" . htmlspecialchars($e->getMessage()) . "
          </div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地下偶像入口網站</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <link rel="stylesheet" href="./sass/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f8f9fa;
            padding: 40px 0;
            font-family: system-ui, sans-serif;
        }

        .table-responsive {
            margin-top: 30px;
        }

        th {
            background: #0d6efd;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        td,
        th {
            vertical-align: middle;
            text-align: center;
            padding: 12px !important;
        }

        .photo-col {
            width: 120px;
        }

        .photo-col img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .ig-link {
            color: #e1306c;
            text-decoration: none;
            font-weight: bold;
        }

        .ig-link:hover {
            text-decoration: underline;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        .active {
            color: #28a745;
            font-weight: bold;
        }

        .former {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
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

    <div class="container">
        <h1 class="text-center mb-4">idol_portal.members 資料表 - 所有記錄（按 id 由小到大）</h1>
        <p class="text-center text-muted mb-5">共找到 <?= count($members) ?> 筆資料</p>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th class="photo-col">照片</th>
                        <?php
                        // 動態顯示所有欄位（除了 photo_url，因為我們單獨處理照片）
                        if (!empty($members)) {
                            foreach (array_keys($members[0]) as $column) {
                                if ($column !== 'photo_url') {
                                    echo "<th scope='col'>" . htmlspecialchars($column) . "</th>";
                                }
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $row): ?>
                        <tr>
                            <!-- 照片欄 -->
                            <td class="photo-col">
    <a href="member_detail.php?id=<?= htmlspecialchars($row['id'] ?? '0') ?>" target="_blank">
        <img src="<?= htmlspecialchars($row['photo_url'] ?? './img/placeholder-member.jpg') ?>" 
             alt="<?= htmlspecialchars($row['stage_name'] ?? '成員照片') ?>" 
             onerror="this.src='./img/placeholder-member.jpg';"
             style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;">
    </a>
</td>

                            <?php foreach ($row as $key => $value): ?>
                                <?php if ($key === 'photo_url') continue; // 跳過照片欄 
                                ?>

                                <td>
                                    <?php
                                    if ($value === null) {
                                        echo "<span class='text-muted'>NULL</span>";
                                    } elseif ($key === 'birth_date' && $value) {
                                        echo date('Y-m-d', strtotime($value));
                                    } elseif ($key === 'instagram_handle' && $value) {
                                        // 改成 IG 連結
                                        $ig_url = "https://www.instagram.com/" . ltrim($value, '@') . "/";
                                        echo "<a href='$ig_url' target='_blank' class='ig-link'>@" . htmlspecialchars($value) . "</a>";
                                    } elseif ($key === 'is_former') {
                                        // 改成現役狀態
                                        echo $value == 0 ? "<span class='active'>現役</span>" : "<span class='former'>已畢業</span>";
                                    } elseif (is_numeric($value) && in_array($key, ['id', 'group_id'])) {
                                        echo $value;
                                    } else {
                                        echo htmlspecialchars($value);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-primary btn-lg">回到首頁</a>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>






    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. 先確認元素存在，避免 JS 報錯
            var toastEl = document.getElementById('searchToast');

            if (toastEl) {
                // 2. 設定延遲 2 秒後執行
                setTimeout(function() {
                    // 3. 初始化並顯示 Toast
                    var toast = new bootstrap.Toast(toastEl, {
                        autohide: false // 不會自動消失
                    });
                    toast.show();
                }, 2000);
            }
        });
    </script>
</body>

</html>