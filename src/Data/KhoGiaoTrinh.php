<?php
/** Nơi duy nhất đọc JSON giáo trình; cache trong một request.
 * Khi chuyển MySQL, giữ giao diện và thay phần đọc bằng PDO.
 */
declare(strict_types=1);
namespace App\Data;
use App\Models\GiaoTrinh;

final class KhoGiaoTrinh
{
    private ?array $ds = null;
    public function __construct(private string $tepJson) {}
    public function tatCa(): array
    {
        if ($this->ds === null) {
            if (!is_readable($this->tepJson)) { throw new \RuntimeException('Không đọc được tệp dữ liệu ' . $this->tepJson); }
            $noiDung = file_get_contents($this->tepJson);
            if ($noiDung === false) { throw new \RuntimeException('Đọc dữ liệu thất bại.'); }
            $mang = json_decode($noiDung, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($mang) || !array_is_list($mang)) { throw new \RuntimeException('Dữ liệu phải là danh sách.'); }
            $this->ds = array_map(fn(array $d): GiaoTrinh => GiaoTrinh::tuMang($d), $mang);
            $ids = array_map(fn(GiaoTrinh $s): int => $s->id, $this->ds);
            if (count($ids) !== count(array_unique($ids))) { throw new \RuntimeException('Trùng ID giáo trình.'); }
        }
        return $this->ds;
    }
    public function timTheoId(int $id): ?GiaoTrinh
    {
        foreach ($this->tatCa() as $s) { if ($s->id === $id) { return $s; } }
        return null;
    }
    public function cacKhoa(): array
    {
        $khoa = array_values(array_unique(array_map(fn(GiaoTrinh $s): string => $s->khoa, $this->tatCa())));
        sort($khoa);
        return $khoa;
    }
    public function timKiem(string $q, string $dm, string $sx): array
    {
        $ds = array_values(array_filter($this->tatCa(), fn(GiaoTrinh $s): bool =>
            ($dm === '' || $s->khoa === $dm) && ($q === '' || mb_stripos($s->ten . ' ' . $s->ma . ' ' . $s->tacGia, $q) !== false)));
        usort($ds, fn(GiaoTrinh $a, GiaoTrinh $b): int => match ($sx) {
            'gia-tang' => $a->gia <=> $b->gia, 'gia-giam' => $b->gia <=> $a->gia,
            'ten' => strcmp(mb_strtolower($a->ten), mb_strtolower($b->ten)), default => $a->id <=> $b->id
        });
        return $ds;
    }
}
