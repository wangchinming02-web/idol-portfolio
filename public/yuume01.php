<?php
// 100 天內容陣列（直接從 IG 複製貼上，每一項是一天的完整文字）
$yuume_days = [
    1 => "Day 1 與推相遇的契機 這件事就要從我開始看花咲的說起了，第一次看演出是去年9/11的黑魔法集會(其實是9/9的月餅場，本來是想去看晚場的) 那一場其實我沒了哩咪之外，有稍微關注了芽芽，雖然我聲光炸開了黃色(被打)，第二次(沒記錯的話)看到花咲是在白宮，雖然那場都除了演出和物販之外都沒啥印象…原本說好的特能限制基本沒拿到，3000元根本是買攝影票，…\n\n（繼續貼完整內容）",
    2 => "Day 2 喜歡推的那一點……只能說都喜歡了吧(XD)\n喜歡她的一點……只能說都喜歡了吧(XD)\n不過是這麼膚淺的人嗎(?\n\n不！我膚深(X)\n言語表達我真的就只會說都喜歡了…其他的都講不出來，不管是誰都一樣",
    // ... 中間省略 ...
    100 => "Day 100 最後一天的總結...\n（貼第100篇完整內容）"
];

// 如果有缺的日子，用空字串補位
for ($i = 1; $i <= 100; $i++) {
    if (!isset($yuume_days[$i])) {
        $yuume_days[$i] = "這是第 {$i} 天內容（尚未填入 IG 貼文）";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地下偶像入口網站</title>

    <!-- 你的原始 CSS -->
    <link rel="stylesheet" href="./sass/all.css">


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <style>
        /* 芽人風格補強（可覆蓋 all.css 中的部分） */
        body {
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            font-family: "Noto Sans TC", "源泉圓體 TW", "Microsoft JhengHei", sans-serif;
            margin: 0;
        }

        main#content {
            max-width: 1200px;
            margin: 2rem auto;
            display: flex;
            gap: 2rem;
            padding: 0 1rem;
        }

        #contentL {
            width: 280px;
            flex-shrink: 0;
            font-family: "源泉圓體 TW", sans-serif;
            font-weight: 900;
            color: #8BA758;
            position: sticky;
            /* 關鍵：黏性定位 */
            top: 20px;
            /* 距離頂部 20px（可調整） */
            height: fit-content;
            /* 高度自動適應內容 */
            min-height: 300px;
            /* 最小高度，避免太短 */
            z-index: 10;
            /* 確保在內容上方 */
        }

        #contentL h3 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #8BA758;
            padding-bottom: 0.5rem;
        }

        .side-nav .side-link {
            display: block;
            color: #8BA758;
            text-decoration: none;
            padding: 0.8rem 0;
            border-bottom: 1px dashed #ccc;
        }

        .side-nav .side-link .sub {
            font-size: 0.9rem;
            color: #6c757d;
            display: block;
        }

        .latest-updates ul,
        .groups ul {
            list-style: none;
            padding-left: 0;
        }

        .latest-updates li,
        .groups li {
            padding: 0.6rem 0;
            border-bottom: 1px dashed #eee;
        }

        .latest-updates .date {
            color: #0d6efd;
            font-weight: bold;
            margin-right: 0.5rem;
        }

        #contentR {
            flex: 1;
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            min-height: 80vh;
            text-shadow: none !important;
            -webkit-text-stroke: 0 !important;
            stroke: none !important;
        }

        /* 手機版：左側移到上方 */
        @media (max-width: 991px) {
            main#content {
                flex-direction: column;
            }

            #contentL {
                width: 100%;
            }
        }

        /* 回頂部連結樣式（芽人風格） */
        .to-top {
            text-align: right;
            margin: 1.5rem 0;
            font-size: 0.95rem;
        }

        #contentR p {
            white-space: pre-wrap;
            /* 保留使用者輸入的換行 + 自動斷字換行 */
            word-break: break-word;
            /* 長單字強制斷行（例如超長網址） */
            line-height: 1.6;
            /* 行距舒適一點 */
            margin-bottom: 1.5rem;
            /* 段落間距 */
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main id="content">
        <div id="contentL">
            <h1>版主個人空間</h1>
            <!-- 最新發芽記錄（之後自己更新內容） -->
            <div class="latest-updates mt-5">
                <h3>最新發芽</h3>
                <ul>
                    <li><a href="#">白色相簿2</a></li>
                    <li><a href="#">優芽專輯場Repo</a></li>
                    <li><a href="#">優芽30日推</a></li>
                    <li><a href="yuume01.php">優芽百日推01</a></li>
                    <li><a href="#">優芽百日推02</a></li>
                    <li><a href="#">優芽百日推03</a></li>
                    <li><a href="#">天羽梨音解散活動Repo</a></li>
                    <li><a href="#">天羽梨音百日推</a></li>
                </ul>
            </div>
        </div>

        <div id="contentR">

            <!-- 這裡就是你之後的主要內容區 -->
            <!-- 可以放文章標題、照片、影片、文字、任何想寫的東西 -->

            <div class="post-header mb-4">
                <!-- 如果需要標題圖，可以在這裡放 -->
                <!-- <img src="你的標題圖.jpg" alt="標題圖" class="img-fluid rounded"> -->
                <h1 style="text-align:center; color:#8BA758; font-family:'源泉圓體 TW'; font-weight:900; margin:2rem 0;">
                    青羽優芽百日推01
                </h1>
            </div>

            <!-- 範例文章結構，你可以直接覆蓋或新增 section -->
            <section>
                <?php for ($day = 1; $day <= 100; $day++): ?>
        <div class="card mb-5 shadow-sm border-0 rounded-lg">
            <div class="card-body p-4">
                <h2 class="card-title text-success fw-bold mb-4">
                    青羽優芽百日推 DAY<?= str_pad($day, 2, '0', STR_PAD_LEFT) ?>
                </h2>
                <p class="card-text text-muted lh-lg">
                    <?= nl2br(htmlspecialchars($yuume_days[$day])) ?>
                </p>
            </div>
        </div>
    <?php endfor; ?>

                <p class="to-top">
                    <a href="#top">↑ 回頂部</a>
                </p>
            </section>

        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 如果你有 gotop 腳本或其他自訂 JS，也可以加在這裡 -->
    <!-- <script src="gotop copy.js"></script> -->

</body>

</html>