# UEDBookHub - Bài tập nhóm 7, Phần A

Website PHP thuần của Nhóm 01 dùng MySQL qua PDO để quản lý giáo trình, tài khoản, liên hệ và đơn hàng. Phiên bản Chương 7 bổ sung CSRF, CSP, session an toàn, `.env`, phân quyền CSDL tối thiểu, chống dò mật khẩu và nhật ký bảo mật.

## Cài đặt và chạy

Cần PHP 8.1 trở lên (bật `pdo_mysql`, `mbstring`, `fileinfo`), MySQL 8 hoặc MariaDB 10.4 trở lên và Composer 2. Cấu hình mẫu trong `.env.example` dùng máy chủ `127.0.0.1:3306`, tài khoản `root`, mật khẩu rỗng như XAMPP.

1. Cài thư viện, sao chép cấu hình cục bộ (không commit `.env`):

   ```sh
   composer install
   cp .env.example .env
   ```

2. Tạo lại CSDL từ đầu, rồi nạp dữ liệu mẫu:

   ```sh
   mysql -u root < database/schema.sql
   mysql -u root ltweb_nhom01 < database/seed.sql
   ```

   Trong phpMyAdmin, có thể Import `database/schema.sql` trước rồi Import `database/seed.sql`.

3. Chạy website và mở <http://localhost:8000>:

   ```sh
   php -S localhost:8000
   ```

Với XAMPP, chép kho vào `htdocs/ltweb-doan-nhom01`, thực hiện hai bước import rồi mở `http://localhost/ltweb-doan-nhom01/`. PHP cần quyền ghi `logs/` và `uploads/`. Cấu hình kết nối nằm trong `.env`; đặt `APP_DEBUG=false` khi trình diễn/triển khai. Khi triển khai, chạy `database/tai-khoan.sql`, thay mật khẩu giữ chỗ và dùng `DB_USERNAME=nhom01_app` để ứng dụng chỉ có quyền cần thiết.

## Biện pháp bảo mật Chương 7

| Biện pháp | Tệp chính | Slide |
|---|---|---:|
| CSRF cho mọi POST form và fetch | `inc/csrf.php`, `inc/config.php`, `inc/header.php` | 23–24 |
| Session/cookie an toàn, hết hạn 30 phút | `inc/config.php`, `inc/xac-thuc.php` | 25 |
| CSP và security headers, không inline | `inc/config.php`, các tệp CSS/JS ngoài | 22, 26–27 |
| Bí mật trong `.env` | `.env.example`, `inc/db.php` | 29 |
| Chặn truy cập tệp/thư mục nhạy cảm | `.htaccess`, `uploads/.htaccess` | 28, 30 |
| Tài khoản CSDL quyền tối thiểu | `database/tai-khoan.sql` | 20 |
| Khóa 5 lần sai/15 phút và ghi log | `dang-nhap.php`, `dang_nhap_sai`, `logs/bao-mat.log` | 31–32 |

Nội dung tương ứng các slide 22–32 của `Slide_Chuong7_BaoMat.pptx`. Không có URL đăng nhập nhanh hoặc cơ chế đổi quyền qua tham số GET.

## Tài khoản thử

Mật khẩu chung: **matkhau123**.

| Email | Vai trò | Dữ liệu mẫu |
|---|---|---|
| `admin@ued.vn` | `quan_tri` | Quản trị giáo trình, liên hệ và mọi đơn |
| `lan@ued.vn` | `khach_hang` | Có một đơn mẫu |
| `an@ued.vn` | `khach_hang` | Có một đơn mẫu |

## Chức năng - URL - tệp PHP

| Chức năng | URL | Tệp chính |
|---|---|---|
| Trang chủ, giáo trình mới, đã xem gần đây | `index.php` | `index.php`, `src/Data/KhoGiaoTrinh.php` |
| Tìm, lọc danh mục/giá, sắp xếp, phân trang | `danh-sach.php?q=giao&dm=2&gia=100000&sx=gia-tang` | `danh-sach.php`, `js/tim-kiem.js` |
| Chi tiết, cùng danh mục, thêm giỏ | `chi-tiet.php?id=1` | `chi-tiet.php`, `js/gio-hang.js` |
| Xem và cập nhật giỏ session | `gio-hang.php` | `gio-hang.php`, `src/Services/GioHang.php` |
| Liên hệ và ảnh đính kèm | `lien-he.php` | `lien-he.php`, `src/Data/KhoLienHe.php` |
| Đăng ký, đăng nhập, đăng xuất | `dang-ky.php`, `dang-nhap.php` | `inc/xac-thuc.php` |
| CRUD giáo trình, xem liên hệ | `quan-tri.php` | `them-giao-trinh.php`, `sua-giao-trinh.php`, `xoa-giao-trinh.php` |
| Đặt hàng trong giao dịch | `dat-hang.php` | `src/Data/KhoDonHang.php` |
| Đơn của tôi; quản trị mọi đơn/trạng thái | `don-hang.php` | `don-hang.php`, `src/Data/KhoDonHang.php` |
| Trang cá nhân Trần Văn Tú: Quản lý bảng điểm GPA & Thống kê đồ án CSDL | `thanhvien/3120224169_tu/gioithieu.php` | `tv_tu_bang_diem` |
| Trang cá nhân Nguyễn Tuấn Nhật Thanh: Lọc kỹ năng & Tìm kiếm thời khóa biểu CSDL | `thanhvien/3120224132_thanh/gioithieu.php` | `tv_thanh_ky_nang`, `tv_thanh_tkb` |
| Trang cá nhân Bhling Trường: Sổ lưu bút phân trang & Thống kê lượt xem CSDL | `thanhvien/3120124040_truong/gioithieu.php` | `tv_luu_but`, `tv_truong_luot_xem` |
| Trang cá nhân Lê Trung Hậu: Thả tim & Đếm lượt thích và Câu chào theo thời gian thực CSDL | `thanhvien/3120224052_hau/gioithieu.php` | `tv_hau_luot_thich`, `tv_hau_cau_chao` |

Các thao tác thêm, sửa, xóa và đổi trạng thái dùng POST. Trang quản trị kiểm tra vai trò ở máy chủ; truy vấn chi tiết đơn của khách luôn kèm `nguoi_dung_id`, nên ID đơn của người khác trả 404.

## REST API

| Phương thức | Endpoint | Kết quả |
|---|---|---|
| GET | `/api/giao-trinh.php?q=giao` | Tối đa 10 giáo trình công khai |
| GET | `/api/giao-trinh.php?id=1` | Một giáo trình hoặc 400/404 |
| GET | `/api/gio-hang.php` | Giỏ, tổng số món và tổng tiền |
| POST | `/api/gio-hang.php` với `{"id":1,"sl":2}` | Thêm vào giỏ hoặc 400/404/422 |

Mở `api.http` bằng REST Client để chạy các request mẫu và trường hợp lỗi. Trước POST API, lấy cookie và token từ trang có thẻ meta CSRF rồi gửi header `X-CSRF-Token`. API trả thêm mã 403 cho token sai/thiếu.

## CSDL và quy tắc chính

`database/schema.sql` xóa và tạo lại `ltweb_nhom01` với `utf8mb4_unicode_ci`. ERD ở `database/erd.png`. Bảng `chi_tiet_don` là bảng trung gian n-n với khóa chính ghép; `don_gia` lưu giá tại thời điểm đặt để lịch sử đơn không đổi khi giá giáo trình thay đổi. Chỉ mục `(danh_muc_id, gia)` hỗ trợ lọc danh mục và sắp xếp giá.

Mọi dữ liệu người dùng đi vào SQL qua prepared statement. Cột `ORDER BY` được chọn từ danh sách cố định. Đặt hàng dùng transaction; câu trừ kho có điều kiện `ton_kho >= ?`, nên thiếu một mục sẽ rollback đơn, chi tiết và tồn kho đã cập nhật trước đó. Session chỉ lưu `uid`; vai trò được đọc lại từ CSDL.

`500.php` không nạp header và không truy vấn CSDL, nên vẫn hiển thị khi kết nối CSDL hỏng. Chi tiết lỗi được ghi vào `logs/php-error.log`; khi `APP_DEBUG=false`, HTML và JSON không đưa thông tin nội bộ ra phản hồi.

Website vẫn dùng được khi tắt JavaScript: tìm kiếm là form GET và thêm giỏ là form POST. `fetch` chỉ tăng cường tìm tức thời và thêm giỏ không tải lại trang.

## Kiểm thử

Sau khi import mới `schema.sql` và `seed.sql`, chạy `bash tests/bao-mat.sh http://localhost:8000`. Kịch bản gồm 12 ca âm bắt buộc và 2 ca riêng của UEDBookHub. Khi PHP và Apache chạy ở hai URL khác nhau, truyền thêm `HTACCESS_GOC=http://localhost/...` để ba ca `.htaccess` kiểm tra trên Apache. Sau đó chạy lại các ca Bài 6 bằng trình duyệt/curl, kiểm tra `logs/php-error.log` và `logs/bao-mat.log`.

AI (Codex) hỗ trợ chuyển dữ liệu sang PDO, xây dựng chức năng và kiểm thử. Thành viên cần chạy lại, hiểu mã và ghi đúng đóng góp thực tế trong báo cáo.
