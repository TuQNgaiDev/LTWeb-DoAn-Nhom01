<?php
/** Trang lỗi thân thiện; prod không hiển thị chi tiết nội bộ. */
require_once __DIR__ . '/inc/config.php';
http_response_code(500);
$tieuDe = 'Hệ thống tạm gián đoạn'; $trang = '';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>500 · Hệ thống tạm gián đoạn</h1><p>Chưa thể xử lý yêu cầu. Vui lòng thử lại sau.</p>
<?php if (MOI_TRUONG === 'dev' && !empty($chiTietLoi)): ?><p><?= e($chiTietLoi) ?></p><?php endif; ?>
<a class="nut nut--phu" href="index.php">Về trang chủ</a></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
