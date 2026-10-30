<?php
/** Tìm, lọc, sắp xếp và phân trang giáo trình bằng truy vấn PDO.
 * Mọi bộ lọc được giữ lại khi chuyển trang; JavaScript chỉ tăng cường tìm tức thời.
 */
require __DIR__ . '/inc/config.php';

$kho = khoGiaoTrinh();
$danhMuc = $kho->cacDanhMuc();
$idDanhMuc = array_map('intval', array_column($danhMuc, 'id'));
$q = chuoi($_GET, 'q');
if (mb_strlen($q) > 150) { $q = mb_substr($q, 0, 150); }
$dmRaw = chuoi($_GET, 'dm');
$dm = $dmRaw === '' ? null : soNguyen($dmRaw);
if ($dm !== null && !in_array($dm, $idDanhMuc, true)) { $dm = -1; }
$giaRaw = chuoi($_GET, 'gia');
$giaToiDa = $giaRaw === '' ? null : soNguyen($giaRaw, 1000, 1000000000);
$sx = chuoi($_GET, 'sx');
$sapXep = ['moi' => 'Mới nhất', 'ten' => 'Tên A-Z', 'gia-tang' => 'Giá tăng dần', 'gia-giam' => 'Giá giảm dần'];
if (!array_key_exists($sx, $sapXep)) { $sx = 'moi'; }
$loc = ['q' => $q, 'dm' => $dm, 'giaToiDa' => $giaToiDa];
$moiTrang = 8;
$tong = $kho->dem($loc);
$soTrang = max(1, (int) ceil($tong / $moiTrang));
$hienTai = soNguyen($_GET['trang'] ?? 1) ?? 1;
$hienTai = min($hienTai, $soTrang);
$danhSachThe = $kho->trang($loc, $sx, $moiTrang, ($hienTai - 1) * $moiTrang);

function lienKetTrang(int $so): string
{
    $thamSo = $_GET;
    $thamSo['trang'] = $so;
    return '?' . http_build_query($thamSo);
}

$tieuDe = 'Danh mục giáo trình'; $trang = 'danh-sach'; $jsThem = ['js/tim-kiem.js'];
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh" id="danh-sach-dong">
<h1>Danh mục giáo trình</h1>
<form action="danh-sach.php" method="get" class="bo-loc-giao-trinh" data-form-tim-kiem>
<div><label for="q">Từ khóa</label><input id="q" name="q" maxlength="150" value="<?= e($q) ?>" placeholder="Tên, mã hoặc tác giả" autocomplete="off"></div>
<div><label for="dm">Danh mục</label><select id="dm" name="dm"><option value="">Tất cả danh mục</option><?php foreach ($danhMuc as $d): ?><option value="<?= e($d['id']) ?>" <?= $dm === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['ten']) ?></option><?php endforeach; ?></select></div>
<div><label for="gia">Giá tối đa</label><input id="gia" name="gia" type="number" min="1000" step="1000" value="<?= e($giaToiDa) ?>"></div>
<div><label for="sx">Sắp xếp</label><select id="sx" name="sx"><?php foreach ($sapXep as $key => $label): ?><option value="<?= e($key) ?>" <?= $sx === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
<div><button class="nut nut--chinh" type="submit">Tìm kiếm</button> <a href="danh-sach.php">Xóa bộ lọc</a></div>
</form>
<section aria-live="polite" data-ket-qua-tim-kiem>
<h2>Kết quả tra cứu</h2><p>Tìm thấy <?= e($tong) ?> giáo trình.</p>
<?php if (!$danhSachThe): ?><p class="trang-thai-du-lieu">Không tìm thấy giáo trình phù hợp.</p><?php else: require __DIR__ . '/inc/danh-sach-the.php'; endif; ?>
</section>
<?php if ($soTrang > 1): ?><nav aria-label="Phân trang"><ul class="phan-trang">
<?php for ($so = 1; $so <= $soTrang; $so++): ?><li><a href="<?= e(lienKetTrang($so)) ?>" <?= $so === $hienTai ? 'aria-current="page"' : '' ?>><?= e($so) ?></a></li><?php endfor; ?>
</ul></nav><?php endif; ?>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>
