<?php
/** Trang báo không đủ quyền; dùng được cả khi được require từ trang khác. */
require_once __DIR__ . '/inc/config.php';
http_response_code(403);
$tieuDe = 'Không có quyền truy cập';
$trang = '';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh">
  <h1>403 - Không có quyền truy cập</h1>
  <p>Tài khoản hiện tại không được phép thực hiện thao tác này.</p>
  <p><a href="index.php">Về trang chủ</a></p>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>
