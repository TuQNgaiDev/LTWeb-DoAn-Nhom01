-- Chạy bằng tài khoản quản trị CSDL, thay mật khẩu giữ chỗ trước khi dùng.
CREATE USER IF NOT EXISTS 'nhom01_app'@'localhost' IDENTIFIED BY 'THAY_MAT_KHAU_MANH_O_DAY';
GRANT SELECT, INSERT, UPDATE, DELETE ON ltweb_nhom01.* TO 'nhom01_app'@'localhost';
FLUSH PRIVILEGES;
