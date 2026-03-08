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
                    關於版主
                </h1>
            </div>

            <!-- 範例文章結構，你可以直接覆蓋或新增 section -->
            <section>
                <h2>版主自我介紹</h2>
                <p>白學家/冬馬黨/綠綠發芽人/發芽的人/臭執著仔/天羽波堤/發芽的白學家/桃心禹平</p>
                <p>
                <h3>我永遠愛著冬(青)馬(羽)和(優)紗(芽)</h3>
                </p>
                <p>優芽的超重度發芽仔aka臭臭執著仔<br>
                    天羽梨音的天羽波堤🍩<br>
                    桃心愛琉的桃心禹平<br>
                    啄木鳥🌲🐦<br>
                    一ノ瀬かな的紫PY💜<br>
                    今天也最喜歡紗音了<br>
                    🌱Midori🌱<br>
                    優奈的發芽好朋朋/發廚的藍芽甲魚<br>
                    專精發芽技能🌱<br>
                    星奈寶貝的寶貝<br>
                    與井🥺
                    自費攝影師</p>
                    <p><h4>今年28歲，剛不是學生</h4></p>
                    <p><h4>我覺得我的網站十分甚至九分的好</h4></p>
                    <p><h4>感謝雷普...我說雷厲風行的科普</h4></p>
                <!-- 照片展示格子（之後可大量使用） -->
                <div class="photo-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:1rem; margin:2rem 0;">
                    <!-- <img src="照片1.jpg" alt="" class="img-fluid rounded" loading="lazy"> -->
                    <!-- <img src="照片2.jpg" alt="" class="img-fluid rounded" loading="lazy"> -->
                </div>

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