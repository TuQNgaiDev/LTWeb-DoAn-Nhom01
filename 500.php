<?php
/** Trang lỗi độc lập, tuyệt đối không gọi header hoặc truy vấn CSDL. */
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Hệ thống tạm gián đoạn | UEDBookHub</title><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/php.css"></head>
<body class="trang"><main class="noi-dung-chinh"><h1>500 - Hệ thống tạm gián đoạn</h1><p>Chưa thể xử lý yêu cầu. Vui lòng thử lại sau.</p>
<?php if (defined('MOI_TRUONG') && MOI_TRUONG === 'dev' && !empty($chiTietLoi)): ?><p><?= htmlspecialchars((string) $chiTietLoi, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><?php endif; ?>
<a class="nut nut--phu" href="index.php">Về trang chủ</a></main></body></html>
