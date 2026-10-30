<?php
/** Xử lý thêm, sửa, xóa giỏ qua POST rồi chuyển hướng PRG. */
require __DIR__ . '/inc/config.php';
$kho = khoGiaoTrinh(); $gio = new App\Services\GioHang();
$loi = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $hd = chuoi($_POST, 'hanh_dong');
        if (!in_array($hd, ['them', 'cap-nhat', 'xoa', 'xoa-het'], true)) { throw new InvalidArgumentException('Hành động không hợp lệ.'); }
        $id = soNguyen($_POST['id'] ?? null);
        if ($hd !== 'xoa-het' && ($id === null || $kho->timTheoId($id) === null)) { throw new InvalidArgumentException('Giáo trình không hợp lệ.'); }
        if ($hd === 'them' || $hd === 'cap-nhat') {
            $sl = soNguyen($_POST['so_luong'] ?? null, 1, 99);
            if ($sl === null) { throw new InvalidArgumentException('Số lượng phải là số nguyên từ 1 đến 99.'); }
            if ($hd === 'them') { $gio->them($id, $sl, $kho); } else { $gio->capNhat($id, $sl, $kho); }
        } elseif ($hd === 'xoa') { $gio->xoa($id); } else { $gio->xoaHet(); }
        flash('Đã cập nhật giỏ hàng.'); chuyenHuong('gio-hang.php');
    } catch (InvalidArgumentException $ex) { $loi = $ex->getMessage(); }
}
$chiTietGio = $gio->chiTiet($kho);
$tb = flash(); $tieuDe = 'Giỏ đặt mua giáo trình'; $trang = 'gio-hang';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Giỏ đặt mua giáo trình</h1>
<?php if ($loi): ?><p class="bieu-mau__loi" role="alert"><?= e($loi) ?></p><?php endif; ?>
<?php if ($tb): ?><p class="thong-bao-bieu-mau--thanh-cong" role="status"><?= e($tb) ?></p><?php endif; ?>
<?php if (!$chiTietGio): ?><p>Giỏ hàng đang trống. <a href="danh-sach.php">Chọn giáo trình</a>.</p><?php else: ?>
<div class="bang-cuon"><table><caption>Giá và số lượng giáo trình đặt mua</caption><thead><tr><th scope="col">Giáo trình</th><th scope="col">Đơn giá</th><th scope="col">Số lượng</th><th scope="col">Thành tiền</th><th scope="col">Thao tác</th></tr></thead><tbody>
<?php foreach ($chiTietGio as $muc): $s = $muc['giaoTrinh']; $sl = $muc['soLuong']; ?>
<tr><td><a href="chi-tiet.php?id=<?= e($s->id) ?>"><?= e($s->ten) ?></a></td><td><?= e(vnd($s->gia)) ?></td><td>
<form action="gio-hang.php" method="post"><input type="hidden" name="hanh_dong" value="cap-nhat"><input type="hidden" name="id" value="<?= e($s->id) ?>"><label for="sl-<?= e($s->id) ?>">Số lượng</label><input id="sl-<?= e($s->id) ?>" type="number" name="so_luong" min="1" max="<?= e(min(99, $s->soLuong)) ?>" value="<?= e($sl) ?>" required><button class="nut nut--phu" type="submit">Cập nhật</button></form>
</td><td><?= e(vnd($s->gia * $sl)) ?></td><td><form action="gio-hang.php" method="post"><input type="hidden" name="hanh_dong" value="xoa"><input type="hidden" name="id" value="<?= e($s->id) ?>"><button class="nut nut--phu" type="submit">Xóa <span class="sr-only"><?= e($s->ten) ?></span></button></form></td></tr>
<?php endforeach; ?></tbody></table></div>
<p><strong>Tổng tiền: <?= e(vnd($gio->tongTien($kho))) ?></strong></p>
<div class="nhom-nut"><form action="gio-hang.php" method="post"><input type="hidden" name="hanh_dong" value="xoa-het"><button class="nut nut--phu" type="submit">Xóa hết giỏ hàng</button></form><a class="nut nut--chinh" href="dat-hang.php">Đặt hàng</a></div>
<p>Đăng nhập để đặt hàng. Giá và tồn kho được máy chủ kiểm tra lại trong giao dịch.</p>
<?php endif; ?></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
