# Thư mục kiểm tra & minh chứng của Trần Văn Tú (MSSV: 3120224169)

Thư mục lưu trữ toàn bộ ảnh chụp màn hình minh chứng kiểm thử theo yêu cầu đề bài Bài tập nhóm số 6 của sinh viên **Trần Văn Tú**:

---

## Bài tập thực hành nhóm số 6 (Chương 6 – PHP và cơ sở dữ liệu MySQL)

Minh chứng cho **Phần B – Dữ liệu trên trang cá nhân của thành viên** (`thanhvien/3120224169_tu/gioithieu.php`):

### 1. Bảng CSDL riêng trong hệ thống
- **`tv_tu_bang_diem`**: Lưu trữ danh sách học phần, số tín chỉ, điểm thành phần (A1, A2, A3) và điểm tổng kết hệ 10 của sinh viên Trần Văn Tú; có ràng buộc CHECK [0, 10], khóa ngoại `mssv` tham chiếu `thanh_vien(mssv)` với `ON DELETE CASCADE`.

### 2. Hai chức năng CSDL MySQL đã cài đặt
1. **Chức năng 1 - Quản lý Bảng điểm học phần & Tính GPA tích lũy bằng CSDL (`tv_tu_bang_diem`)**:
   - Chuyển đổi và nâng cấp toàn diện từ *Máy tính điểm học phần Bài 5* của Tú sang lưu trữ lâu dài trên CSDL MySQL.
   - Thêm học phần mới có kiểm tra hợp lệ chặt chẽ ở máy chủ PHP (Điểm thành phần A1, A2, A3 từ 0 đến 10, số tín chỉ 1–10).
   - Tự động tính điểm tổng kết hệ 10: `0.2×A1 + 0.3×A2 + 0.5×A3`, quy đổi thang điểm 4 và điểm chữ (A, B+, B, C+, C, D+, D, F).
   - Thống kê tự động: Tổng số tín chỉ tích lũy, Điểm trung bình hệ 10, Điểm trung bình hệ 4 (GPA) và Xếp loại học lực.
   - Áp dụng mẫu **PRG (Post/Redirect/Get)** kèm thông báo flash trong Session (F5 không bị gửi lặp dữ liệu).
   - **Phân quyền xóa máy chủ (chống IDOR)**: Khách truy cập hoặc người dùng thông thường **không thấy cột và nút Xóa**. Chỉ khi đăng nhập đúng tài khoản chủ trang (`admin@ued.vn`) thì nút Xóa màu đỏ mới xuất hiện. Khi nhận POST xóa, máy chủ kiểm tra `$laChuTrang` và thực thi `DELETE FROM tv_tu_bang_diem WHERE id = ? AND mssv = ?`.

2. **Chức năng 2 - Bảng điều khiển Thống kê Đồ án UEDBookHub (Business Intelligence Dashboard)**:
   - Khai thác trực tiếp CSDL đồ án UEDBookHub bằng các câu lệnh truy vấn phức hợp nhiều bảng:
   - **Top 5 giáo trình bán chạy nhất**: Kết hợp `chi_tiet_don JOIN giao_trinh JOIN danh_muc`, gom nhóm `GROUP BY`, tính `SUM(so_luong)` và `SUM(so_luong * don_gia)`.
   - **Doanh thu và số lượng theo Danh mục**: Kết hợp đa bảng `danh_muc LEFT JOIN giao_trinh LEFT JOIN chi_tiet_don`, đếm số đầu sách `COUNT(DISTINCT)` và tổng doanh thu ngành hàng.
   - **Thẻ tổng quan kinh doanh**: Tổng doanh thu đồ án và tổng số đơn hàng đã đặt.

---

### 3. Danh mục tệp ảnh minh chứng kiểm thử trong thư mục

| Tên tệp ảnh | Nội dung minh chứng |
|:---|:---|
| **`B6_tu_bang_diem_chutrang_xoa.png`** | Đăng nhập tài khoản `admin@ued.vn` (chủ trang Tú), hệ thống hiển thị huy hiệu `Đã xác thực chủ trang 🔑` và xuất hiện nút Xóa màu đỏ cạnh từng môn học trong bảng điểm. |
| **`B6_tu_bang_diem_khach_an_nut_xoa.png`** | Khi xem với tư cách khách vãng lai (chưa đăng nhập), cột Thao tác và nút Xóa hoàn toàn bị ẩn, đảm bảo an toàn phân quyền và chống IDOR. |
| **`B6_tu_them_diem_thanh_cong.png`** | Thêm mới học phần thành công qua form nhập điểm, hiển thị thông báo flash màu xanh và cập nhật ngay vào bảng điểm cùng chỉ số GPA tích lũy theo mô hình PRG. |
| **`B6_tu_thong_ke_do_an.png`** | Bảng điều khiển thống kê đồ án UEDBookHub với thẻ tổng doanh thu, tổng đơn hàng, Bảng Top 5 giáo trình bán chạy và Bảng doanh thu theo danh mục (`JOIN + GROUP BY`). |
