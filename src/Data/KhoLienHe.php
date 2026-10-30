<?php
/** Lưu và đọc liên hệ trong MySQL bằng prepared statement.
 * Ảnh tiếp tục nằm trong uploads/, CSDL chỉ lưu tên tệp an toàn.
 */
declare(strict_types=1);
namespace App\Data;

use PDO;

final class KhoLienHe
{
    public function __construct(private PDO $db) {}

    public function them(array $du): int
    {
        $st = $this->db->prepare('INSERT INTO lien_he
            (ho_ten, ma_sinh_vien, email, so_dien_thoai, lop_hoc_phan, hinh_thuc,
             giao_trinh_id, so_luong, ngay_hen, ghi_chu, anh)
            VALUES (:ho_ten, :ma_sinh_vien, :email_sv, :so_dien_thoai, :lop_hoc_phan,
                    :hinh_thuc, :chon_giao_trinh, :so_luong, :ngay_hen, :ghi_chu, :anh)');
        $st->execute($du);
        return (int) $this->db->lastInsertId();
    }

    public function tatCa(): array
    {
        return $this->db->query('SELECT l.*, g.ten AS ten_giao_trinh
            FROM lien_he l JOIN giao_trinh g ON g.id = l.giao_trinh_id
            ORDER BY l.ngay_tao DESC, l.id DESC')->fetchAll();
    }
}
