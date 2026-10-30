<?php
/** Tạo đơn từ giỏ cho người đã đăng nhập bằng một giao dịch CSDL.
 * POST thành công chuyển sang trang chi tiết, nên F5 không tạo thêm đơn.
 */
require __DIR__ . '/inc/config.php';
$nd = yeuCauDangNhap();
$gio = new App\Services\GioHang();
$kho = khoGiaoTrinh();
$chiTietGio = $gio->chiTiet($kho);
$du = ['dia_chi' => '', 'ghi_chu' => '']; $loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['dia_chi'] = chuoi($_POST, 'dia_chi');
    $du['ghi_chu'] = chuoi($_POST, 'ghi_chu');
    if (mb_strlen($du['dia_chi']) < 5 || mb_strlen($du['dia_chi']) > 255) { $loi['dia_chi'] = 'Địa chỉ nhận dài từ 5 đến 255 ký tự.'; }
    if (mb_strlen($du['ghi_chu']) > 1000) { $loi['ghi_chu'] = 'Ghi chú không quá 1000 ký tự.'; }
    if (!$chiTietGio) { $loi['gio'] = 'Giỏ hàng đang trống.'; }
    if (!$loi) {
        try {
            $id = (new App\Data\KhoDonHang(db()))->dat($nd['id'], $gio->cacMuc(), $du['dia_chi'], $du['ghi_chu']);
            $gio->xoaHet(); flash('Đặt hàng thành công.'); chuyenHuong('don-hang.php?id=' . $id);
        } catch (RuntimeException $ex) { $loi['gio'] = $ex->getMessage(); }
    }
}
$tieuDe = 'Xác nhận đặt hàng'; $trang = 'gio-hang'; require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Xác nhận đặt hàng</h1>
<?php if (isset($loi['gio'])): ?><p class="bieu-mau__loi" role="alert"><?= e($loi['gio']) ?></p><?php endif; ?>
<?php if (!$chiTietGio): ?><p>Giỏ hàng đang trống. <a href="danh-sach.php">Chọn giáo trình</a>.</p><?php else: ?>
<ul><?php foreach ($chiTietGio as $muc): ?><li><?= e($muc['giaoTrinh']->ten) ?>: <?= e($muc['soLuong']) ?> × <?= e(vnd($muc['giaoTrinh']->gia)) ?></li><?php endforeach; ?></ul>
<p><strong>Tổng tiền: <?= e(vnd($gio->tongTien($kho))) ?></strong></p>
<form class="bieu-mau" action="dat-hang.php" method="post">
<div class="bieu-mau__nhom"><label for="dia_chi">Địa chỉ nhận</label><input class="bieu-mau__o-nhap" id="dia_chi" name="dia_chi" value="<?= e($du['dia_chi']) ?>" minlength="5" maxlength="255" required aria-describedby="loi-dia-chi"><p class="bieu-mau__loi" id="loi-dia-chi"><?= e($loi['dia_chi'] ?? '') ?></p></div>
<div class="bieu-mau__nhom"><label for="ghi_chu">Ghi chú</label><textarea class="bieu-mau__o-nhap" id="ghi_chu" name="ghi_chu" maxlength="1000" rows="4"><?= e($du['ghi_chu']) ?></textarea><p class="bieu-mau__loi"><?= e($loi['ghi_chu'] ?? '') ?></p></div>
<button class="nut nut--chinh" type="submit">Đặt hàng</button></form><?php endif; ?></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
