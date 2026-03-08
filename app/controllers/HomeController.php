<?php
// app/controllers/HomeController.php

require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../models/MemberModel.php';
require_once __DIR__ . '/../models/CompanyModel.php';
require_once __DIR__ . '/../models/SearchModel.php';

class HomeController {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function index() {
        $groupModel   = new GroupModel();
        $memberModel  = new MemberModel();
        $companyModel = new CompanyModel();
        $searchModel  = new SearchModel();

        // 近期出道
        $recent_debut = $groupModel->getRecentDebut( );
        $error_msg = empty($recent_debut) ? "查詢近期出道失敗" : '';

        // 熱門成員
        $hot_members = $memberModel->getHotMembers(12);
        if (empty($hot_members)) {
            $hot_members = [
                ['id' => 1, 'stage_name' => '測試成員1', 'group_name' => '測試團', 'is_former' => 0, 'image_url' => './img/placeholder-member.jpg'],
                // ... 其他測試資料
            ];
        }

        // 近期生日
        $birthday_members = $memberModel->getUpcomingBirthdays(7, 7);

        // 精選團體（硬編 ID）
        $featured_groups = $groupModel->getFeaturedGroups([18, 43, 44,7]);

        // 所有公司
        $all_companies = $companyModel->getAll();

        // 搜尋
        $q = trim($_GET['q'] ?? '');
        $category = $_GET['category'] ?? 'all';
        $searchResults = $q ? $searchModel->search($q) : [];

        // 傳給 View
        $data = compact(
            'recent_debut', 'error_msg', 'hot_members', 'birthday_members',
            'featured_groups', 'all_companies', 'q', 'category', 'searchResults'
        );

        // 渲染 View
        extract($data);
        require __DIR__ . '/../../views/home.php';
    }
}