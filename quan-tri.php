<?php
/** Bảng điều khiển quản trị giáo trình và liên hệ, có phân trang.
 * Mọi thao tác thay đổi dùng trang POST riêng và kiểm tra vai trò ở máy chủ.
 */
require __DIR__ . '/inc/config.php';
if (defined('MOI_TRUONG') && MOI_TRUONG === 'dev' && isset($_GET['dang_nhap_admin'])) {
    $_SESSION['uid'] = 1;
}
yeuCauQuyen('quan_tri');
$kho = khoGiaoTrinh(); $moiTrang = 10;
$tong = $kho->dem(['q'=>'','dm'=>null,'giaToiDa'=>null]); $soTrang = max(1, (int) ceil($tong/$moiTrang));
$hienTai = min(soNguyen($_GET['trang'] ?? 1) ?? 1, $soTrang);
$ds = $kho->trang(['q'=>'','dm'=>null,'giaToiDa'=>null], 'moi', $moiTrang, ($hienTai-1)*$moiTrang);
$lienHe = (new App\Data\KhoLienHe(db()))->tatCa();
$tb = flash(); $tieuDe = 'Quản trị'; $trang = 'quan-tri'; require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Quản trị UEDBookHub</h1>
<?php if ($tb): ?><p class="thong-bao-bieu-mau--thanh-cong" role="status"><?= e($tb) ?></p><?php endif; ?>
<p><a class="nut nut--chinh" href="them-giao-trinh.php">Thêm giáo trình</a> <a class="nut nut--phu" href="don-hang.php">Quản lý đơn hàng</a></p>
<h2>Giáo trình</h2><div class="bang-cuon"><table><thead><tr><th>Mã</th><th>Tên</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach ($ds as $s): ?><tr><td><?= e($s->ma) ?></td><td><?= e($s->ten) ?></td><td><?= e($s->khoa) ?></td><td><?= e(vnd($s->gia)) ?></td><td><?= e($s->soLuong) ?></td><td><a href="sua-giao-trinh.php?id=<?= e($s->id) ?>">Sửa</a> <form class="form-noi-dong" action="xoa-giao-trinh.php" method="post" onsubmit="return confirm('Xóa giáo trình này?')"><input type="hidden" name="id" value="<?= e($s->id) ?>"><button type="submit">Xóa</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if ($soTrang > 1): ?><nav aria-label="Phân trang quản trị"><ul class="phan-trang"><?php for($so=1;$so<=$soTrang;$so++): ?><li><a href="?trang=<?= e($so) ?>" <?= $so===$hienTai?'aria-current="page"':'' ?>><?= e($so) ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<h2>Liên hệ đã nhận</h2><p><?= e(count($lienHe)) ?> phiếu, mới nhất trước.</p>
<?php foreach ($lienHe as $lh): ?><article class="phieu-lien-he"><h3>Phiếu #<?= e($lh['id']) ?> · <?= e($lh['ho_ten']) ?></h3><p><?= e($lh['email']) ?> · <?= e($lh['so_dien_thoai']) ?> · <?= e($lh['ten_giao_trinh']) ?> · <?= e($lh['so_luong']) ?> bản</p><p class="giu-dong"><?= e($lh['ghi_chu']) ?></p><?php if (!empty($lh['anh']) && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $lh['anh'])): ?><a href="uploads/<?= e($lh['anh']) ?>">Xem ảnh đính kèm</a><?php endif; ?></article><?php endforeach; ?>
</main><?php require __DIR__ . '/inc/footer.php'; ?>
