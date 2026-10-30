-- Tạo lại toàn bộ CSDL UEDBookHub từ đầu; chạy trước seed.sql.
SET NAMES utf8mb4;
DROP DATABASE IF EXISTS ltweb_nhom01;
CREATE DATABASE ltweb_nhom01 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ltweb_nhom01;

CREATE TABLE danh_muc (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_danh_muc_ten (ten),
    UNIQUE KEY uq_danh_muc_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE nguoi_dung (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL,
    ho_ten VARCHAR(100) NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    vai_tro ENUM('quan_tri', 'khach_hang') NOT NULL DEFAULT 'khach_hang',
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_nguoi_dung_email (email)
) ENGINE=InnoDB;

CREATE TABLE giao_trinh (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    danh_muc_id INT UNSIGNED NOT NULL,
    ma VARCHAR(30) NOT NULL,
    ten VARCHAR(200) NOT NULL,
    tac_gia VARCHAR(255) NOT NULL,
    nam_xuat_ban SMALLINT UNSIGNED NOT NULL,
    nha_xuat_ban VARCHAR(150) NOT NULL,
    gia INT UNSIGNED NOT NULL,
    gia_goc INT UNSIGNED NOT NULL,
    ton_kho INT UNSIGNED NOT NULL DEFAULT 0,
    so_trang SMALLINT UNSIGNED NOT NULL,
    anh VARCHAR(255) NULL,
    mo_ta TEXT NULL,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_giao_trinh_ma (ma),
    KEY idx_giao_trinh_danh_muc_gia (danh_muc_id, gia),
    CONSTRAINT fk_giao_trinh_danh_muc FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id),
    CONSTRAINT chk_giao_trinh_gia CHECK (gia >= 1000),
    CONSTRAINT chk_giao_trinh_gia_goc CHECK (gia_goc >= gia),
    CONSTRAINT chk_giao_trinh_nam CHECK (nam_xuat_ban BETWEEN 1900 AND 2100),
    CONSTRAINT chk_giao_trinh_so_trang CHECK (so_trang > 0)
) ENGINE=InnoDB;

CREATE TABLE don_hang (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nguoi_dung_id INT UNSIGNED NOT NULL,
    trang_thai ENUM('moi', 'dang_xu_ly', 'da_giao', 'da_huy') NOT NULL DEFAULT 'moi',
    dia_chi VARCHAR(255) NOT NULL,
    ghi_chu VARCHAR(1000) NULL,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_don_hang_nguoi_dung_ngay (nguoi_dung_id, ngay_tao),
    CONSTRAINT fk_don_hang_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id)
) ENGINE=InnoDB;

CREATE TABLE chi_tiet_don (
    don_hang_id INT UNSIGNED NOT NULL,
    giao_trinh_id INT UNSIGNED NOT NULL,
    so_luong SMALLINT UNSIGNED NOT NULL,
    don_gia INT UNSIGNED NOT NULL,
    PRIMARY KEY (don_hang_id, giao_trinh_id),
    CONSTRAINT fk_chi_tiet_don_don_hang FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
    CONSTRAINT fk_chi_tiet_don_giao_trinh FOREIGN KEY (giao_trinh_id) REFERENCES giao_trinh(id),
    CONSTRAINT chk_chi_tiet_don_so_luong CHECK (so_luong BETWEEN 1 AND 99),
    CONSTRAINT chk_chi_tiet_don_don_gia CHECK (don_gia >= 1000)
) ENGINE=InnoDB;

CREATE TABLE lien_he (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    giao_trinh_id INT UNSIGNED NOT NULL,
    ho_ten VARCHAR(70) NOT NULL,
    ma_sinh_vien VARCHAR(10) NOT NULL,
    email VARCHAR(254) NOT NULL,
    so_dien_thoai VARCHAR(10) NOT NULL,
    lop_hoc_phan VARCHAR(100) NULL,
    hinh_thuc ENUM('mua', 'muon') NOT NULL,
    so_luong TINYINT UNSIGNED NOT NULL,
    ngay_hen DATE NOT NULL,
    ghi_chu TEXT NULL,
    anh VARCHAR(255) NULL,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_lien_he_ngay_tao (ngay_tao),
    CONSTRAINT fk_lien_he_giao_trinh FOREIGN KEY (giao_trinh_id) REFERENCES giao_trinh(id),
    CONSTRAINT chk_lien_he_so_luong CHECK (so_luong BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- Bảng thành viên là đầu mối để Phần B bổ sung các bảng tv_ riêng về sau.
CREATE TABLE thanh_vien (
    mssv CHAR(10) PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    nguoi_dung_id INT UNSIGNED NULL UNIQUE,
    CONSTRAINT fk_thanh_vien_nguoi_dung FOREIGN KEY (nguoi_dung_id)
        REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Phần B: Sổ lưu bút theo MSSV thành viên (dành cho Bhling Trường chuyển từ tệp luubut.jsonl Bài 5)
CREATE TABLE tv_luu_but (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv CHAR(10) NOT NULL,
    ten_khach VARCHAR(50) NOT NULL,
    loi_nhan VARCHAR(500) NOT NULL,
    thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_tv_luu_but_mssv_thoi_gian (mssv, thoi_gian),
    CONSTRAINT fk_tv_luu_but_thanh_vien FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Phần B: Bộ đếm lượt xem theo ngày của Bhling Trường (Chuyển từ tệp views.txt Bài 5)
CREATE TABLE tv_truong_luot_xem (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv CHAR(10) NOT NULL,
    ngay DATE NOT NULL,
    so_luot INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY uq_tv_truong_luot_xem_ngay (mssv, ngay),
    CONSTRAINT fk_tv_truong_luot_xem_thanh_vien FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Phần B: Bảng điểm học phần cá nhân của Trần Văn Tú (MSSV: 3120224169) - Chuyển đổi & nâng cấp từ Máy tính điểm Bài 5
CREATE TABLE tv_tu_bang_diem (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv CHAR(10) NOT NULL,
    ten_hoc_phan VARCHAR(120) NOT NULL,
    so_tin_chi TINYINT UNSIGNED NOT NULL DEFAULT 3,
    a1 DECIMAL(3,1) NOT NULL,
    a2 DECIMAL(3,1) NOT NULL,
    a3 DECIMAL(3,1) NOT NULL,
    tong_ket DECIMAL(3,1) NOT NULL,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_tu_a1 CHECK (a1 BETWEEN 0.0 AND 10.0),
    CONSTRAINT chk_tu_a2 CHECK (a2 BETWEEN 0.0 AND 10.0),
    CONSTRAINT chk_tu_a3 CHECK (a3 BETWEEN 0.0 AND 10.0),
    CONSTRAINT chk_tu_tin_chi CHECK (so_tin_chi BETWEEN 1 AND 10),
    CONSTRAINT fk_tv_tu_bang_diem_thanh_vien FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Phần B: Thả tim & Đếm lượt thích cá nhân của Lê Trung Hậu (MSSV: 3120224052) - Lưu CSDL MySQL
CREATE TABLE tv_hau_luot_thich (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv CHAR(10) NOT NULL DEFAULT '3120224052',
    ip_dia_chi VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_tv_hau_luot_thich_mssv (mssv),
    CONSTRAINT fk_tv_hau_luot_thich_thanh_vien FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv) ON DELETE CASCADE
 ) ENGINE=InnoDB;

-- Phần B: Hệ thống câu chào theo thời gian thực của Lê Trung Hậu (MSSV: 3120224052) - Lưu CSDL MySQL
CREATE TABLE tv_hau_cau_chao (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv CHAR(10) NOT NULL DEFAULT '3120224052',
    buoi ENUM('sang', 'chieu', 'toi', 'khuya') NOT NULL,
    gio_bat_dau TINYINT UNSIGNED NOT NULL,
    gio_ket_thuc TINYINT UNSIGNED NOT NULL,
    bieu_tuong VARCHAR(20) NOT NULL DEFAULT '✨',
    tieu_de VARCHAR(100) NOT NULL,
    loi_chao VARCHAR(500) NOT NULL,
    kich_hoat TINYINT(1) NOT NULL DEFAULT 1,
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_tv_hau_cau_chao_gio (mssv, gio_bat_dau, gio_ket_thuc, kich_hoat),
    CONSTRAINT fk_tv_hau_cau_chao_thanh_vien FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Phần B: Bảng kỹ năng cá nhân của Nguyễn Tuấn Nhật Thanh (MSSV: 3120224132) - Chức năng lọc CSDL
CREATE TABLE tv_thanh_ky_nang (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv CHAR(10) NOT NULL,
    nhom VARCHAR(50) NOT NULL, -- laptrinh, csdl, kynangmem
    ten_ky_nang VARCHAR(100) NOT NULL,
    muc_do VARCHAR(50) NOT NULL DEFAULT 'Khá', -- Thành thạo, Khá, Cơ bản
    bieu_tuong VARCHAR(20) NOT NULL DEFAULT '⭐',
    CONSTRAINT fk_tv_thanh_ky_nang_thanh_vien FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Phần B: Bảng thời khóa biểu cá nhân của Nguyễn Tuấn Nhật Thanh (MSSV: 3120224132) - Chức năng tìm kiếm CSDL
CREATE TABLE tv_thanh_tkb (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv CHAR(10) NOT NULL,
    ma_hoc_phan VARCHAR(30) NOT NULL,
    ten_mon VARCHAR(150) NOT NULL,
    so_tin_chi TINYINT UNSIGNED NOT NULL DEFAULT 3,
    thu VARCHAR(20) NOT NULL,
    tiet_hoc VARCHAR(30) NOT NULL,
    phong_hoc VARCHAR(50) NOT NULL,
    giang_vien VARCHAR(100) NULL,
    CONSTRAINT fk_tv_thanh_tkb_thanh_vien FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB;
