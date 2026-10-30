<?php
/** Hiển thị đơn của chính khách hàng; quản trị được xem mọi đơn và đổi trạng thái.
 * Truy vấn chi tiết luôn kèm điều kiện chủ sở hữu để ID của khách khác trả 404.
 */
require __DIR__ . '/inc/config.php';
if (defined('MOI_TRUONG') && MOI_TRUONG === 'dev' && isset($_GET['dang_nhap_khach'])) {
    $_SESSION['uid'] = 2;
}
$nd = yeuCauDangNhap(); $laQuanTri = $nd['vai_tro'] === 'quan_tri';
$khoDon = new App\Data\KhoDonHang(db());
$id = soNguyen($_GET['id'] ?? null);
$trangThai = ['moi' => 'Mới', 'dang_xu_ly' => 'Đang xử lý', 'da_giao' => 'Đã giao', 'da_huy' => 'Đã hủy'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$laQuanTri) { http_response_code(403); require __DIR__ . '/403.php'; exit; }
    $idPost = soNguyen($_POST['id'] ?? null); $moi = chuoi($_POST, 'trang_thai');
    if ($idPost === null || !array_key_exists($moi, $trangThai) || !$khoDon->doiTrangThai($idPost, $moi)) { flash('Không cập nhật được trạng thái đơn.'); }
    else { flash('Đã cập nhật trạng thái đơn #' . $idPost . '.'); }
    chuyenHuong('don-hang.php?id=' . ($idPost ?? ''));
}
$don = $id !== null ? $khoDon->chiTiet($id, $nd['id'], $laQuanTri) : null;
if ($id !== null && $don === null) { require __DIR__ . '/404.php'; exit; }
$ds = $id === null ? $khoDon->danhSach($nd['id'], $laQuanTri) : [];
$tb = flash(); $tieuDe = $id ? 'Chi tiết đơn hàng' : 'Đơn hàng'; $trang = 'don-hang'; require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1><?= $laQuanTri ? 'Quản lý đơn hàng' : 'Đơn hàng của tôi' ?></h1>
<?php if ($tb): ?><p class="thong-bao-bieu-mau--thanh-cong" role="status"><?= e($tb) ?></p><?php endif; ?>
<?php if ($don !== null): ?>
<p><a href="don-hang.php">← Danh sách đơn</a></p><h2>Đơn #<?= e($don['id']) ?></h2>
<p>Khách hàng: <?= e($don['ho_ten']) ?> · <?= e($don['email']) ?><br>Trạng thái: <?= e($trangThai[$don['trang_thai']] ?? $don['trang_thai']) ?><br>Địa chỉ: <?= e($don['dia_chi']) ?></p>
<div class="bang-cuon"><table><thead><tr><th>Giáo trình</th><th>Đơn giá lúc đặt</th><th>Số lượng</th><th>Thành tiền</th></tr></thead><tbody><?php $tong = 0; foreach ($don['chi_tiet'] as $ct): $thanhTien = $ct['don_gia'] * $ct['so_luong']; $tong += $thanhTien; ?><tr><td><?= e($ct['ten']) ?></td><td><?= e(vnd((int) $ct['don_gia'])) ?></td><td><?= e($ct['so_luong']) ?></td><td><?= e(vnd((int) $thanhTien)) ?></td></tr><?php endforeach; ?></tbody></table></div><p><strong>Tổng tiền: <?= e(vnd((int) $tong)) ?></strong></p>
<?php if ($laQuanTri): ?><form action="don-hang.php" method="post"><input type="hidden" name="id" value="<?= e($don['id']) ?>"><label for="trang_thai">Trạng thái</label><select id="trang_thai" name="trang_thai"><?php foreach ($trangThai as $key => $nhan): ?><option value="<?= e($key) ?>" <?= $don['trang_thai'] === $key ? 'selected' : '' ?>><?= e($nhan) ?></option><?php endforeach; ?></select> <button class="nut nut--chinh" type="submit">Cập nhật</button></form><?php endif; ?>
<?php else: ?>
<?php if (!$ds): ?><p>Chưa có đơn hàng nào.</p><?php else: ?><div class="bang-cuon"><table><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th>Trạng thái</th><th>Tổng tiền</th></tr></thead><tbody><?php foreach ($ds as $d): ?><tr><td><a href="don-hang.php?id=<?= e($d['id']) ?>">#<?= e($d['id']) ?></a></td><td><?= e($d['ho_ten']) ?></td><td><?= e($d['ngay_tao']) ?></td><td><?= e($trangThai[$d['trang_thai']] ?? $d['trang_thai']) ?></td><td><?= e(vnd((int) $d['tong_tien'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php endif; ?></main><?php require __DIR__ . '/inc/footer.php'; ?>
