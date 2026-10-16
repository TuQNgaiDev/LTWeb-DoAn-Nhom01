<?php
/** Xác thực tài khoản thử bằng password_verify; đổi session ID khi thành công. */
require __DIR__ . '/inc/config.php';
$loi = ''; $ten = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = chuoi($_POST, 'ten_dang_nhap');
    $matKhau = isset($_POST['mat_khau']) && is_string($_POST['mat_khau']) ? $_POST['mat_khau'] : '';
    $taiKhoan = require __DIR__ . '/inc/tai-khoan.php';
    if (mb_strlen($ten) <= 50 && strlen($matKhau) <= 200 && isset($taiKhoan[$ten]) && password_verify($matKhau, $taiKhoan[$ten])) {
        session_regenerate_id(true); $_SESSION['user'] = $ten; chuyenHuong('quan-tri.php');
    }
    error_log('Dang nhap sai tai khoan thu; thoi gian ' . date(DATE_ATOM));
    $loi = 'Sai tên đăng nhập hoặc mật khẩu';
}
$tieuDe = 'Đăng nhập'; $trang = 'dang-nhap';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Đăng nhập quản trị</h1>
<?php if ($loi): ?><p class="bieu-mau__loi" role="alert"><?= e($loi) ?></p><?php endif; ?>
<form class="bieu-mau" action="dang-nhap.php" method="post"><div class="bieu-mau__nhom"><label for="ten_dang_nhap">Tên đăng nhập</label><input class="bieu-mau__o-nhap" id="ten_dang_nhap" name="ten_dang_nhap" value="<?= e($ten) ?>" maxlength="50" autocomplete="username" required></div><div class="bieu-mau__nhom"><label for="mat_khau">Mật khẩu</label><input class="bieu-mau__o-nhap" id="mat_khau" name="mat_khau" type="password" maxlength="200" autocomplete="current-password" required></div><button class="nut nut--chinh" type="submit">Đăng nhập</button></form></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
