# Thư mục kiểm tra & minh chứng của Nguyễn Tuấn Nhật Thanh (MSSV: 3120224132)

Thư mục lưu trữ toàn bộ ảnh chụp màn hình minh chứng kiểm thử theo yêu cầu đề bài Bài tập nhóm số 6 của sinh viên **Nguyễn Tuấn Nhật Thanh**:

---

## Bài tập thực hành nhóm số 6 (Chương 6 – PHP và cơ sở dữ liệu MySQL)

Minh chứng cho **Phần B – Dữ liệu trên trang cá nhân của thành viên** (`thanhvien/3120224132_thanh/gioithieu.php`):

### 1. Bảng CSDL riêng trong hệ thống
- **`tv_thanh_ky_nang`**: Lưu trữ danh sách kỹ năng, nhóm kỹ năng (`laptrinh`, `csdl`, `kynangmem`), mức độ thành thạo và biểu tượng; khóa ngoại `mssv` tham chiếu `thanh_vien(mssv)` với `ON DELETE CASCADE`.
- **`tv_thanh_tkb`**: Lưu trữ thời khóa biểu các học phần học kỳ (mã học phần, tên môn học, số tín chỉ, thứ, tiết học, phòng học, giảng viên phụ trách); khóa ngoại `mssv` tham chiếu `thanh_vien(mssv)` với `ON DELETE CASCADE`.

### 2. Hai chức năng CSDL MySQL đã cài đặt
1. **Chức năng 1 - Lọc kỹ năng theo nhóm bằng CSDL (`tv_thanh_ky_nang`)**:
   - Chuyển đổi toàn diện từ mảng tĩnh PHP Bài 5 sang lưu trữ và truy vấn trong CSDL MySQL.
   - Hỗ trợ lọc theo các nhóm: Tất cả, Lập trình & Web, Cơ sở dữ liệu, Kỹ năng mềm bằng **Prepared Statement** an toàn.
   - Tự động đếm và hiển thị số lượng kỹ năng của từng nhóm bằng câu lệnh `COUNT(*) ... GROUP BY nhom`.
   - Giao diện thẻ kỹ năng trực quan, huy hiệu mức độ thành thạo rõ ràng.

2. **Chức năng 2 - Tìm kiếm môn học trong Thời khóa biểu bằng CSDL (`tv_thanh_tkb`)**:
   - Truy vấn CSDL trực tiếp từ bảng `tv_thanh_tkb` bằng câu lệnh `LIKE` kết hợp `addcslashes` chống SQL Injection.
   - Tìm kiếm linh hoạt theo tên môn học, mã môn học, phòng học hoặc giảng viên phụ trách.
   - Hiển thị bảng kết quả tìm kiếm với số lượng kết quả tìm thấy hoặc thông báo nếu không có môn học phù hợp.
   - Kèm theo bảng Thời khóa biểu tuần tổng quan đầy đủ của học kỳ 1 năm học 2026 – 2027.

---

### 3. Danh mục tệp ảnh minh chứng kiểm thử trong thư mục

| Tên tệp ảnh | Nội dung minh chứng |
|:---|:---|
| **`B6_thanh_loc_ky_nang_laptrinh.png`** | Lọc danh sách kỹ năng thuộc nhóm Lập trình từ bảng CSDL `tv_thanh_ky_nang` (`?nhom=laptrinh`). |
| **`B6_thanh_loc_ky_nang_csdl.png`** | Lọc danh sách kỹ năng thuộc nhóm Cơ sở dữ liệu từ bảng CSDL `tv_thanh_ky_nang` (`?nhom=csdl`). |
| **`B6_thanh_tim_kiem_tkb_thanhcong.png`** | Tìm kiếm môn học với từ khóa `"web"` hiển thị đúng học phần Thiết kế & lập trình web cùng phòng học và giảng viên từ bảng `tv_thanh_tkb`. |
| **`B6_thanh_tim_kiem_tkb_khongthay.png`** | Tìm kiếm với từ khóa không tồn tại (`"xyz"`), hiển thị thông báo lỗi thân thiện không làm hỏng trang. |

