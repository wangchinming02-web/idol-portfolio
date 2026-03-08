<?php
// app/models/SearchModel.php

require_once __DIR__ . '/BaseModel.php';

class SearchModel extends BaseModel {

    public function search($q) {
        $params = [':q' => "%$q%"];
        $sql = "
            SELECT 'group' AS type, g.id, g.name AS name, g.image_path AS image_path, NULL AS country, NULL AS group_name
            FROM idol_portal.groups g WHERE g.name LIKE :q
            UNION ALL
            SELECT 'company' AS type, c.id, c.name AS name, COALESCE(c.logo_path, './img/placeholder.jpg') AS image_path, NULL AS country, NULL AS group_name
            FROM idol_portal.companies c WHERE c.name LIKE :q
            UNION ALL
            SELECT 'member' AS type, m.id, m.stage_name AS name, COALESCE(mp.image_url, './img/placeholder-member.jpg') AS image_path, NULL AS country, g.name AS group_name
            FROM idol_portal.members m
            LEFT JOIN idol_portal.groups g ON m.group_id = g.id
            LEFT JOIN idol_portal.member_photos mp ON mp.member_id = m.id AND mp.is_primary = 1
            WHERE m.stage_name LIKE :q
        ";
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}