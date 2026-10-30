<?php
/** Thực hiện đặt hàng nguyên tử và các truy vấn danh sách/chi tiết đơn.
 * Tồn kho được trừ bằng UPDATE có điều kiện để chống bán vượt khi đồng thời.
 */
declare(strict_types=1);
namespace App\Data;

use PDO;
use RuntimeException;
use Throwable;

final class KhoDonHang
{
    public function __construct(private PDO $db) {}

    public function dat(int $nguoiDungId, array $gio, string $diaChi, string $ghiChu): int
    {
        $ids = array_map('intval', array_keys($gio));
        if ($ids === []) { throw new RuntimeException('Giỏ hàng đang trống.'); }
        $dauHoi = implode(',', array_fill(0, count($ids), '?'));
        $st = $this->db->prepare("SELECT id, gia FROM giao_trinh WHERE id IN ($dauHoi)");
        $st->execute($ids);
        $giaTheoId = [];
        foreach ($st->fetchAll() as $dong) { $giaTheoId[(int) $dong['id']] = (int) $dong['gia']; }
        if (count($giaTheoId) !== count($ids)) { throw new RuntimeException('Giỏ hàng có giáo trình không còn tồn tại.'); }

        $this->db->beginTransaction();
        try {
            $tao = $this->db->prepare('INSERT INTO don_hang (nguoi_dung_id, dia_chi, ghi_chu) VALUES (?, ?, ?)');
            $tao->execute([$nguoiDungId, $diaChi, $ghiChu === '' ? null : $ghiChu]);
            $donId = (int) $this->db->lastInsertId();
            $tru = $this->db->prepare('UPDATE giao_trinh SET ton_kho = ton_kho - ? WHERE id = ? AND ton_kho >= ?');
            $chiTiet = $this->db->prepare('INSERT INTO chi_tiet_don (don_hang_id, giao_trinh_id, so_luong, don_gia) VALUES (?, ?, ?, ?)');
            foreach ($gio as $idRaw => $slRaw) {
                $id = (int) $idRaw; $sl = (int) $slRaw;
                if ($sl < 1 || $sl > 99 || !isset($giaTheoId[$id])) { throw new RuntimeException('Giỏ hàng không hợp lệ.'); }
                $tru->execute([$sl, $id, $sl]);
                if ($tru->rowCount() !== 1) { throw new RuntimeException('Một giáo trình không đủ số lượng trong kho.'); }
                $chiTiet->execute([$donId, $id, $sl, $giaTheoId[$id]]);
            }
            $this->db->commit();
            return $donId;
        } catch (Throwable $loi) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $loi;
        }
    }

    public function danhSach(int $nguoiDungId, bool $laQuanTri): array
    {
        $where = $laQuanTri ? '' : ' WHERE dh.nguoi_dung_id = ?';
        $st = $this->db->prepare('SELECT dh.id, dh.trang_thai, dh.dia_chi, dh.ngay_tao,
                nd.ho_ten, nd.email, SUM(ct.so_luong * ct.don_gia) AS tong_tien
            FROM don_hang dh JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id
            JOIN chi_tiet_don ct ON ct.don_hang_id = dh.id' . $where . '
            GROUP BY dh.id, dh.trang_thai, dh.dia_chi, dh.ngay_tao, nd.ho_ten, nd.email
            ORDER BY dh.ngay_tao DESC, dh.id DESC');
        $st->execute($laQuanTri ? [] : [$nguoiDungId]);
        return $st->fetchAll();
    }

    public function chiTiet(int $id, int $nguoiDungId, bool $laQuanTri): ?array
    {
        $where = $laQuanTri ? 'dh.id = ?' : 'dh.id = ? AND dh.nguoi_dung_id = ?';
        $st = $this->db->prepare('SELECT dh.*, nd.ho_ten, nd.email FROM don_hang dh
            JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id WHERE ' . $where);
        $st->execute($laQuanTri ? [$id] : [$id, $nguoiDungId]);
        $don = $st->fetch();
        if ($don === false) { return null; }
        $ct = $this->db->prepare('SELECT ct.giao_trinh_id, g.ma, g.ten, ct.so_luong, ct.don_gia
            FROM chi_tiet_don ct JOIN giao_trinh g ON g.id = ct.giao_trinh_id
            WHERE ct.don_hang_id = ? ORDER BY g.ten');
        $ct->execute([$id]);
        $don['chi_tiet'] = $ct->fetchAll();
        return $don;
    }

    public function doiTrangThai(int $id, string $trangThai): bool
    {
        $st = $this->db->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
        $st->execute([$trangThai, $id]);
        if ($st->rowCount() === 1) { return true; }
        $kiemTra = $this->db->prepare('SELECT COUNT(*) FROM don_hang WHERE id = ?');
        $kiemTra->execute([$id]);
        return (int) $kiemTra->fetchColumn() === 1;
    }
}
