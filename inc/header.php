<?php
/** Khung đầu trang dùng chung; hỗ trợ đường dẫn tương đối qua $goc.
 * Trang con có thể truyền $moTa và danh sách $cssThem từ thư mục gốc.
 */
$goc ??= '';
$trang ??= '';
$cssThem ??= [];
$dataGiaoDien ??= '';
$gioDauTrang = new App\Services\GioHang();
$nguoiDungDauTrang = nguoiDung();
$menu = ['index' => 'Trang chủ', 'danh-sach' => 'Danh mục giáo trình', 'gioi-thieu' => 'Giới thiệu nhóm', 'lien-he' => 'Đăng ký & Liên hệ'];
?>
<!DOCTYPE html>
<html lang="vi"<?= $dataGiaoDien !== '' ? ' data-giao-dien="' . e($dataGiaoDien) . '"' : '' ?>>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($moTa ?? 'UEDBookHub giúp tra cứu và đặt mua giáo trình, đăng ký học liệu trực tuyến.') ?>">
  <title><?= e($tieuDe ?? 'UEDBookHub') ?> | UEDBookHub</title>
  <link rel="stylesheet" href="<?= e($goc) ?>css/style.css">
  <link rel="stylesheet" href="<?= e($goc) ?>css/php.css">
  <?php foreach ($cssThem as $css): ?>
  <link rel="stylesheet" href="<?= e($goc . $css) ?>">
  <?php endforeach; ?>
</head>
<body class="trang">
<a class="bo-qua" href="#noi-dung">Đến nội dung chính</a>
<header class="dau-trang">
  <img class="dau-trang__bieu-trung" src="<?= e($goc) ?>images/UEDBookHub.svg" alt="UEDBookHub" width="320" height="80">
  <p class="dau-trang__khau-hieu">Hệ thống học liệu &amp; Thư viện số UED</p>
</header>
<nav class="thanh-dieu-huong" aria-label="Điều hướng chính">
  <ul class="thanh-dieu-huong__danh-sach">
    <?php foreach ($menu as $tep => $nhan): ?>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc . $tep) ?>.php" <?= $trang === $tep ? 'aria-current="page"' : '' ?>><?= e($nhan) ?></a></li>
    <?php endforeach; ?>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>gio-hang.php" <?= $trang === 'gio-hang' ? 'aria-current="page"' : '' ?>>Giỏ hàng (<span data-so-mon><?= e($gioDauTrang->soMon()) ?></span>)</a></li>
    <?php if ($nguoiDungDauTrang !== null): ?>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>don-hang.php" <?= $trang === 'don-hang' ? 'aria-current="page"' : '' ?>>Đơn hàng</a></li>
    <?php if ($nguoiDungDauTrang['vai_tro'] === 'quan_tri'): ?><li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>quan-tri.php" <?= $trang === 'quan-tri' ? 'aria-current="page"' : '' ?>>Quản trị</a></li><?php endif; ?>
    <li class="thanh-dieu-huong__muc"><span class="thanh-dieu-huong__lien-ket"><?= e($nguoiDungDauTrang['ho_ten']) ?></span></li>
    <li class="thanh-dieu-huong__muc"><form action="<?= e($goc) ?>dang-xuat.php" method="post"><button class="thanh-dieu-huong__lien-ket nut-dang-xuat" type="submit">Đăng xuất</button></form></li>
    <?php else: ?>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>dang-nhap.php" <?= $trang === 'dang-nhap' ? 'aria-current="page"' : '' ?>>Đăng nhập</a></li>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>dang-ky.php">Đăng ký</a></li>
    <?php endif; ?>
  </ul>
</nav>
<div id="noi-dung" tabindex="-1"></div>
