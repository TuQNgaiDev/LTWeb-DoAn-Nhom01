<?php
/** Kiểm tra toàn bộ trường đăng ký và ảnh tùy chọn ở máy chủ.
 * Chỉ trả dữ liệu hợp lệ; controller lưu ảnh và JSONL sau khi hết lỗi.
 */
declare(strict_types=1);
namespace App\Services;
use App\Data\KhoGiaoTrinh;

final class KiemTraLienHe
{
    public function kiemTra(array $post, array $files, KhoGiaoTrinh $kho): array
    {
        $du = [];
        $loi = [];
        foreach (['ho_ten', 'ma_sinh_vien', 'email_sv', 'so_dien_thoai', 'lop_hoc_phan', 'hinh_thuc', 'chon_giao_trinh', 'so_luong', 'ngay_hen', 'ghi_chu', 'cam_ket'] as $key) {
            $giaTri = $post[$key] ?? '';
            // Kiểm tra chuỗi gốc trước trim: không bỏ qua NUL hoặc sửa ngầm UTF-8 lỗi.
            if (!is_string($giaTri) || !mb_check_encoding($giaTri, 'UTF-8') || str_contains($giaTri, "\0")) {
                $du[$key] = '';
                $loi[$key] = 'Dữ liệu không hợp lệ. Vui lòng nhập lại trường này.';
            } else {
                $du[$key] = trim($giaTri);
            }
        }
        if (mb_strlen($du['ho_ten']) < 3 || mb_strlen($du['ho_ten']) > 70) { $loi['ho_ten'] = 'Họ tên cần từ 3 đến 70 ký tự.'; }
        if (!preg_match('/^[0-9]{9,10}$/D', $du['ma_sinh_vien'])) { $loi['ma_sinh_vien'] = 'Mã sinh viên gồm 9 đến 10 chữ số.'; }
        if (!filter_var($du['email_sv'], FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($du['email_sv']), '@ued.udn.vn') || mb_strlen($du['email_sv']) > 254) { $loi['email_sv'] = 'Email không hợp lệ; cần dùng tên miền @ued.udn.vn.'; }
        if (!preg_match('/^0[35789][0-9]{8}$/D', $du['so_dien_thoai'])) { $loi['so_dien_thoai'] = 'Số điện thoại gồm 10 chữ số, đầu 03, 05, 07, 08 hoặc 09.'; }
        if (mb_strlen($du['lop_hoc_phan']) > 100) { $loi['lop_hoc_phan'] = 'Thông tin lớp không quá 100 ký tự.'; }
        if (!in_array($du['hinh_thuc'], ['mua', 'muon'], true)) { $loi['hinh_thuc'] = 'Chọn hình thức mua hoặc mượn.'; }
        $id = \soNguyen($du['chon_giao_trinh']);
        $s = $id !== null ? $kho->timTheoId($id) : null;
        if ($s === null) { $loi['chon_giao_trinh'] = 'Vui lòng chọn giáo trình có trong danh mục.'; }
        $sl = \soNguyen($du['so_luong'], 1, 5);
        if ($sl === null || ($s !== null && $sl > $s->soLuong)) { $loi['so_luong'] = 'Số lượng từ 1 đến 5 và không vượt số bản hiện có.'; }
        // Chỉ gọi bộ phân tích ngày sau khi chuỗi đã đúng định dạng YYYY-MM-DD.
        $ngay = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $du['ngay_hen']) === 1
            ? \DateTimeImmutable::createFromFormat('!Y-m-d', $du['ngay_hen'])
            : false;
        if (!$ngay || $ngay->format('Y-m-d') !== $du['ngay_hen'] || $du['ngay_hen'] < '2026-09-01' || $du['ngay_hen'] > '2027-01-31') { $loi['ngay_hen'] = 'Ngày hẹn hợp lệ từ 01/09/2026 đến 31/01/2027.'; }
        if (mb_strlen($du['ghi_chu']) > 2000) { $loi['ghi_chu'] = 'Ghi chú không quá 2000 ký tự.'; }
        if ($du['cam_ket'] !== '1') { $loi['cam_ket'] = 'Bạn cần xác nhận cam kết.'; }
        $anh = null;
        if (isset($files['anh'])) {
            $f = $files['anh'];
            if (!is_array($f) || !isset($f['error']) || !is_int($f['error'])) { $loi['anh'] = 'Dữ liệu tệp không hợp lệ.'; }
            elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
                if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) { $loi['anh'] = 'Ảnh không được lớn hơn 2 MB.'; }
                elseif ($f['error'] !== UPLOAD_ERR_OK) { $loi['anh'] = 'Tải ảnh chưa thành công, vui lòng chọn lại.'; }
                elseif (!isset($f['size'], $f['tmp_name']) || !is_int($f['size']) || !is_string($f['tmp_name']) || !is_uploaded_file($f['tmp_name'])) { $loi['anh'] = 'Dữ liệu tệp không hợp lệ.'; }
                elseif ($f['size'] > 2 * 1024 * 1024) { $loi['anh'] = 'Ảnh không được lớn hơn 2 MB.'; }
                else {
                    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
                    $duoi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                    if (!isset($duoi[$mime]) || @getimagesize($f['tmp_name']) === false) { $loi['anh'] = 'Chỉ nhận ảnh JPG, PNG, WebP đúng định dạng.'; }
                    else { $anh = ['tam' => $f['tmp_name'], 'ten' => bin2hex(random_bytes(16)) . '.' . $duoi[$mime]]; }
                }
            }
        }
        return [$du, $loi, $anh];
    }
}
