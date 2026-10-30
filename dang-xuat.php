<?php
/** Đăng xuất chỉ nhận POST để tránh thay đổi trạng thái từ liên kết GET. */
require __DIR__ . '/inc/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); header('Allow: POST');
    $tieuDe = 'Phương thức không hợp lệ'; require __DIR__ . '/inc/header.php';
    ?><main class="noi-dung-chinh"><h1>405 - Chỉ chấp nhận POST</h1></main><?php
    require __DIR__ . '/inc/footer.php'; exit;
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
chuyenHuong('index.php');
