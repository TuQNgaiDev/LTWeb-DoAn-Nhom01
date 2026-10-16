<?php
/** Thực thể giáo trình bất biến, tạo từ dữ liệu JSON đã kiểm tra.
 * Tên trường giữ theo bộ dữ liệu của bài tập 4.
 */
declare(strict_types=1);
namespace App\Models;

final class GiaoTrinh
{
    public function __construct(
        public readonly int $id, public readonly string $ma,
        public readonly string $ten, public readonly string $tacGia,
        public readonly string $khoa, public readonly int $namXuatBan,
        public readonly string $nhaXuatBan, public readonly int $gia,
        public readonly int $giaGoc, public readonly int $soLuong,
        public readonly int $soTrang, public readonly string $anh,
        public readonly string $moTa
    ) {}
    public static function tuMang(array $d): self
    {
        foreach (['id', 'namXuatBan', 'gia', 'giaGoc', 'soLuong', 'soTrang'] as $key) {
            if (!isset($d[$key]) || !is_int($d[$key]) || $d[$key] < 0) { throw new \RuntimeException('Trường số giáo trình không hợp lệ: ' . $key); }
        }
        foreach (['ma', 'ten', 'tacGia', 'khoa', 'nhaXuatBan', 'anh', 'moTa'] as $key) {
            if (!isset($d[$key]) || !is_string($d[$key])) { throw new \RuntimeException('Trường chữ giáo trình không hợp lệ: ' . $key); }
        }
        if ($d['id'] < 1 || !preg_match('~^images/[a-zA-Z0-9_.-]+$~D', $d['anh'])) { throw new \RuntimeException('ID hoặc ảnh giáo trình không hợp lệ.'); }
        return new self($d['id'], $d['ma'], $d['ten'], $d['tacGia'], $d['khoa'], $d['namXuatBan'], $d['nhaXuatBan'], $d['gia'], $d['giaGoc'], $d['soLuong'], $d['soTrang'], $d['anh'], $d['moTa']);
    }
}
