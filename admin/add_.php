<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>後台管理系統 - 地下偶像入口網站</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;      /* 水平置中 */
            /* align-items: center;       */ /* 如果你也想要垂直置中，整個畫面置中才取消註解 */
        }

        .container {
            max-width: 1080px;             /* 限制最大寬度，避免超大螢幕太寬 */
            width: 100%;
            text-align: center;
        }

        .menu-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;      /* 卡片水平置中排列 */
            gap: 20px;                    /* 卡片之間的間距 */
            margin-top: 30px;
        }

        .menu-card {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            width: 200px;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.15s;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            font-size: 1.1em;
        }

        h1 {
            margin: 40px 0 20px;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>偶像資料管理後台</h1>

        <div class="menu-wrapper">
            <div class="menu-card">
                <h3>1. 營運管理</h3>
                <a href="add_company.php">新增公司/營運</a>
            </div>
            <div class="menu-card">
                <h3>2. 團體管理</h3>
                <a href="add_group.php">新增偶像團體</a>
            </div>
            <div class="menu-card">
                <h3>3. 成員管理</h3>
                <a href="add_member.php">新增成員資料</a>
            </div>
            <div class="menu-card">
                <h3>4. 照片管理</h3>
                <a href="add_photo.php">新增成員照片</a>
            </div>
            <div class="menu-card">
                <h3>5. 團體logo管理</h3>
                <a href="batch_upload_group_logos_a.php">批量上傳團體logo</a>
            </div>
            <div class="menu-card">
                <h3>6. 營運logo管理</h3>
                <a href="upload_company_logos.php">批量上傳營運logo</a>
            </div>
        </div>
    </div>

</body>
</html>