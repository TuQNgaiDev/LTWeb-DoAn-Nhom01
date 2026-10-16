<?php
/** Liệt kê phiếu đã nhận mới nhất trước, chỉ dành cho phiên đăng nhập. */
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/bao-ve.php';
$ds = (new App\Data\KhoLienHe(__DIR__ . '/storage/lien-he.jsonl'))->tatCa();
$tieuDe = 'Quản trị liên hệ'; $trang = 'quan-tri';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Quản trị liên hệ</h1><p>Đã nhận <?= e(count($ds)) ?> phiếu. Mới nhất hiển thị trước.</p>
<?php if (!$ds): ?><p>Chưa có liên hệ nào.</p><?php endif; ?>
<?php foreach ($ds as $i => $lh): ?>
<article class="phieu-lien-he"><h2>Phiếu <?= e(count($ds) - $i) ?> · <?= e($lh['ho_ten'] ?? '') ?></h2>
<dl><?php foreach (['thoiGian' => 'Thời gian', 'ma_sinh_vien' => 'Mã sinh viên', 'email_sv' => 'Email', 'so_dien_thoai' => 'Điện thoại', 'lop_hoc_phan' => 'Lớp', 'hinh_thuc' => 'Hình thức', 'chon_giao_trinh' => 'ID giáo trình', 'so_luong' => 'Số lượng', 'ngay_hen' => 'Ngày hẹn', 'ghi_chu' => 'Nội dung'] as $key => $label): ?><div><dt><?= e($label) ?></dt><dd class="giu-dong"><?= e($lh[$key] ?? '') ?></dd></div><?php endforeach; ?></dl>
<?php if (!empty($lh['anh']) && is_string($lh['anh']) && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $lh['anh'])): ?><p><a href="uploads/<?= e($lh['anh']) ?>">Xem ảnh đính kèm</a></p><img class="anh-dinh-kem" src="uploads/<?= e($lh['anh']) ?>" alt="Ảnh đính kèm phiếu <?= e(count($ds) - $i) ?>" width="320" loading="lazy"><?php endif; ?>
<?php if (!empty($lh['gioHang'])): ?><h3>Giỏ đặt mua khi gửi phiếu</h3><ul><?php foreach ($lh['gioHang'] as $muc): ?><li><?= e($muc['ten']) ?> · <?= e($muc['soLuong']) ?> × <?= e(vnd($muc['donGia'])) ?></li><?php endforeach; ?></ul><?php endif; ?>
</article><?php endforeach; ?></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
