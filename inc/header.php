<?php
/** Khung đầu trang dùng chung; hỗ trợ đường dẫn tương đối qua $goc.
 * Trang con có thể truyền $moTa và danh sách $cssThem từ thư mục gốc.
 */
$goc ??= '';
$trang ??= '';
$cssThem ??= [];
$dataGiaoDien ??= '';
$gioDauTrang = new App\Services\GioHang();
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
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>gio-hang.php" <?= $trang === 'gio-hang' ? 'aria-current="page"' : '' ?>>Giỏ hàng (<?= e($gioDauTrang->soMon()) ?>)</a></li>
    <?php if (isset($_SESSION['user'])): ?>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>quan-tri.php" <?= $trang === 'quan-tri' ? 'aria-current="page"' : '' ?>>Quản trị · <?= e($_SESSION['user']) ?></a></li>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>dang-xuat.php">Đăng xuất</a></li>
    <?php else: ?>
    <li class="thanh-dieu-huong__muc"><a class="thanh-dieu-huong__lien-ket" href="<?= e($goc) ?>dang-nhap.php" <?= $trang === 'dang-nhap' ? 'aria-current="page"' : '' ?>>Đăng nhập</a></li>
    <?php endif; ?>
  </ul>
</nav>
<div id="noi-dung" tabindex="-1"></div>
