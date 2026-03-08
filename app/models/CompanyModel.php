<?php
// app/models/CompanyModel.php

require_once __DIR__ . '/BaseModel.php';

class CompanyModel extends BaseModel {

    public function getAll() {
        $sql = "SELECT * FROM companies ORDER BY id ASC";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}