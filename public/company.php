<?php
require_once __DIR__ . '/../includes/db_config.php';

// 從 DB 取出所有公司
$stmt = $pdo->query("SELECT id, name, logo_path FROM companies ORDER BY name ASC");
$companies_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 連結對應陣列（支援多連結，優先顯示 FB > 官網 > 其他）
$company_links = [
    '黑魔法演藝工作室' => [
        'fb' => 'https://www.facebook.com/BlackMagicStudio.IDOL/',
        'website' => 'https://blackmagic-studio.com'  // 已死，但保留
    ],
    '閃星演藝工作室(Shining Star Project)' => [  // 假設 DB 是這個，調整 key 成你 DB 實際 name
        'website' => 'https://shiningstar.ouen.tw/',
        'fb' => 'https://www.facebook.com/SSr561/'
    ],
    'Shining Star Project' => [  // 如果 DB 只有英文
        'website' => 'https://shiningstar.ouen.tw/',
        'fb' => 'https://www.facebook.com/SSr561/'
    ],
    'Toyplaトイプラ' => [
        'fb' => 'https://www.facebook.com/ToyplaOfficial/',
        'website' => 'https://amber-jack.co.jp/toypla/'  // 日本官網
    ],
    'Magic Idol Project' => [
        'fb' => 'https://www.facebook.com/MagicIdolProject/'
    ],
    'Zi:zoo Taipei' => [  // 從輸出看 DB 是 Zi:zoo Taipei
        'website' => 'https://zizoo-taipei.com/zh/',
        'fb' => 'https://www.facebook.com/zizootaipei/'
    ],
    'Funfull豐富娛樂' => [  // 注意 DB 可能是 Funfull豐富娛樂
        'fb' => 'https://www.facebook.com/FunfullEnt/'
    ],
    'MUSIC GEAR 音樂聚' => [
        'fb' => 'https://www.facebook.com/music.gear.tw/'
    ],
    '佐藤飛工作室' => [
        'fb' => 'https://www.facebook.com/SatoFeyStudio/'
    ],
    '信號蛋娛樂' => [
        'ig' => 'https://www.instagram.com/singhowdance/'
    ],
    '綻蒔演藝工作室(FloriSeed Idol Project)' => [
        'fb' => 'https://www.facebook.com/FloriSeedIdolProject'
    ],
    '杰克音樂Jack\'s studio (杰克音樂偶像部)' => [
        'fb' => 'https://www.facebook.com/p/%E6%9D%B0%E5%85%8B%E9%9F%B3%E6%A8%82%E5%81%B6%E5%83%8F%E9%83%A8-61573315101176/'
    ],
    '可蕾仙朵女僕偶像企劃(Crescendo Maid Idol Project)' => [  // 調整成你輸出中的名稱
        'fb' => 'https://www.facebook.com/cmip.tw/'
    ],
    'CutiVerse' => [
        'website' => 'https://www.cutiverse.com/'
    ],
    'Night Tour Idol 夜巡偶像部' => [
        'linktr' => 'https://linktr.ee/NightTourIdol'
    ],
    '布啾的工作室' => [
        'fb' => 'https://www.facebook.com/BUJONOSTUDIO/'
    ],
    'KIRA BASE' => [
        'website' => 'https://kirabase.com.tw/starry/'
    ],
    'HaiNuoYa' => [],
    '藤原瓔唱片國際股份有限公司' => [],
    'IfIDOL Project' => [  // 從最新搜尋，FB 是 https://www.facebook.com/ifidolproject
        'fb' => 'https://www.facebook.com/ifidolproject'
    ],
    'If IDOL Project' => [
        'fb' => 'https://www.facebook.com/ifidolproject',
        'ig' => 'https://www.instagram.com/ifidolproject'
    ],
    '夜巡-Night Tour' => [
        'linktr' => 'https://linktr.ee/NightTourIdol'
    ],
    // 加其他 DB 沒 match 的，例如 'ZeRockProject企劃' => [], '光跡途工作室' => [], 等
];

// 合併資料 + 加強匹配
$companies = [];
foreach ($companies_db as $row) {
    $name_original = trim($row['name']);
    $name_clean = preg_replace('/\s*\(.*?\)\s*/u', '', $name_original); // 移除括號 (Unicode 安全)
    $name_clean = trim($name_clean);

    $links = $company_links[$name_original] ?? $company_links[$name_clean] ?? [];

    // 如果沒找到，嘗試其他常見變體（例如 Shining Star 相關）
    if (empty($links)) {
        if (stripos($name_clean, 'Shining Star') !== false || stripos($name_original, '閃星') !== false) {
            $links = $company_links['Shining Star Project'] ?? [];
        } elseif (stripos($name_clean, 'If IDOL') !== false || stripos($name_original, 'IfIDOL') !== false) {
            $links = $company_links['IfIDOL Project'] ?? [];
        }
        if (stripos($name_original, '杰克音樂') !== false && stripos($name_original, '偶像部') !== false) {
            $links = [
                'fb' => 'https://www.facebook.com/p/%E6%9D%B0%E5%85%8B%E9%9F%B3%E6%A8%82%E5%81%B6%E5%83%8F%E9%83%A8-61573315101176/'
            ];
        }
        // 可以繼續加其他 if 條件
    }

    $companies[] = [
        'name' => $name_original,
        'logo_path' => $row['logo_path'] ? trim($row['logo_path']) : '',
        'links' => $links
    ];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地下偶像入口網站 - 營運公司一覽</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <link rel="stylesheet" href="./sass/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        $(function() {
            $("#nav-placeholder").load("navbar.php");
        });
    </script>
    <style>
        #contentL ul a:hover {
            text-decoration: underline;
        }
        #contentL small {
            font-size: 0.9em;
        }
        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
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
    <div id="nav-placeholder"></div>

    <div class="container mt-4">
        <div class="p-4">
            <div id="contentL">
                <h1>營運公司一覽</h1>
                <p>數據更新至2026/02/21</p>

                <ul style="list-style: none; padding-left: 0; line-height: 2.2;">
                    <?php foreach ($companies as $comp): ?>
                        <li style="margin: 15px 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                            <?php
                            $name = htmlspecialchars($comp['name']);
                            $logo_path = $comp['logo_path'];
                            $links = $comp['links'];

                            // 處理 logo src（本地路徑優先）
                            $logo_src = './img/placeholder-company.jpg';  // 預設
                            if (!empty($logo_path)) {
                                // 如果是完整 URL（舊資料相容）
                                if (filter_var($logo_path, FILTER_VALIDATE_URL)) {
                                    $logo_src = htmlspecialchars($logo_path);
                                } 
                                // 否則視為本地相對路徑，加上前綴
                                else {
                                    $logo_src = './uploads/companies/' . htmlspecialchars(trim($logo_path, '/'));
                                }
                            }

                            // 顯示名稱 + logo
                            $display = '<img src="' . $logo_src . '" alt="' . $name . ' logo" '
                                     . 'class="company-logo me-3" '
                                     . 'onerror="this.src=\'./img/placeholder-company.jpg\'; this.alt=\'Logo 載入失敗\';">'
                                     . $name;

                            if (!empty($links)) {
                                $first_link = reset($links);
                                echo '<a href="' . htmlspecialchars($first_link) . '" target="_blank" rel="noopener" '
                                     . 'style="text-decoration:none; color:#333; font-weight:bold;">' . $display . '</a><br>';

                                $link_display = [];
                                if (isset($links['fb']))      $link_display[] = '<a href="' . htmlspecialchars($links['fb']) . '" target="_blank" style="color:#1877f2; margin-right:10px;">FB</a>';
                                if (isset($links['website'])) $link_display[] = '<a href="' . htmlspecialchars($links['website']) . '" target="_blank" style="color:#0066cc; margin-right:10px;">官網</a>';
                                if (isset($links['ig']))      $link_display[] = '<a href="' . htmlspecialchars($links['ig']) . '" target="_blank" style="color:#e4405f; margin-right:10px;">IG</a>';
                                if (isset($links['linktr']))  $link_display[] = '<a href="' . htmlspecialchars($links['linktr']) . '" target="_blank" style="color:#00b140; margin-right:10px;">Linktree</a>';

                                echo implode(' | ', $link_display);
                            } else {
                                echo $display . ' <small style="color:#888; font-size:0.95em;">（暫無連結）</small>';
                            }
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p style="margin-top: 30px; font-size: 0.95em; color: #666;">
                    部分公司有官網/FB/IG 多連結，可點擊上方 FB/官網進入查看最新資訊。<br>
                    如連結失效或 logo 錯誤歡迎回報更新～
                </p>
            </div>

            <p>資料來源：</p>
            <blockquote cite="https://zh.wikiversity.org/wiki/%E5%8F%B0%E7%81%A3%E5%9C%B0%E4%B8%8B%E5%81%B6%E5%83%8F%E6%BC%94%E5%87%BA%E8%80%85">
                <ul>
                    <li>
                        <cite><a href="https://zh.wikiversity.org/wiki/%E5%8F%B0%E7%81%A3%E5%9C%B0%E4%B8%8B%E5%81%B6%E5%83%8F%E6%BC%94%E5%87%BA%E8%80%85">維基學院-台灣地下偶像演出者</a></cite>
                    </li>
                    <li>
                        <cite><a href="https://www.google.com/">Google</a></cite>
                    </li>
                </ul>
            </blockquote>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
    <script>
        // Toast 提示
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                var toastEl = document.getElementById('searchToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl, { autohide: false });
                    toast.show();
                }
            }, 2000);
        });

        // 所有連結在新分頁開啟
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('#contentL a').forEach(a => {
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
            });
        });
    </script>
</body>
</html>