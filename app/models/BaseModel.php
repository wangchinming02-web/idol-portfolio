<?php
// app/models/BaseModel.php

abstract class BaseModel {
    protected $pdo;

    public function __construct() {
        global $pdo;  // 來自 includes/db_config.php 的 global $pdo
        $this->pdo = $pdo;
    }

    // 共用安全查詢方法（防 SQL injection）
    protected function query($sql, $params = []) {
    $stmt = $this->pdo->prepare($sql);

    // 修正：把數值參數轉成 int/float，避免 PDO 當成字串綁定
    foreach ($params as $key => &$value) {
        if (is_numeric($value)) {
            $value = (int)$value;  // 或 (float) 如果是小數
        }
    }
    unset($value);

    $stmt->execute($params);
    return $stmt;
}
}