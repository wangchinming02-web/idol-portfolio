<?php
// app/models/GroupModel.php

require_once __DIR__ . '/BaseModel.php';

class GroupModel extends BaseModel {

    /**
     * 取得近期出道團體（預設近6個月，LIMIT 8筆）
     * @param int $months 回溯幾個月
     * @param int $limit 每頁顯示筆數
     * @return array
     */
    
    public function getRecentDebut($months = 6, $limit = 150) {
    error_log("getRecentDebut called with months=$months, limit=$limit");
        $months_ago = date('Y-m-d', strtotime("-$months months"));
        
        // 強制轉 int，防止 SQL injection（雖然 $limit 是你控制的，但防呆）
        $limit = max(1, (int)$limit);  // 至少 1，避免 0 或負數

        $sql = "
            SELECT 
                g.id,
                g.name,
                g.debut_date,
                g.image_path,
                g.status,
                c.name AS company_name
            FROM idol_portal.groups g
            LEFT JOIN idol_portal.companies c ON g.company_id = c.id
            WHERE g.debut_date >= ?
              AND g.status = 'active'
            ORDER BY g.debut_date DESC
            LIMIT $limit
        ";
        
        // 只綁定日期參數，LIMIT 直接用字串拼接（安全，因為 $limit 已轉 int）
        $stmt = $this->query($sql, [$months_ago]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 取得指定 ID 的精選團體（含團員主要照片）
     * @param array $ids 團體 ID 陣列
     * @return array
     */
    public function getFeaturedGroups(array $ids) {
        if (empty($ids)) {
            return [];
        }

        // 產生 IN (?) 的 placeholders
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $sql = "
            SELECT 
                g.id,
                g.name AS group_name,
                g.debut_date,
                g.image_path,
                g.status,
                c.name AS company_name
            FROM idol_portal.groups g
            LEFT JOIN idol_portal.companies c ON g.company_id = c.id
            WHERE g.id IN ($placeholders)
            ORDER BY FIELD(g.id, $placeholders)
        ";
        
        // 重複兩次 ids：一次給 WHERE IN，一次給 FIELD 排序
        $params = array_merge($ids, $ids);
        
        $stmt = $this->query($sql, $params);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 為每個團體抓團員資料
        foreach ($groups as &$group) {
            $sql_members = "
                SELECT 
                    m.id,
                    m.stage_name,
                    m.member_color,
                    m.is_former,
                    COALESCE(
                        (SELECT mp.image_url FROM idol_portal.member_photos mp 
                         WHERE mp.member_id = m.id AND mp.is_primary = 1 LIMIT 1),
                        (SELECT mp.image_url FROM idol_portal.member_photos mp 
                         WHERE mp.member_id = m.id ORDER BY mp.sort_order ASC, mp.id ASC LIMIT 1),
                        './img/placeholder-member.jpg'
                    ) AS photo_url
                FROM idol_portal.members m
                WHERE m.group_id = ?
                ORDER BY m.id ASC
            ";
            
            $stmt_members = $this->query($sql_members, [(int)$group['id']]);
            $group['members'] = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($group);

        return $groups;
    }
}