<?php
// app/models/MemberModel.php

require_once __DIR__ . '/BaseModel.php';

class MemberModel extends BaseModel {

    public function getHotMembers($limit = 12) {
        // 強制轉整數 + 防呆（至少 1 筆）
        $limit = max(1, (int)$limit);

        $sql = "
            SELECT
                m.id,
                m.group_id,
                m.stage_name,
                m.member_color,
                m.is_former,
                g.name AS group_name,
                c.name AS company_name,
                COALESCE(
                    (SELECT mp.image_url FROM idol_portal.member_photos mp WHERE mp.member_id = m.id AND mp.is_primary = 1 LIMIT 1),
                    (SELECT mp.image_url FROM idol_portal.member_photos mp WHERE mp.member_id = m.id ORDER BY mp.sort_order ASC, mp.id ASC LIMIT 1),
                    './img/placeholder-member.jpg'
                ) AS image_url
            FROM idol_portal.members m
            LEFT JOIN idol_portal.groups g ON m.group_id = g.id
            LEFT JOIN idol_portal.companies c ON g.company_id = c.id
            ORDER BY RAND()
            LIMIT $limit
        ";

        // 沒有其他參數要綁定，直接執行
        $stmt = $this->query($sql);  // ← 注意這裡沒有第二個參數！

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getUpcomingBirthdays($daysBefore = 7, $daysAfter = 7) {
        $sql = "
            SELECT m.id, m.stage_name, m.birth_date, m.member_color,
                   TIMESTAMPDIFF(YEAR, m.birth_date, CURDATE()) AS age,
                   g.name AS group_name,
                   COALESCE(
                       (SELECT mp.image_url FROM idol_portal.member_photos mp WHERE mp.member_id = m.id AND mp.is_primary = 1 LIMIT 1),
                       './img/placeholder-member.jpg'
                   ) AS image_url
            FROM idol_portal.members m
            LEFT JOIN idol_portal.groups g ON m.group_id = g.id
            WHERE DATE_FORMAT(m.birth_date, '%m-%d') BETWEEN
                  DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL ? DAY), '%m-%d') AND
                  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL ? DAY), '%m-%d')
              AND m.next_member_id IS NULL
            ORDER BY DATE_FORMAT(m.birth_date, '%m-%d') ASC
        ";
        $stmt = $this->query($sql, [$daysBefore, $daysAfter]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($members as &$m) {
            $m['display_age'] = ($m['age'] > 100) ? 18 : $m['age'];
        }
        return $members;
    }

    // 以後可加 getById($id), searchByName($keyword), getByGroup($group_id) 等
}