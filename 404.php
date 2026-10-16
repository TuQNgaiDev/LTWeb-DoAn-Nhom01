<?php
/** Trang không tìm thấy với đúng HTTP 404. */
require_once __DIR__ . '/inc/config.php';
http_response_code(404);
$tieuDe = 'Không tìm thấy trang'; $trang = '';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>404 · Không tìm thấy trang</h1><p>Giáo trình hoặc địa chỉ bạn yêu cầu không tồn tại.</p><a class="nut nut--chinh" href="danh-sach.php">Về danh mục giáo trình</a></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
