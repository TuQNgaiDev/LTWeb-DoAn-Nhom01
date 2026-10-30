<?php
/** Chi tiết theo ID đã kiểm tra; cookie bốn mục mới nhất trước output. */
require __DIR__ . '/inc/config.php';
$kho = khoGiaoTrinh();
$id = soNguyen($_GET['id'] ?? null);
$s = $id !== null ? $kho->timTheoId($id) : null;
if ($s === null) { require __DIR__ . '/404.php'; exit; }
$cungDanhMuc = $kho->cungDanhMuc($s->id, $s->danhMucId, 4);
$cu = array_map(fn(App\Models\GiaoTrinh $x): int => $x->id, daXem($kho));
$moi = array_slice(array_unique([$s->id, ...$cu]), 0, 4);
setcookie('da_xem', implode(',', $moi), ['expires' => time() + 30 * 86400, 'path' => $duongDanCookie, 'httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
$tieuDe = $s->ten; $trang = 'danh-sach'; $jsThem = ['js/gio-hang.js'];
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh">
<p><a href="danh-sach.php">← Danh mục giáo trình</a></p>
<h1><?= e($s->ten) ?></h1>
<div class="chi-tiet-php"><img src="<?= e($s->anh) ?>" alt="Bìa <?= e($s->ten) ?>" width="240" height="300">
<div><p><?= e($s->moTa) ?></p><dl class="chi-tiet-dong__thong-tin">
<?php foreach (['Mã giáo trình' => $s->ma, 'Tác giả' => $s->tacGia, 'Khoa' => $s->khoa, 'Nhà xuất bản' => $s->nhaXuatBan, 'Năm xuất bản' => $s->namXuatBan, 'Số trang' => $s->soTrang, 'Số bản hiện có' => $s->soLuong] as $nhan => $giaTri): ?>
<div><dt><?= e($nhan) ?></dt><dd><?= e($giaTri) ?></dd></div><?php endforeach; ?></dl>
<p>Giá đặt mua: <strong><?= e(vnd($s->gia)) ?></strong></p>
<form action="gio-hang.php" method="post" class="bieu-mau" data-them-gio><input type="hidden" name="hanh_dong" value="them"><input type="hidden" name="id" value="<?= e($s->id) ?>">
<label for="sl">Số lượng đặt mua</label><input id="sl" type="number" name="so_luong" value="1" min="1" max="<?= e(min(99, $s->soLuong)) ?>" required>
<button class="nut nut--chinh" type="submit" <?= $s->soLuong === 0 ? 'disabled' : '' ?>>Thêm vào giỏ</button></form>
<p><button hidden class="nut nut--phu" type="button" data-hanh-dong="yeu-thich" data-id="<?= e($s->id) ?>">Thêm yêu thích</button> <a href="lien-he.php?giao-trinh=<?= e($s->id) ?>">Gửi yêu cầu về giáo trình</a></p>
</div></div>
<section><h2>Giáo trình cùng danh mục</h2><?php if (!$cungDanhMuc): ?><p>Chưa có giáo trình liên quan.</p><?php else: $danhSachThe = $cungDanhMuc; require __DIR__ . '/inc/danh-sach-the.php'; endif; ?></section>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>
