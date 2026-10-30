<?php
/** Đọc người dùng hiện tại từ uid trong session và kiểm tra quyền ở máy chủ.
 * Session không giữ vai trò để thay đổi quyền trong CSDL có hiệu lực ngay.
 */
declare(strict_types=1);

function nguoiDung(): ?array
{
    static $daDoc = false;
    static $nguoiDung = null;
    if ($daDoc) {
        return $nguoiDung;
    }
    $daDoc = true;
    $uid = soNguyen($_SESSION['uid'] ?? null);
    if ($uid === null) {
        return null;
    }
    $dong = (new App\Data\KhoNguoiDung(db()))->timTheoId($uid);
    if ($dong === null) {
        unset($_SESSION['uid']);
        return null;
    }
    return $nguoiDung = $dong;
}

function yeuCauDangNhap(): array
{
    $nd = nguoiDung();
    if ($nd === null) {
        $_SESSION['sau_dang_nhap'] = $_SERVER['REQUEST_URI'] ?? '/index.php';
        chuyenHuong('dang-nhap.php');
    }
    return $nd;
}

function yeuCauQuyen(string $vaiTro): array
{
    $nd = yeuCauDangNhap();
    if ($nd['vai_tro'] !== $vaiTro) {
        http_response_code(403);
        require __DIR__ . '/../403.php';
        exit;
    }
    return $nd;
}
