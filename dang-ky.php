<?php
/** Đăng ký tài khoản khách hàng; vai trò luôn do máy chủ đặt.
 * Email trùng được bắt từ ràng buộc UNIQUE và báo lỗi thân thiện.
 */
require __DIR__ . '/inc/config.php';
if (nguoiDung() !== null) { chuyenHuong('index.php'); }
$du = ['ho_ten' => '', 'email' => '']; $loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['ho_ten'] = chuoi($_POST, 'ho_ten');
    $du['email'] = strtolower(chuoi($_POST, 'email'));
    $matKhau = isset($_POST['mat_khau']) && is_string($_POST['mat_khau']) ? $_POST['mat_khau'] : '';
    $xacNhan = isset($_POST['xac_nhan']) && is_string($_POST['xac_nhan']) ? $_POST['xac_nhan'] : '';
    if (mb_strlen($du['ho_ten']) < 3 || mb_strlen($du['ho_ten']) > 100) { $loi['ho_ten'] = 'Họ tên dài từ 3 đến 100 ký tự.'; }
    if (!filter_var($du['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($du['email']) > 254) { $loi['email'] = 'Email không hợp lệ.'; }
    if (strlen($matKhau) < 8 || strlen($matKhau) > 200) { $loi['mat_khau'] = 'Mật khẩu dài từ 8 đến 200 ký tự.'; }
    if ($xacNhan !== $matKhau) { $loi['xac_nhan'] = 'Mật khẩu nhập lại chưa khớp.'; }
    if (!$loi) {
        try {
            (new App\Data\KhoNguoiDung(db()))->themKhach($du['email'], $du['ho_ten'], password_hash($matKhau, PASSWORD_DEFAULT));
            flash('Đăng ký thành công. Bạn có thể đăng nhập.');
            chuyenHuong('dang-nhap.php');
        } catch (PDOException $ex) {
            if ((int) ($ex->errorInfo[1] ?? 0) === 1062) { $loi['email'] = 'Email này đã được đăng ký.'; }
            else { throw $ex; }
        }
    }
}
$tieuDe = 'Đăng ký'; $trang = '';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Đăng ký tài khoản</h1>
<?php if ($loi): ?><p class="bieu-mau__loi" role="alert">Vui lòng kiểm tra các trường báo lỗi.</p><?php endif; ?>
<form class="bieu-mau" action="dang-ky.php" method="post">
<?php foreach (['ho_ten' => 'Họ và tên', 'email' => 'Email'] as $key => $nhan): ?><div class="bieu-mau__nhom"><label for="<?= e($key) ?>"><?= e($nhan) ?></label><input class="bieu-mau__o-nhap" id="<?= e($key) ?>" name="<?= e($key) ?>" type="<?= $key === 'email' ? 'email' : 'text' ?>" value="<?= e($du[$key]) ?>" maxlength="<?= $key === 'email' ? '254' : '100' ?>" required aria-describedby="loi-<?= e($key) ?>"><p class="bieu-mau__loi" id="loi-<?= e($key) ?>"><?= e($loi[$key] ?? '') ?></p></div><?php endforeach; ?>
<div class="bieu-mau__nhom"><label for="mat_khau">Mật khẩu</label><input class="bieu-mau__o-nhap" id="mat_khau" name="mat_khau" type="password" minlength="8" maxlength="200" autocomplete="new-password" required aria-describedby="loi-mat-khau"><p class="bieu-mau__loi" id="loi-mat-khau"><?= e($loi['mat_khau'] ?? '') ?></p></div>
<div class="bieu-mau__nhom"><label for="xac_nhan">Nhập lại mật khẩu</label><input class="bieu-mau__o-nhap" id="xac_nhan" name="xac_nhan" type="password" minlength="8" maxlength="200" autocomplete="new-password" required aria-describedby="loi-xac-nhan"><p class="bieu-mau__loi" id="loi-xac-nhan"><?= e($loi['xac_nhan'] ?? '') ?></p></div>
<button class="nut nut--chinh" type="submit">Tạo tài khoản</button>
</form></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
