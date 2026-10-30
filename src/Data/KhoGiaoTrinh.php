<?php
/** Gom mọi truy vấn của giáo trình; không ghép dữ liệu người dùng vào SQL.
 * Bộ lọc dùng chung cho COUNT và phân trang để tổng số dòng luôn nhất quán.
 */
declare(strict_types=1);
namespace App\Data;

use App\Models\GiaoTrinh;
use PDO;

final class KhoGiaoTrinh
{
    private const CHON = 'SELECT g.*, d.ten AS danh_muc FROM giao_trinh g JOIN danh_muc d ON d.id = g.danh_muc_id';

    public function __construct(private PDO $db) {}

    private function doiTuong(array $dong): GiaoTrinh { return GiaoTrinh::tuDong($dong); }

    public function tatCa(): array
    {
        return array_map([$this, 'doiTuong'], $this->db->query(self::CHON . ' ORDER BY g.id DESC')->fetchAll());
    }

    public function moiNhat(int $gioiHan = 3): array
    {
        $st = $this->db->prepare(self::CHON . ' ORDER BY g.ngay_tao DESC, g.id DESC LIMIT ?');
        $st->execute([$gioiHan]);
        return array_map([$this, 'doiTuong'], $st->fetchAll());
    }

    public function timTheoId(int $id): ?GiaoTrinh
    {
        $st = $this->db->prepare(self::CHON . ' WHERE g.id = ?');
        $st->execute([$id]);
        $dong = $st->fetch();
        return $dong === false ? null : $this->doiTuong($dong);
    }

    public function timNhieuTheoId(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, fn($id): bool => is_int($id) && $id > 0)));
        if ($ids === []) { return []; }
        $dauHoi = implode(',', array_fill(0, count($ids), '?'));
        $st = $this->db->prepare(self::CHON . " WHERE g.id IN ($dauHoi)");
        $st->execute($ids);
        $theoId = [];
        foreach ($st->fetchAll() as $dong) { $theoId[(int) $dong['id']] = $this->doiTuong($dong); }
        $ketQua = [];
        foreach ($ids as $id) { if (isset($theoId[$id])) { $ketQua[] = $theoId[$id]; } }
        return $ketQua;
    }

    public function cungDanhMuc(int $id, int $danhMucId, int $gioiHan = 4): array
    {
        $st = $this->db->prepare(self::CHON . ' WHERE g.danh_muc_id = ? AND g.id <> ? ORDER BY g.id DESC LIMIT ?');
        $st->execute([$danhMucId, $id, $gioiHan]);
        return array_map([$this, 'doiTuong'], $st->fetchAll());
    }

    public function cacDanhMuc(): array
    {
        return $this->db->query('SELECT id, ten FROM danh_muc ORDER BY ten')->fetchAll();
    }

    private function dieuKien(array $loc): array
    {
        $dk = []; $ts = [];
        if (($loc['q'] ?? '') !== '') {
            $dk[] = '(g.ten LIKE ? OR g.ma LIKE ? OR g.tac_gia LIKE ?)';
            $q = '%' . addcslashes((string) $loc['q'], '%_\\') . '%';
            array_push($ts, $q, $q, $q);
        }
        if (($loc['dm'] ?? null) !== null) { $dk[] = 'g.danh_muc_id = ?'; $ts[] = $loc['dm']; }
        if (($loc['giaToiDa'] ?? null) !== null) { $dk[] = 'g.gia <= ?'; $ts[] = $loc['giaToiDa']; }
        return [$dk === [] ? '' : ' WHERE ' . implode(' AND ', $dk), $ts];
    }

    public function dem(array $loc): int
    {
        [$where, $ts] = $this->dieuKien($loc);
        $st = $this->db->prepare('SELECT COUNT(*) FROM giao_trinh g' . $where);
        $st->execute($ts);
        return (int) $st->fetchColumn();
    }

    public function trang(array $loc, string $sx, int $soDong, int $boQua): array
    {
        [$where, $ts] = $this->dieuKien($loc);
        $orderBy = match ($sx) {
            'gia-tang' => 'g.gia ASC, g.id ASC', 'gia-giam' => 'g.gia DESC, g.id ASC',
            'ten' => 'g.ten ASC, g.id ASC', default => 'g.ngay_tao DESC, g.id DESC',
        };
        $st = $this->db->prepare(self::CHON . $where . " ORDER BY $orderBy LIMIT ? OFFSET ?");
        $st->execute([...$ts, $soDong, $boQua]);
        return array_map([$this, 'doiTuong'], $st->fetchAll());
    }

    public function them(array $du): int
    {
        $st = $this->db->prepare('INSERT INTO giao_trinh
            (danh_muc_id, ma, ten, tac_gia, nam_xuat_ban, nha_xuat_ban, gia, gia_goc, ton_kho, so_trang, anh, mo_ta)
            VALUES (:danh_muc_id, :ma, :ten, :tac_gia, :nam_xuat_ban, :nha_xuat_ban, :gia, :gia_goc, :ton_kho, :so_trang, :anh, :mo_ta)');
        $st->execute($du);
        return (int) $this->db->lastInsertId();
    }

    public function sua(int $id, array $du): void
    {
        $st = $this->db->prepare('UPDATE giao_trinh SET danh_muc_id=:danh_muc_id, ma=:ma, ten=:ten,
            tac_gia=:tac_gia, nam_xuat_ban=:nam_xuat_ban, nha_xuat_ban=:nha_xuat_ban,
            gia=:gia, gia_goc=:gia_goc, ton_kho=:ton_kho, so_trang=:so_trang, anh=:anh, mo_ta=:mo_ta WHERE id=:id');
        $st->execute($du + ['id' => $id]);
    }

    public function xoa(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM giao_trinh WHERE id = ?');
        $st->execute([$id]);
    }
}
