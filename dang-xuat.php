<?php
/** Đăng xuất chỉ nhận POST để tránh thay đổi trạng thái từ liên kết GET. */
require __DIR__ . '/inc/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); header('Allow: POST');
    $tieuDe = 'Phương thức không hợp lệ'; require __DIR__ . '/inc/header.php';
    ?><main class="noi-dung-chinh"><h1>405 - Chỉ chấp nhận POST</h1></main><?php
    require __DIR__ . '/inc/footer.php'; exit;
}
dangXuat();
chuyenHuong('index.php');
