<?php
/** Thực thể giáo trình bất biến tạo từ một dòng truy vấn CSDL.
 * JsonSerializable chỉ công khai các trường được phép trả qua REST API.
 */
declare(strict_types=1);
namespace App\Models;

use JsonSerializable;

final class GiaoTrinh implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $ma,
        public readonly string $ten,
        public readonly string $tacGia,
        public readonly string $khoa,
        public readonly int $danhMucId,
        public readonly int $namXuatBan,
        public readonly string $nhaXuatBan,
        public readonly int $gia,
        public readonly int $giaGoc,
        public readonly int $soLuong,
        public readonly int $soTrang,
        public readonly string $anh,
        public readonly string $moTa
    ) {}

    public static function tuDong(array $d): self
    {
        return new self(
            (int) $d['id'], (string) $d['ma'], (string) $d['ten'],
            (string) $d['tac_gia'], (string) $d['danh_muc'], (int) $d['danh_muc_id'],
            (int) $d['nam_xuat_ban'], (string) $d['nha_xuat_ban'],
            (int) $d['gia'], (int) $d['gia_goc'], (int) $d['ton_kho'],
            (int) $d['so_trang'], (string) ($d['anh'] ?? ''), (string) ($d['mo_ta'] ?? '')
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id, 'ma' => $this->ma, 'ten' => $this->ten,
            'tac_gia' => $this->tacGia, 'danh_muc' => $this->khoa,
            'gia' => $this->gia, 'ton_kho' => $this->soLuong,
            'anh' => $this->anh, 'mo_ta' => $this->moTa,
        ];
    }
}
