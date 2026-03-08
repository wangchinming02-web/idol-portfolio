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
        /* 讓 TODAY 標籤動起來更吸睛 */
        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: translateX(-50%) scale(1);
            }

            50% {
                transform: translateX(-50%) scale(1.1);
            }

            100% {
                transform: translateX(-50%) scale(1);
            }
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

    <div class="container mt-4">
        <div class="p-4">
            <div class="p-4 rounded mb-4 shadow-sm bg-white">
                <div class="row">
                    <div class="col-md-8 position-relative overflow-hidden rounded shadow-sm" id="search_bar_container" style="height: 300px; background: #000;">
                        <div id="searchBgCarousel" class="carousel slide carousel-fade position-absolute w-100 h-100 top-0 start-0" data-bs-ride="carousel">
                            <div class="carousel-inner h-100">
                                <?php
                                $dir = "img/carousel/";
                                $images = glob($dir . "*.{jpg,jpeg,png,JPG}", GLOB_BRACE);
                                if (empty($images)) $images = ['./img/placeholder-bg.jpg']; // 防呆背景
                                foreach ($images as $index => $image):
                                    $active = ($index === 0) ? 'active' : '';
                                ?>
                                    <div class="carousel-item <?php echo $active; ?> h-100">
                                        <div class="w-100 h-100" style="background: url('<?php echo $image; ?>') center/cover no-repeat;"></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="position-absolute w-100 h-100 top-0 start-0" style="background: rgba(0,0,0,0.4); z-index: 1;"></div>
                        <div class="position-relative p-4 h-100 d-flex flex-column justify-content-center" style="z-index: 2;">
                            <p class="mb-2 fw-bold text-white" style="font-size: 0.9rem;">
                                搜尋台灣地下偶像團體
                            </p>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" id="searchForm" class="input-group mb-3">
                                <input
                                    type="text"
                                    name="q"
                                    class="form-control"
                                    placeholder="輸入關鍵字..."
                                    value="<?php echo htmlspecialchars($q ?? ''); ?>"
                                    aria-label="搜尋關鍵字">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-light dropdown-toggle"
                                        type="button"
                                        id="categoryDropdown"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        全部分類
                                    </button>
                                    <ul class="dropdown-menu shadow">
                                        <li><a class="dropdown-item" href="#" data-value="all">全部</a></li>
                                        <li><a class="dropdown-item" href="#" data-value="company">公司</a></li>
                                    </ul>
                                </div>
                                <input type="hidden" name="category" id="categoryInput" value="<?php echo htmlspecialchars($category ?? 'all'); ?>">
                                <button class="btn btn-primary" type="submit" aria-label="搜尋">
                                    🔍
                                </button>
                            </form>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="?q=優芽Yuume" class="btn btn-sm btn-glass text-white border-white">#優芽Yuume</a>
                                <a href="?q=營運" class="btn btn-sm btn-glass text-white border-white">#營運</a>
                                <a href="?q=我老婆" class="btn btn-sm btn-glass text-white border-white">#我老婆</a>
                                <a href="?q=Chloris" class="btn btn-sm btn-glass text-white border-white">#Chloris</a>
                                <a href="?q=SSR" class="btn btn-sm btn-glass text-white border-white">#SSR</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm h-100">
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <h3 class="mb-3 card-title">查看地偶行事曆</h3>
                                <p class="text-muted mb-4">
                                    點擊下方按鈕查看 Google 日曆 <br>
                                    （包含過去與未來所有活動）<br>
                                    感謝叔公的整理及分享
                                </p>
                                <h3 class="mb-4">我愛叔公，我是叔公嘎仔</h3>
                                <a href="#full-calendar" class="btn btn-primary btn-lg px-5 py-3 mt-auto">
                                    <i class="bi bi-calendar-event me-2"></i> 前往地偶行事曆
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($q)): ?>
                        <div class="container mt-5 mb-5">
                            <h5 class="fw-bold mb-4 border-start border-primary border-4 ps-3">
                                關於「<?= htmlspecialchars($q) ?>」的搜尋結果
                                <span class="badge bg-secondary ms-2">
                                    <?= htmlspecialchars($category === 'all' ? '全部' : ($category === 'member' ? '成員' : ($category === 'company' ? '公司' : '團體'))) ?>
                                </span>
                            </h5>
                            <?php if (empty($searchResults)): ?>
                                <div class="alert alert-light border shadow-sm py-4 text-center">
                                    <i class="bi bi-search me-2"></i> 沒有找到相關內容。<br>
                                    <small class="text-muted">試試其他關鍵字，例如「優芽Yuume」、「SSR」、「Chloris」</small>
                                </div>
                            <?php else: ?>
                                <div class="row g-4 justify-content-start">
                                    <?php foreach ($searchResults as $item): ?>
                                        <div class="col-6 col-md-3 col-lg-3">
                                            <div class="card shadow-sm border-0 card-hover"
                                                style="height: 600px; min-height: 600px; display: flex; flex-direction: column;">
                                                <?php
                                                $publicBase = '/project01/public/';  // 保持不變
                                                $defaultPlaceholder = $publicBase . 'img/placeholder-member.jpg';

                                                // 先取出乾淨的路徑（去掉開頭斜線）
                                                $rawPath = ltrim($item['image_path'] ?? '', '/');

                                                // 根據 type 決定最終路徑
                                                $imgSrc = $defaultPlaceholder;  // 先設成預設，避免空值

                                                if (!empty($rawPath)) {
                                                    switch ($item['type'] ?? '') {
                                                        case 'member':
                                                            // 成員已經包含 uploads/members/，直接用
                                                            if (strpos($rawPath, 'uploads/members/') === 0) {
                                                                $imgSrc = $publicBase . $rawPath;
                                                            } else {
                                                                // 如果成員也變成只存檔名，補上
                                                                $imgSrc = $publicBase . 'uploads/members/' . $rawPath;
                                                            }
                                                            break;

                                                        case 'group':
                                                            // 團體圖片放在 uploads/groups/
                                                            if (strpos($rawPath, 'uploads/') === 0) {
                                                                $imgSrc = $publicBase . $rawPath;  // 如果已經帶 uploads/ 就直接用
                                                            } else {
                                                                $imgSrc = $publicBase . 'uploads/groups/' . $rawPath;
                                                            }
                                                            break;

                                                        case 'company':
                                                            // 公司圖片放在 uploads/companies/
                                                            if (strpos($rawPath, 'uploads/') === 0) {
                                                                $imgSrc = $publicBase . $rawPath;
                                                            } else {
                                                                $imgSrc = $publicBase . 'uploads/companies/' . $rawPath;
                                                            }
                                                            break;

                                                        default:
                                                            // 其他類型，強制補 uploads/others/ 或直接用預設
                                                            $imgSrc = $publicBase . 'uploads/others/' . $rawPath;
                                                            break;
                                                    }
                                                }
                                                ?>
                                                <img src="<?= htmlspecialchars($imgSrc) ?>"
                                                    class="card-img-top"
                                                    style="height: 550px; object-fit: contain; object-position: top; background: #f8f9fa;"
                                                    alt="<?= htmlspecialchars($item['name'] ?? '未知名稱') ?>"
                                                    onerror="this.onerror=null; this.src='<?= $defaultPlaceholder ?>'; this.alt='圖片載入失敗';">
                                                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                                    <h6 class="card-title fw-bold text-truncate mb-2" style="font-size: 1.1rem;">
                                                        <?= htmlspecialchars($item['name'] ?? '未知') ?>
                                                    </h6>
                                                    <p class="text-muted small mb-3 flex-grow-1">
                                                        <?php
                                                        $typeText = '未知類型';
                                                        if (isset($item['type'])) {
                                                            if ($item['type'] === 'member') {
                                                                $typeText = '成員';
                                                                if (!empty($item['group_name'])) {
                                                                    $typeText .= ' - ' . htmlspecialchars($item['group_name']);
                                                                }
                                                            } elseif ($item['type'] === 'group') {
                                                                $typeText = '偶像團體';
                                                            } elseif ($item['type'] === 'company') {
                                                                $typeText = '經紀公司';
                                                            }
                                                        }
                                                        echo $typeText;
                                                        ?>
                                                    </p>
                                                    <a href="<?php
                                                                $link = '#';
                                                                if (isset($item['type'])) {
                                                                    if ($item['type'] === 'member') $link = 'member_detail.php';
                                                                    elseif ($item['type'] === 'group') $link = 'group.php';
                                                                    elseif ($item['type'] === 'company') $link = 'company.php';
                                                                }
                                                                echo $link . '?id=' . ($item['id'] ?? '0');
                                                                ?>"
                                                        class="btn btn-sm btn-outline-primary mt-auto">
                                                        進入頁面
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row text-center mb-4 g-3">
                    <div class="col-4"><a href="#" class="d-block py-4 rounded shadow-sm fw-bold text-dark text-decoration-none border-0 btn-hover-effect">國家</a></div>
                    <div class="col-4"><a href="#" class="d-block py-4 rounded shadow-sm fw-bold text-dark text-decoration-none border-0 btn-hover-effect">經紀公司</a></div>
                    <div class="col-4"><a href="#" class="d-block py-4 rounded shadow-sm fw-bold text-dark text-decoration-none border-0 btn-hover-effect">推薦文章</a></div>
                </div>

                <div class="mb-5">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-cake2-fill text-danger"></i> 近期生日成員 <small class="text-muted fw-normal">(前後一週)</small>
                    </h6>
                    <?php if (empty($birthday_members)): ?>
                        <div class="alert alert-light border shadow-sm text-center py-4">
                            <p class="mb-0 text-muted">這段時間沒有成員過生日喔 🍰</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-nowrap overflow-auto gap-3 pb-3 pt-4" style="scrollbar-width: thin;">
                            <?php foreach ($birthday_members as $bm): ?>
                                <?php
                                $bday = date('m/d', strtotime($bm['birth_date']));
                                $is_today = (date('m-d') === date('m-d', strtotime($bm['birth_date'])));
                                ?>
                                <div class="card border-0 shadow-sm flex-shrink-0 position-relative" style="width: 160px; margin-top: 10px;">
                                    <?php if ($is_today): ?>
                                        <div class="position-absolute" style="top: -25px; left: 50%; transform: translateX(-50%); z-index: 5;">
                                            <span class="badge rounded-pill bg-danger shadow pulse-animation">🎂 TODAY!</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="camponylogo">
                                        <img src="<?= htmlspecialchars($bm['image_url'] ?? './img/placeholder-member.jpg') ?>"
                                            class="card-img-top w-100"
                                            style="height: 160px; object-fit: cover;"
                                            alt="<?= htmlspecialchars($bm['stage_name']) ?>"
                                            onerror="this.src='./img/placeholder-member.jpg';">
                                    </div>
                                    <div class="card-body p-2 text-center">
                                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.9rem;">
                                            <?= htmlspecialchars($bm['stage_name']) ?>
                                        </h6>
                                        <p class="small text-primary mb-1 fw-bold" style="font-size: 0.85rem;">
                                            <?= $bday ?> <span class="text-muted">(<?= $bm['display_age'] ?> 歲)</span>
                                        </p>
                                        <p class="small text-muted text-truncate mb-0" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($bm['group_name'] ?? 'Solo') ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <hr>

                <div class="mb-5">
                    <div class="mb-5">
                        <h6 class="fw-bold mb-4 d-flex align-items-center justify-content-between">
                            精選團體推薦 / 我愛花花娛樂
                            <div>
                                <button id="prevGroup" class="btn btn-outline-secondary btn-sm me-2" disabled>
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button id="nextGroup" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </h6>
                        <div id="featuredGroupsContainer" class="position-relative">
                            <?php foreach ($featured_groups as $index => $group): ?>
                                <div class="featured-group-card <?= $index === 0 ? '' : 'd-none' ?>" data-index="<?= $index ?>">
                                    <div class="card shadow-lg border-0">
                                        <div class="row g-0">
                                            <div class="col-md-5">
                                                <?php
                                                $base = '/project01/public/';   // 依照你的真實根目錄調整
                                                $img_src = !empty($group['image_path'])
                                                    ? $base . 'uploads/groups/' . ltrim($group['image_path'], '/')
                                                    : $base . 'img/placeholder-group.jpg';
                                                ?>
                                                <img src="<?= htmlspecialchars($img_src) ?>"
                                                    class="img-fluid w-100"
                                                    alt="<?= htmlspecialchars($group['group_name'] ?? '團體照片') ?>"
                                                    onerror="this.src='<?= $base ?>img/placeholder-group.jpg';">
                                            </div>
                                            <div class="col-md-7">
                                                <div class="card-body d-flex flex-column h-100 p-4">
                                                    <h4 class="card-title fw-bold mb-2 fs-4">
                                                        <?= htmlspecialchars($group['group_name'] ?? '未知團名') ?>
                                                        <?php if (!empty($group['status'])): ?>
                                                            <?php
                                                            $status_text = match ($group['status']) {
                                                                'active' => '活躍中',
                                                                'disbanded' => '已解散',
                                                                'hiatus' => '活動休止',
                                                                default => '未知'
                                                            };
                                                            $status_class = match ($group['status']) {
                                                                'active' => 'bg-success',
                                                                'disbanded' => 'bg-secondary',
                                                                'hiatus' => 'bg-warning',
                                                                default => 'bg-info'
                                                            };
                                                            ?>
                                                            <span class="badge <?= $status_class ?> ms-2"><?= $status_text ?></span>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <p class="text-muted mb-3 small">
                                                        營運公司：<?= htmlspecialchars($group['company_name'] ?? '獨立／無所屬公司') ?><br>
                                                        出道日期：<?= $group['debut_date'] ? date('Y/m/d', strtotime($group['debut_date'])) : '未知' ?>
                                                    </p>
                                                    <h6 class="fw-bold mb-2">團員列表</h6>
                                                    <?php if (!empty($group['members'])): ?>
                                                        <div class="member-grid">
                                                            <?php foreach ($group['members'] as $member): ?>
                                                                <div class="member-item d-flex align-items-center p-2 rounded bg-light-subtle border border-light">
                                                                    <div class="flex-shrink-0 me-3 overflow-hidden rounded-circle">
                                                                        <img src="<?= htmlspecialchars($member['photo_url'] ?? './img/placeholder-member.jpg') ?>"
                                                                            alt="<?= htmlspecialchars($member['stage_name'] ?? '成員') ?>"
                                                                            onerror="this.src='./img/placeholder-member.jpg';">
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold fs-6"><?= htmlspecialchars($member['stage_name'] ?: '無藝名') ?></div>
                                                                        <div class="d-flex align-items-center gap-2 mt-1">
                                                                            <?php if (!empty($member['member_color'])): ?>
                                                                                <span class="d-inline-block rounded-circle"
                                                                                    style="width: 14px; height: 14px; background-color: <?= htmlspecialchars($member['member_color']) ?>; border: 1px solid #ccc;"></span>
                                                                            <?php endif; ?>
                                                                            <?php if ($member['is_former'] == 1): ?>
                                                                                <small class="text-danger">(前成員)</small>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted small fst-italic">目前無團員資料或尚未設定</p>
                                                    <?php endif; ?>
                                                    <a href="group.php?id=<?= (int)$group['id'] ?>"
                                                        class="btn btn-primary mt-3 mt-md-auto btn-lg w-100 w-md-auto align-self-start">
                                                        查看團體詳細 <i class="bi bi-arrow-right ms-2"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-5">
                    <h6 class="fw-bold mb-3">熱門團體 / 熱門 solo</h6>
                    <?php if (empty($hot_members)): ?>
                        <div class="alert alert-info">
                            目前無熱門團體 / solo 資料...<br>
                            <small class="text-muted">（資料庫中可能還沒有成員資料，或查詢條件過濾掉所有結果）</small>
                        </div>
                    <?php else: ?>
                        <?php
                        $chunks = array_chunk($hot_members, 4);
                        if (empty($chunks)) $chunks = [[]];
                        ?>
                        <div id="hotIdolCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($chunks as $index => $chunk): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="row g-3 px-5">
                                            <?php if (empty($chunk)): ?>
                                                <div class="col-12 text-center py-4">
                                                    <p class="text-muted">此頁暫無資料</p>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($chunk as $member): ?>
                                                    <div class="col-3">
                                                        <div class="card h-100 shadow-sm border-0">
                                                            <img src="<?= htmlspecialchars($member['image_url'] ?? './img/placeholder-member.jpg') ?>"
                                                                class="card-img-top"
                                                                style="height: 200px; object-fit: cover;"
                                                                alt="<?= htmlspecialchars($member['stage_name'] ?? '成員照片') ?>">
                                                            <div class="card-body d-flex flex-column">
                                                                <h6 class="card-title fw-bold mb-1">
                                                                    <?= htmlspecialchars($member['stage_name'] ?: '未知名稱') ?>
                                                                </h6>
                                                                <p class="card-text small mb-2 text-muted">
                                                                    <?php
                                                                    $displayText = '';
                                                                    if (!empty($member['group_name'])) {
                                                                        $displayText = htmlspecialchars($member['group_name']);
                                                                        if (!empty($member['member_color'])) {
                                                                            $displayText .= ' (' . htmlspecialchars($member['member_color']) . ')';
                                                                        }
                                                                        if ($member['is_former'] == 1) {
                                                                            $displayText .= ' <span class="text-danger">(畢業 / 前成員)</span>';
                                                                        }
                                                                    } else {
                                                                        $displayText = 'Solo 藝人';
                                                                        if (!empty($member['company_name'])) {
                                                                            $displayText .= ' / ' . htmlspecialchars($member['company_name']);
                                                                        }
                                                                    }
                                                                    echo $displayText ?: '<span class="text-muted">（無所屬團體）</span>';
                                                                    ?>
                                                                </p>
                                                                <?php
                                                                $target_id = !empty($member['group_id']) ? $member['group_id'] : $member['id'];
                                                                $target_page = !empty($member['group_id']) ? 'group.php' : 'member_detail.php';
                                                                ?>
                                                                <a href="<?= $target_page ?>?id=<?= (int)$target_id ?>"
                                                                    class="btn btn-primary mt-auto btn-sm">
                                                                    前往
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($chunks) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#hotIdolCarousel" data-bs-slide="prev" style="width: 4%;">
                                    <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#hotIdolCarousel" data-bs-slide="next" style="width: 4%;">
                                    <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <br>

                <h6 class="fw-bold mb-3">近期出道</h6>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php endif; ?>
                <div id="debutCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
                    <div class="carousel-inner">
                        <?php if (empty($recent_debut)): ?>
                            <div class="carousel-item active">
                                <div class="row text-center">
                                    <div class="col-12 py-5">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-calendar-event me-2"></i>
                                            目前無近期出道資訊（近 6 個月內）...<br>
                                            <small class="text-muted">或許可以期待下個月囉～</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php $chunks = array_chunk($recent_debut, 2); ?>
                            <?php foreach ($chunks as $index => $chunk): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <div class="row text-center h-100 align-items-stretch g-3">
                                        <?php foreach ($chunk as $group): ?>
                                            <div class="col-6 d-flex">
                                                <div class="card mb-3 shadow-sm flex-fill border-0">
                                                    <div class="row g-0 h-100">
                                                        <div class="col-5 col-md-6">
                                                            <img src="/project01/public/uploads/groups/<?= htmlspecialchars(ltrim($group['image_path'] ?? '')) ?>"
                                                                class="img-fluid" alt="<?= htmlspecialchars($group['name'] ?? '團體照片') ?>">
                                                        </div>
                                                        <div class="col-7 col-md-6">
                                                            <div class="card-body d-flex flex-column justify-content-center p-3">
                                                                <h5 class="card-title fw-bold mb-1 fs-5">
                                                                    <?= htmlspecialchars($group['name'] ?? '未知團名') ?>
                                                                </h5>
                                                                <p class="card-text small text-muted mb-2">
                                                                    <?= htmlspecialchars($group['company_name'] ?? '獨立 / 未知公司') ?><br>
                                                                    出道：<?= $group['debut_date'] ? date('Y/m/d', strtotime($group['debut_date'])) : '未知' ?>
                                                                </p>
                                                                <a href="group.php?id=<?= (int)$group['id'] ?>"
                                                                    class="btn btn-sm btn-primary mt-2 mt-md-auto">
                                                                    更多資訊
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#debutCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#debutCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    <?php if (count($chunks ?? []) > 2): ?>
                        <div class="carousel-indicators" style="position: relative; margin-top: 12px; bottom: 0;">
                            <?php for ($i = 0; $i < count($chunks); $i++): ?>
                                <button type="button"
                                    data-bs-target="#debutCarousel"
                                    data-bs-slide-to="<?= $i ?>"
                                    class="<?= $i === 0 ? 'active' : '' ?>"
                                    aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
                                    aria-label="Slide <?= $i + 1 ?>">
                                </button>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <br>

                <h6 class="fw-bold mb-3">最新上架</h6>
                <div class="card shadow">
                    <div id="companyCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner album">
                            <div class="carousel-item active white-album">
                                <img src="./img/1200x630bb.jpg" class="d-block w-100" alt="Chl0r1s">
                                <div class="p-3">
                                    <h5 class="fw-bold text-primary">#花咲＊Chloris原創專輯「Chl0r1s」</h5>
                                    <div class="d-flex align-items-center gap-3">
                                        <p class="card-text mb-0">此處收聽：</p>
                                        <div class="d-flex gap-3 fs-4">
                                            <a href="https://open.spotify.com/artist/7LGFjcC5IcR8xXB1eaZVXN" class="text-success" target="_blank"><i class="bi bi-spotify"></i></a>
                                            <a href="https://music.apple.com/tw/album/chl0r1s/1740068232" class="text-secondary" target="_blank"><i class="bi bi-apple"></i></a>
                                            <a href="https://www.youtube.com/watch?v=eXR-oLa2yIc&list=OLAK5uy_mhkSrqi98uXslvnfIgVRLyiE3wRyLHGEU" class="text-danger" target="_blank"><i class="bi bi-youtube"></i></a>
                                            <a href="https://www.amazon.com/Chl0r1s-%E8%8A%B1%E5%92%B2-Chloris/dp/B0D149XTS4" class="text-warning" target="_blank"><i class="bi bi-amazon"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item white-album">
                                <img src="./img/gentirover.jpg" class="d-block w-100" alt="SSR">
                                <div class="p-3">
                                    <h5 class="fw-bold text-danger">#Gentirover首張原創單曲「REBORN」</h5>
                                    <div class="d-flex align-items-center gap-3">
                                        <p class="card-text mb-0"><a href="https://big-up.style/rGoPA3seLX?fbclid=..." target="_blank">此處收聽：</a></p>
                                        <div class="d-flex gap-3 fs-4">
                                            <a href="https://open.spotify.com/album/4oLn7UGOGjpo1XdEG4a54U" class="text-success" target="_blank"><i class="bi bi-spotify"></i></a>
                                            <a href="https://music.apple.com/jp/album/reborn-single/1698658773?l=en-US" class="text-secondary" target="_blank"><i class="bi bi-apple"></i></a>
                                            <a href="https://music.youtube.com/playlist?list=OLAK5uy_mwEEfYMmu7kwJt2gsOPfF9XTZHNv7_Dns" class="text-danger" target="_blank"><i class="bi bi-youtube"></i></a>
                                            <a href="https://music.amazon.co.jp/albums/B0CCDQDTRZ" class="text-warning" target="_blank"><i class="bi bi-amazon"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-indicators" style="bottom: 0px;">
                            <button type="button" data-bs-target="#companyCarousel" data-bs-slide-to="0" class="active bg-dark"></button>
                            <button type="button" data-bs-target="#companyCarousel" data-bs-slide-to="1" class="bg-dark"></button>
                        </div>
                    </div>
                </div>

                <br>

                <h6 class="fw-bold mb-3">營運/經紀公司</h6>
                <div class="card shadow">
                    <div id="companyCarousel-company" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php if (empty($all_companies)): ?>
                                <div class="carousel-item active p-5 text-center">
                                    <p class="text-muted">尚無公司資料</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($all_companies as $index => $company): ?>
                                    <?php
                                    $logo_src = '/img/default_company.jpg';
                                    if (!empty($company['logo_path'])) {
                                        $path = ltrim(trim($company['logo_path']), '/');
                                        $logo_src = '/project01/public/uploads/companies/' . $path;
                                    }
                                    ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> camponylogo">
                                        <div class="d-flex align-items-center justify-content-center bg-light" style="height: 350px; min-height: 350px;">
                                            <img src="<?= htmlspecialchars($logo_src) ?>"
                                                class="img-fluid"
                                                alt="<?= htmlspecialchars($company['name']) ?>"
                                                style="max-height: 320px; max-width: 90%; object-fit: contain;"
                                                onerror="this.src='/img/default_company.jpg';">
                                        </div>
                                        <div class="p-3 text-center">
                                            <h5 class="fw-bold text-primary mb-1"><?= htmlspecialchars($company['name']) ?></h5>
                                            <p class="card-text small text-muted">專業偶像營運與活動企劃</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#companyCarousel-company" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#companyCarousel-company" data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        <div class="carousel-indicators" style="bottom: 10px;">
                            <?php foreach ($all_companies as $index => $company): ?>
                                <button type="button"
                                    data-bs-target="#companyCarousel-company"
                                    data-bs-slide-to="<?= $index ?>"
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                    aria-label="Slide <?= $index + 1 ?>">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <br>

                <h6 class="fw-bold mb-3">場地方/常(租)用場地</h6>
                <div class="card shadow">
                    <div id="companyCarousel-location" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active locationIMG">
                                <img src="./img/杰克音樂.jpg" class="d-block w-100" alt="杰克音樂">
                                <div class="p-3">
                                    <h5 class="fw-bold text-primary">杰克音樂</h5>
                                    <p class="card-text">樂器研討交流、練團室預約聯絡電話: <br> 02-23810999 <br>
                                        演出接洽：jackbig8@gmail.com <br>
                                        地址：台北市萬華區昆明街76號B1</p>
                                </div>
                            </div>
                            <div class="carousel-item locationIMG">
                                <img src="./img/貝洛音樂.jpg" class="d-block w-100" alt="貝洛音樂">
                                <div class="p-3">
                                    <h5 class="fw-bold text-danger">月讀館貝洛音樂中心</h5>
                                    <p class="card-text">演出場所 / 舞台租用 / 教室租用
                                        光學動態捕捉服務 / Vtuber整合業務 <br>
                                        地址:台北市中正區羅斯福路一段94號B1</p>
                                </div>
                            </div>
                            <div class="carousel-item locationIMG">
                                <img src="./img/時藝劇場.jpg" class="d-block w-100" alt="時藝劇場">
                                <div class="p-3">
                                    <h5 class="fw-bold text-danger">時藝劇場</h5>
                                    <p class="card-text">所在地點： 空軍三重一村 <br>
                                        地址：新北市三重區正義南路86巷2號</p>
                                </div>
                            </div>
                            <div class="carousel-item locationIMG">
                                <img src="./img/魔法劇場.webp" class="d-block w-100" alt="魔法劇場">
                                <div class="p-3">
                                    <h5 class="fw-bold text-danger">魔法劇場</h5>
                                    <p class="card-text">展演活動空間、舞蹈教室
                                        適合演唱會、簽售會、見面會、會議等各種用途
                                        <br>24HR開放租借
                                        <br>地址:台北市萬華區武昌街二段21號2樓 (西門町徒步區)
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-indicators" style="bottom: 0px; position: relative; margin-top: 10px;">
                            <button type="button" data-bs-target="#companyCarousel-location" data-bs-slide-to="0" class="active bg-dark"></button>
                            <button type="button" data-bs-target="#companyCarousel-location" data-bs-slide-to="1" class="bg-dark"></button>
                            <button type="button" data-bs-target="#companyCarousel-location" data-bs-slide-to="2" class="bg-dark"></button>
                            <button type="button" data-bs-target="#companyCarousel-location" data-bs-slide-to="3" class="bg-dark"></button>
                        </div>
                    </div>
                </div>

                <div class="container mt-5" id="full-calendar">
                    <h3 class="mb-4">偶像活動行程表</h3>
                    <div class="ratio ratio-16x9">
                        <iframe src="https://calendar.google.com/calendar/embed?src=mr7kibfjcm3gu52v6t64lreras%40group.calendar.google.com&ctz=Asia%2FTaipei"
                            style="border: 0" width="100%" height="600" frameborder="0" scrolling="no"></iframe>
                    </div>
                </div>

                <div class="row mt-5 border-top pt-3">
                    <div class="col-6 border-end">
                        <h5 class="fw-bold mb-4">意見提供 / 私訊我</h5>
                        <p class="text-muted small mb-4">
                            公開討論請至 <a href="board.php" class="text-primary">留言板</a>，<br>
                            不想公開的意見、合作提案、問題回報，請填寫下方表單或直接聯絡我。
                        </p>
                        <form action="send_contact.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label small">您的 Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" id="contactEmail" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="contactSubject" class="form-label small">主旨 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="contactSubject" name="subject" placeholder="請輸入主旨" required>
                            </div>
                            <div class="mb-3">
                                <label for="formFile" class="form-label small">附件（可選）</label>
                                <input class="form-control form-control-sm" type="file" id="formFile" name="attachment" accept="image/*,.pdf">
                                <div class="form-text" style="font-size: 0.75rem;">支援 JPG, PNG, PDF，單檔上限 5MB</div>
                            </div>
                            <div class="mb-4">
                                <label for="contactMessage" class="form-label small">內容 <span class="text-danger">*</span></label>
                                <textarea class="form-control form-control-sm" id="contactMessage" name="message" rows="5" placeholder="請輸入詳細內容..." required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-sm">送出表單</button>
                            </div>
                        </form>
                        <div id="content">
                            <br>
                            <div id="contentR">
                                <h1>聯絡我</h1>
                                <p>歡迎透過以下方式與我聯絡：</p>
                                <ul>
                                    <li>Line ID: yuping19970715</li>
                                    <li>Instagram: @yp1997_</li>
                                    <li>Telegram: @a1025a47893</li>
                                    <li>Email: wang.chin.ming02@gmail.com </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 bg-light">
                        <img src="./img/LBMmjlG.jpeg" alt="" width="100%" height="auto">
                        <p>24歲，是個學生</p>
                        <img src="./img/IMG_3487.JPG" alt="" width="100%" height="auto">
                        <p>20歲以下，30年coding經驗</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../includes/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- 大張card-JavaScript 切換控制 -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.featured-group-card');
                const prevBtn = document.getElementById('prevGroup');
                const nextBtn = document.getElementById('nextGroup');
                let currentIndex = 0;

                function showCard(index) {
                    cards.forEach((card, i) => {
                        card.classList.toggle('d-none', i !== index);
                    });
                    prevBtn.disabled = index === 0;
                    nextBtn.disabled = index === cards.length - 1;
                }
                prevBtn.addEventListener('click', () => {
                    if (currentIndex > 0) {
                        currentIndex--;
                        showCard(currentIndex);
                    }
                });
                nextBtn.addEventListener('click', () => {
                    if (currentIndex < cards.length - 1) {
                        currentIndex++;
                        showCard(currentIndex);
                    }
                });
                showCard(0);
            });
        </script>

        <script>
            $(document).ready(function() {
                const $categoryToggle = $('#categoryDropdown');
                const $categoryItems = $categoryToggle.next('.dropdown-menu').find('.dropdown-item');
                $categoryItems.on('click', function(e) {
                    e.preventDefault();
                    const $this = $(this);
                    const text = $this.text().trim();
                    const value = $this.data('value') || 'all';
                    $categoryToggle.text(text);
                    let $hiddenInput = $('input[name="category"]', $categoryToggle.closest('form'));
                    if ($hiddenInput.length === 0) {
                        $hiddenInput = $('<input type="hidden" name="category" value="">');
                        $categoryToggle.closest('form').append($hiddenInput);
                    }
                    $hiddenInput.val(value);
                });

                const urlParams = new URLSearchParams(window.location.search);
                const currentCategory = urlParams.get('category');
                if (currentCategory) {
                    const $selectedItem = $categoryItems.filter(`[data-value="${currentCategory}"]`);
                    if ($selectedItem.length) {
                        $categoryToggle.text($selectedItem.text().trim());
                    }
                }
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