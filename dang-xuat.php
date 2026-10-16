<?php
/** Kết thúc toàn bộ phiên và xóa cookie phiên đúng đường dẫn. */
require __DIR__ . '/inc/config.php';
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $params['path'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Lax']);
session_destroy();
chuyenHuong('dang-nhap.php');
