# Thư mục kiểm tra & minh chứng của Trần Văn Tú (MSSV: 3120224169)

Thư mục lưu trữ toàn bộ ảnh chụp màn hình minh chứng kiểm thử theo yêu cầu đề bài Bài tập nhóm số 6 của sinh viên **Trần Văn Tú**:

---

## Bài tập thực hành nhóm số 6 (Chương 6 – PHP và cơ sở dữ liệu MySQL)

Minh chứng cho **Phần B – Dữ liệu trên trang cá nhân của thành viên** (`thanhvien/3120224169_tu/gioithieu.php`):

### 1. Bảng CSDL riêng trong hệ thống
- **`tv_luu_but`**: Lưu trữ lời nhắn lưu bút của khách, có khóa ngoại `mssv` tham chiếu `thanh_vien(mssv)` với `ON DELETE CASCADE`.
- **`tv_tu_luot_xem`**: Lưu trữ số lượt xem theo ngày của Trần Văn Tú (khóa duy nhất `(mssv, ngay)`), hỗ trợ thống kê 7 ngày gần nhất.

### 2. Hai chức năng CSDL MySQL đã cài đặt
1. **Chức năng 1 - Sổ lưu bút có phân trang & Bảo vệ quyền xóa của chủ trang**:
   - Gửi lời nhắn có kiểm tra tính hợp lệ tại máy chủ PHP (Tên: 1–50 ký tự, Lời nhắn: 1–500 ký tự).
   - Áp dụng mẫu **PRG (Post/Redirect/Get)** kèm thông báo flash trong Session (F5 không bị gửi lặp dữ liệu).
   - Phân trang 5 lời nhắn/trang với prepared statement: `LIMIT 5 OFFSET ?` và `SELECT COUNT(*)`.
   - **Phân quyền xóa máy chủ (chống IDOR)**: Khách truy cập hoặc người dùng thông thường **không thấy nút Xóa**. Chỉ khi đăng nhập đúng tài khoản chủ trang (`admin@ued.vn`) thì nút Xóa màu đỏ mới xuất hiện. Khi nhận POST xóa, máy chủ kiểm tra `$laChuTrang` và thực thi `DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?`.
2. **Chức năng 2 - Bộ đếm lượt xem theo ngày bằng CSDL**:
   - Tự động ghi nhận lượt xem bằng câu lệnh SQL chuẩn: `INSERT INTO tv_tu_luot_xem (mssv, ngay, so_luot) VALUES (?, CURDATE(), 1) ON DUPLICATE KEY UPDATE so_luot = so_luot + 1`.
   - Quản lý theo phiên để tránh tăng ảo khi F5 liên tục trong cùng một ngày.
   - Hiển thị bảng thống kê 7 ngày gần nhất và thanh trực quan tỷ lệ phần trăm; tính tổng lượt xem tích lũy qua `COALESCE(SUM(so_luot), 0)`.

---

### 3. Danh mục tệp ảnh minh chứng kiểm thử trong thư mục

| Tên tệp ảnh | Nội dung minh chứng |
|:---|:---|
| **`B6_tu_luu_but_chutrang_xoa.png`** | Đăng nhập tài khoản `admin@ued.vn` (chủ trang Tú), hệ thống hiển thị huy hiệu `Đã xác thực chủ trang 🔑` và xuất hiện nút Xóa màu đỏ cạnh từng lời nhắn. |
| **`B6_tu_luu_but_khach_an_nut_xoa.png`** | Khi xem với tư cách khách vãng lai, nút Xóa hoàn toàn bị ẩn, đảm bảo an toàn phân quyền. |
| **`B6_tu_luu_but_phantrang.png`** | Phân trang sổ lưu bút 5 lời nhắn/trang tải dữ liệu từ CSDL, hiển thị các nút chuyển trang và xem trang 2 (`?trang=2`). |
| **`B6_tu_thong_ke_luot_xem.png`** | Thẻ tổng lượt xem tích lũy và bảng thống kê 7 ngày gần nhất kèm thanh tỷ lệ từ bảng `tv_tu_luot_xem`. |
