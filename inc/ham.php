<?php
/** Hàm dùng chung: escape HTML, kiểm tra dữ liệu và chuyển hướng PRG.
 * Mọi chuỗi không tin cậy phải được kiểm tra trước khi sử dụng.
 */
declare(strict_types=1);

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function vnd(int $value): string { return number_format($value, 0, ',', '.') . ' đ'; }
function chuoi(array $nguon, string $khoa): string
{
    return isset($nguon[$khoa]) && is_string($nguon[$khoa]) ? trim($nguon[$khoa]) : '';
}
function soNguyen(mixed $value, int $min = 1, int $max = PHP_INT_MAX): ?int
{
    if (!is_string($value) && !is_int($value)) { return null; }
    $so = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
    return $so === false ? null : $so;
}
function chuyenHuong(string $url): never
{
    header('Location: ' . $url, true, 302);
    exit;
}
function flash(?string $noiDung = null): string
{
    if ($noiDung !== null) { $_SESSION['flash'] = $noiDung; return ''; }
    $tb = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    return is_string($tb) ? $tb : '';
}
function khoGiaoTrinh(): App\Data\KhoGiaoTrinh
{
    return new App\Data\KhoGiaoTrinh(__DIR__ . '/../data/giao-trinh.json');
}
function daXem(App\Data\KhoGiaoTrinh $kho): array
{
    $ketQua = [];
    foreach (explode(',', substr(chuoi($_COOKIE, 'da_xem'), 0, 200)) as $x) {
        $id = soNguyen($x);
        $sp = $id !== null ? $kho->timTheoId($id) : null;
        if ($sp !== null) { $ketQua[$id] = $sp; }
        if (count($ketQua) === 4) { break; }
    }
    return array_values($ketQua);
}
