<?php
/** Đăng nhập bằng email và mật khẩu băm trong bảng nguoi_dung.
 * Thông báo lỗi chung không tiết lộ email có tồn tại hay không.
 */
require __DIR__ . '/inc/config.php';
if (nguoiDung() !== null) { chuyenHuong('index.php'); }
$loi = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(chuoi($_POST, 'email'));
    $matKhau = isset($_POST['mat_khau']) && is_string($_POST['mat_khau']) ? $_POST['mat_khau'] : '';
    $khoNguoiDung = new App\Data\KhoNguoiDung(db());
    $taiKhoan = $khoNguoiDung->timTheoEmail($email);
    if ($taiKhoan !== null && password_verify($matKhau, $taiKhoan['mat_khau'])) {
        if (password_needs_rehash($taiKhoan['mat_khau'], PASSWORD_DEFAULT)) {
            $khoNguoiDung->doiMatKhau($taiKhoan['id'], password_hash($matKhau, PASSWORD_DEFAULT));
        }
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $taiKhoan['id'];
        unset($_SESSION['sau_dang_nhap']);
        chuyenHuong('index.php');
    }
    $loi = 'Email hoặc mật khẩu không đúng.';
}
$tb = flash(); $tieuDe = 'Đăng nhập'; $trang = 'dang-nhap';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Đăng nhập</h1>
<?php if ($tb): ?><p class="thong-bao-bieu-mau--thanh-cong" role="status"><?= e($tb) ?></p><?php endif; ?>
<?php if ($loi): ?><p class="bieu-mau__loi" role="alert"><?= e($loi) ?></p><?php endif; ?>
<form class="bieu-mau" action="dang-nhap.php" method="post">
<div class="bieu-mau__nhom"><label for="email">Email</label><input class="bieu-mau__o-nhap" id="email" name="email" type="email" value="<?= e($email) ?>" maxlength="254" autocomplete="username" required></div>
<div class="bieu-mau__nhom"><label for="mat_khau">Mật khẩu</label><input class="bieu-mau__o-nhap" id="mat_khau" name="mat_khau" type="password" minlength="8" maxlength="200" autocomplete="current-password" required></div>
<button class="nut nut--chinh" type="submit">Đăng nhập</button>
</form><p>Chưa có tài khoản? <a href="dang-ky.php">Đăng ký</a>.</p></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
