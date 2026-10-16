<?php
/** Danh mục kết xuất phía máy chủ; GET giữ bộ lọc trên URL. */
require __DIR__ . '/inc/config.php';
$kho = khoGiaoTrinh();
$cacKhoa = $kho->cacKhoa();
$q = chuoi($_GET, 'q');
$dm = chuoi($_GET, 'dm');
$sx = chuoi($_GET, 'sx');
$loi = [];
if (mb_strlen($q) > 150) { $loi[] = 'Từ khóa không quá 150 ký tự.'; $q = mb_substr($q, 0, 150); }
if ($dm !== '' && !in_array($dm, $cacKhoa, true)) { $loi[] = 'Khoa không hợp lệ.'; $dm = ''; }
$sapXep = ['' => 'Mặc định', 'ten' => 'Tên A–Z', 'gia-tang' => 'Giá tăng dần', 'gia-giam' => 'Giá giảm dần'];
if (!array_key_exists($sx, $sapXep)) { $loi[] = 'Kiểu sắp xếp không hợp lệ.'; $sx = ''; }
$danhSachThe = $kho->timKiem($q, $dm, $sx);
$tieuDe = 'Danh mục giáo trình'; $trang = 'danh-sach';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh" id="danh-sach-dong">
<h1>Danh mục giáo trình</h1>
<?php foreach ($loi as $l): ?><p class="bieu-mau__loi" role="alert"><?= e($l) ?></p><?php endforeach; ?>
<form action="danh-sach.php" method="get" class="bo-loc-giao-trinh">
<div><label for="q">Từ khóa</label><input id="q" name="q" maxlength="150" value="<?= e($q) ?>" placeholder="Tên, mã hoặc tác giả"></div>
<div><label for="dm">Khoa chuyên môn</label><select id="dm" name="dm"><option value="">Tất cả khoa</option><?php foreach ($cacKhoa as $k): ?><option value="<?= e($k) ?>" <?= $dm === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
<div><label for="sx">Sắp xếp</label><select id="sx" name="sx"><?php foreach ($sapXep as $key => $label): ?><option value="<?= e($key) ?>" <?= $sx === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
<div><button class="nut nut--chinh" type="submit">Tìm kiếm</button> <a href="danh-sach.php">Xóa bộ lọc</a></div>
</form>
<h2>Kết quả tra cứu</h2><p>Tìm thấy <?= e(count($danhSachThe)) ?> giáo trình.</p>
<?php if (!$danhSachThe): ?><p class="trang-thai-du-lieu">Không tìm thấy giáo trình phù hợp.</p><?php else: require __DIR__ . '/inc/danh-sach-the.php'; endif; ?>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>
