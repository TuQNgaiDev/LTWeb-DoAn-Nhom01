<?php
/** Giỏ đặt mua giáo trình, bọc session và lấy giá từ kho dữ liệu.
 * Kiểm tra cả số lượng mới lẫn tổng sau khi thêm; không nhận giá từ form.
 */
declare(strict_types=1);
namespace App\Services;
use App\Data\KhoGiaoTrinh;

final class GioHang
{
    public function __construct() { $_SESSION['gio'] ??= []; }
    public function cacMuc(): array { return $_SESSION['gio']; }
    private function kiemTra(int $id, int $sl, KhoGiaoTrinh $kho): void
    {
        $s = $kho->timTheoId($id);
        if ($s === null || $sl < 1 || $sl > 99 || $sl > $s->soLuong) {
            throw new \InvalidArgumentException('Giáo trình hoặc số lượng không hợp lệ; tối đa 99 và không vượt số bản hiện có.');
        }
    }
    public function them(int $id, int $sl, KhoGiaoTrinh $kho): void
    {
        $this->kiemTra($id, $sl, $kho);
        $tong = ($_SESSION['gio'][$id] ?? 0) + $sl;
        $this->kiemTra($id, $tong, $kho);
        $_SESSION['gio'][$id] = $tong;
    }
    public function capNhat(int $id, int $sl, KhoGiaoTrinh $kho): void
    {
        $this->kiemTra($id, $sl, $kho);
        if (!isset($_SESSION['gio'][$id])) { throw new \InvalidArgumentException('Mục này chưa có trong giỏ.'); }
        $_SESSION['gio'][$id] = $sl;
    }
    public function xoa(int $id): void { unset($_SESSION['gio'][$id]); }
    public function xoaHet(): void { $_SESSION['gio'] = []; }
    public function soMon(): int { return array_sum($_SESSION['gio']); }
    public function tongTien(KhoGiaoTrinh $kho): int
    {
        $tong = 0;
        foreach ($this->cacMuc() as $id => $sl) { $tong += ($kho->timTheoId((int) $id)?->gia ?? 0) * $sl; }
        return $tong;
    }
}
