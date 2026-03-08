<div id="header" class="w-100">
        <!-- 改成完整的 Bootstrap Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
            <div class="container-fluid px-3">
                <!-- 品牌/Logo（可選，如果你想放 logo 在左邊） -->
                <a class="navbar-brand" href="index-new-資料庫.php">地下偶像入口</a>

                <!-- 手機版漢堡按鈕 -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- 導航內容 -->
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link text-white small" href="index-new-資料庫.php">首頁</a>
                        </li>

                        <!-- 團體相關 → 多層選單示範 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white small" href="#" id="groupDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                團體/成員
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="groupDropdown">
                                <li><a class="dropdown-item" href="group.html">團體一覽</a></li>
                                <li><a class="dropdown-item" href="group.html#members">成員一覽</a></li>

                                <!-- 第二層選單（nested） -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">熱門團體分類</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">花咲*Chloris</a></li>
                                        <li><a class="dropdown-item" href="#">魔法♡ドリーム</a></li>
                                        <li><a class="dropdown-item" href="#">ゼロ→ZeRock。</a></li>
                                        <li><a class="dropdown-item" href="#">其他團體...</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- 營運一覽 → 可以再加子層 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white small" href="#" id="companyDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                營運一覽
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="companyDropdown">
                                <li><a class="dropdown-item" href="company.html">所有營運公司</a></li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">推薦公司</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="/company/black-magic">#黑魔法</a></li>
                                        <li><a class="dropdown-item" href="/company/ssr">#SSR</a></li>
                                        <li><a class="dropdown-item" href="/company/magic-project">#Magic project</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-white small" href="about.html">關於我們</a>
                        </li>
                    </ul>

                    <!-- 右邊登入/註冊 -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link text-white small" href="login.php">登入</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white small" href="register.php">註冊</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- 原有的 Banner 保持不變 -->
        <div class="banner-container">
            <a href="./">
                <img src="./img/483523802_961462839494103_7288383640481031320_n.jpg" class="img-fluid w-100"
                    alt="地下偶像入口網站 Banner" id="forumlogo" style="max-height: 300px; object-fit: cover;">
            </a>
        </div>
    </div>