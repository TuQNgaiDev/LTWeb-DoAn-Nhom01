<?php
/** Gom truy vấn tài khoản: đăng ký, đăng nhập và đọc người dùng hiện tại.
 * Lớp không nhận vai trò từ biểu mẫu; tài khoản mới luôn là khách hàng.
 */
declare(strict_types=1);
namespace App\Data;

use PDO;

final class KhoNguoiDung
{
    public function __construct(private PDO $db) {}
    public function timTheoId(int $id): ?array
    {
        $st=$this->db->prepare('SELECT id, email, ho_ten, vai_tro FROM nguoi_dung WHERE id = ?');$st->execute([$id]);$d=$st->fetch();
        if($d===false){return null;}$d['id']=(int)$d['id'];return $d;
    }
    public function timTheoEmail(string $email): ?array
    {
        $st=$this->db->prepare('SELECT id, email, ho_ten, mat_khau, vai_tro FROM nguoi_dung WHERE email = ?');$st->execute([$email]);$d=$st->fetch();
        if($d===false){return null;}$d['id']=(int)$d['id'];return $d;
    }
    public function themKhach(string $email,string $hoTen,string $matKhauBam): int
    {
        $st=$this->db->prepare("INSERT INTO nguoi_dung (email, ho_ten, mat_khau, vai_tro) VALUES (?, ?, ?, 'khach_hang')");$st->execute([$email,$hoTen,$matKhauBam]);return (int)$this->db->lastInsertId();
    }
    public function doiMatKhau(int $id,string $matKhauBam): void
    {
        $st=$this->db->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');$st->execute([$matKhauBam,$id]);
    }
}
