<?php
/** Kiểm tra dữ liệu giáo trình dùng chung cho trang thêm và sửa.
 * Kết quả có đúng các khóa tham số của câu INSERT/UPDATE trong KhoGiaoTrinh.
 */
declare(strict_types=1);

function kiemTraGiaoTrinh(array $vao, array $idDanhMuc, string $anhCu = 'images/BiaSachGiaoDucHoc.svg'): array
{
    $du = [
        'danh_muc_id' => soNguyen($vao['danh_muc_id'] ?? null),
        'ma' => chuoi($vao, 'ma'), 'ten' => chuoi($vao, 'ten'), 'tac_gia' => chuoi($vao, 'tac_gia'),
        'nam_xuat_ban' => soNguyen($vao['nam_xuat_ban'] ?? null, 1900, 2100),
        'nha_xuat_ban' => chuoi($vao, 'nha_xuat_ban'),
        'gia' => soNguyen($vao['gia'] ?? null, 1000, 1000000000),
        'gia_goc' => soNguyen($vao['gia_goc'] ?? null, 1000, 1000000000),
        'ton_kho' => soNguyen($vao['ton_kho'] ?? null, 0, 999999),
        'so_trang' => soNguyen($vao['so_trang'] ?? null, 1, 10000),
        'anh' => $anhCu, 'mo_ta' => chuoi($vao, 'mo_ta'),
    ];
    $loi = [];
    if (!in_array($du['danh_muc_id'], $idDanhMuc, true)) { $loi['danh_muc_id'] = 'Chọn một danh mục có sẵn.'; }
    if (!preg_match('/^[A-Z0-9-]{3,30}$/D', $du['ma'])) { $loi['ma'] = 'Mã gồm 3-30 chữ hoa, số hoặc dấu gạch ngang.'; }
    foreach (['ten' => [2, 200, 'Tên'], 'tac_gia' => [2, 255, 'Tác giả'], 'nha_xuat_ban' => [2, 150, 'Nhà xuất bản']] as $key => [$min, $max, $nhan]) {
        $n = mb_strlen($du[$key]); if ($n < $min || $n > $max) { $loi[$key] = "$nhan dài từ $min đến $max ký tự."; }
    }
    foreach (['nam_xuat_ban' => 'Năm xuất bản từ 1900 đến 2100.', 'gia' => 'Giá là số nguyên từ 1.000.', 'gia_goc' => 'Giá gốc là số nguyên từ 1.000.', 'ton_kho' => 'Tồn kho từ 0 đến 999999.', 'so_trang' => 'Số trang từ 1 đến 10000.'] as $key => $tb) {
        if ($du[$key] === null) { $loi[$key] = $tb; }
    }
    if ($du['gia'] !== null && $du['gia_goc'] !== null && $du['gia_goc'] < $du['gia']) { $loi['gia_goc'] = 'Giá gốc không được thấp hơn giá bán.'; }
    if (mb_strlen($du['mo_ta']) > 2000) { $loi['mo_ta'] = 'Mô tả không quá 2000 ký tự.'; }
    return [$du, $loi];
}

function kiemTraSanPham(array $vao, array $idDanhMuc, string $anhCu = 'images/BiaSachGiaoDucHoc.svg'): array
{
    return kiemTraGiaoTrinh($vao, $idDanhMuc, $anhCu);
}

function nhanAnhTaiLen(array $files, array &$loi): ?array
{
    if (!isset($files['anh']) || !is_array($files['anh']) || ($files['anh']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) { return null; }
    $f = $files['anh'];
    if (($f['error'] ?? null) !== UPLOAD_ERR_OK || !isset($f['tmp_name'], $f['size']) || !is_uploaded_file($f['tmp_name'])) { $loi['anh'] = 'Tải ảnh chưa thành công.'; return null; }
    if ((int) $f['size'] > 2 * 1024 * 1024) { $loi['anh'] = 'Ảnh không quá 2 MB.'; return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $duoi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($duoi[$mime]) || @getimagesize($f['tmp_name']) === false) { $loi['anh'] = 'Chỉ nhận ảnh JPG, PNG hoặc WebP đúng định dạng.'; return null; }
    return ['tam' => $f['tmp_name'], 'ten' => bin2hex(random_bytes(16)) . '.' . $duoi[$mime]];
}
