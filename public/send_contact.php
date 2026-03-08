<?php
// send-contact.php - 聯絡表單處理（POST only）

require_once __DIR__ . '/includes/config.php';  // 載入 db + mail 設定

// PHPMailer（手動載入）
require_once __DIR__ . '/../src/Exception.php';
require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index-new-資料庫.php");
    exit;
}

$from_email   = trim($_POST['email'] ?? '');
$from_name    = trim($_POST['nickname'] ?? '匿名訪客');
$subject      = trim($_POST['subject'] ?? '無主旨');
$message      = trim($_POST['message'] ?? '');
$attachment   = $_FILES['attachment'] ?? null;

if (empty($from_email) || empty($message) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index-new-資料庫.php?error=" . urlencode('請填寫有效的 Email 與留言內容'));
    exit;
}

$mail = new PHPMailer(true);

try {
    // ===== SMTP 設定（從 config.php） =====
    $mail->isSMTP();
    $mail->Host       = $mail_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_username;
    $mail->Password   = $mail_password;
    $mail->SMTPSecure = $mail_secure;
    $mail->Port       = $mail_port;

    // 測試時開啟 debug（收到信後註解掉）
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // 2 = 詳細輸出，正式上線關掉

    $mail->CharSet = PHPMailer::CHARSET_UTF8;

    // ===== 寄/收件人（Gmail 強制 From 匹配 Username） =====
    $mail->setFrom($mail_from_email, $mail_from_name);          // 固定你的 Gmail
    $mail->addAddress($mail_to_email, $mail_to_name);
    $mail->addReplyTo($from_email, $from_name);                 // 回覆給訪客

    // ===== 內容 =====
    $mail->isHTML(true);
    $mail->Subject = "[網站意見表單] " . $subject;
    $mail->Body    = "
        <h3>新意見表單</h3>
        <p><strong>訪客姓名：</strong> " . htmlspecialchars($from_name) . "</p>
        <p><strong>訪客 Email：</strong> " . htmlspecialchars($from_email) . " （請直接回覆此信聯絡）</p>
        <p><strong>主旨：</strong> " . htmlspecialchars($subject) . "</p>
        <p><strong>內容：</strong></p>
        <p>" . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "</p>
        <hr>
        <small>來自地下偶像入口網站 - " . date('Y-m-d H:i:s') . "</small>
    ";

    // ===== 附件（加大小限制） =====
    if ($attachment && $attachment['error'] === UPLOAD_ERR_OK && $attachment['size'] > 0) {
        if ($attachment['size'] > 5 * 1024 * 1024) {  // 5MB 限制
            throw new Exception('附件過大（限 5MB 以內）');
        }
        $mail->addAttachment($attachment['tmp_name'], $attachment['name']);
    }

    $mail->send();
    header("Location: index-new-資料庫.php?success=" . urlencode('意見已送出，我們會盡快回覆！'));
    exit;

} catch (Exception $e) {
    error_log("寄信失敗: " . $mail->ErrorInfo . " | 訪客: $from_email");
    header("Location: index-new-資料庫.php?error=" . urlencode('寄信失敗，請稍後再試或直接寄到 ' . $mail_to_email));
    exit;
}