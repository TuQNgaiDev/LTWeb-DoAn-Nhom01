# Thư mục kiểm tra & minh chứng của Trần Văn Tú (MSSV: 3120224169)

Thư mục lưu trữ toàn bộ ảnh chụp màn hình minh chứng kiểm thử theo yêu cầu đề bài của sinh viên Trần Văn Tú:

---

## 1. Bài tập nhóm số 5 (Chương 5 – Lập trình PHP phía máy chủ)

Minh chứng cho **Phần B – Chức năng PHP trên trang cá nhân** (`thanhvien/3120224169_tu/gioithieu.php`):

- **`B_tu_cookie_darkmode.png`**:
  - *Chức năng 1*: Chuyển đổi giao diện Sáng / Tối ghi nhớ bằng **Cookie phía máy chủ PHP**.
  - *Mô tả ảnh*: Chụp trang cá nhân ở chế độ tối, mở bảng DevTools (*Application > Cookies*) hiển thị cookie `giao_dien_tu = toi`, thời hạn 30 ngày. Chức năng chạy mượt mà ngay cả khi tắt JavaScript.
- **`B_tu_tinh_diem_thanh_cong.png`**:
  - *Chức năng 2 (Ca hợp lệ)*: Máy tính điểm học phần theo công thức $0,2 \times A1 + 0,3 \times A2 + 0,5 \times A3$.
  - *Mô tả ảnh*: Nhập 3 điểm hợp lệ ($A1 = 8.5, A2 = 9.0, A3 = 8.0$), máy chủ PHP xử lý tính toán ra kết quả $8.40/10$, xếp loại **Khá (B - Đạt)** và hiển thị thông báo flash (áp dụng chuẩn PRG, F5 không bị gửi lặp).
- **`B_tu_tinh_diem_loi.png`**:
  - *Chức năng 2 (Ca kiểm tra lỗi máy chủ)*: Kiểm tra tính hợp lệ của dữ liệu đầu vào.
  - *Mô tả ảnh*: Nhập điểm sai quy tắc (vượt quá 10, số âm hoặc để trống), máy chủ PHP phát hiện và hiển thị dòng báo lỗi màu đỏ cụ thể dưới từng ô: *"Điểm phải là số từ 0 đến 10"*, giữ lại dữ liệu cũ đã nhập.
- **`B_tu_mobile_360px.png`**:
  - *Kiểm chuẩn chất lượng*: Độ tương thích di động và độ sạch của mã nguồn.
  - *Mô tả ảnh*: Trang cá nhân hiển thị chuẩn trên màn hình hẹp **360px**, không bị tràn viền hay cuộn ngang, tab Console đạt chuẩn **0 Warning, 0 Error**.

---

## 2. Bài tập nhóm số 4 (Chương 4 – JavaScript và lập trình phía máy khách)

Minh chứng lưu trữ từ bài tập nhóm 4:
- `B1_menu_mobile.png`: Menu thu gọn trên màn hình hẹp (bật/tắt bằng JavaScript, phím Esc và hỗ trợ aria-expanded).
- `B2_trang_thai_dang_tai.png`: Trạng thái đang tải spinner khi fetch JSON dữ liệu giáo trình.
- `B2_trang_thai_loi.png`: Trạng thái hiển thị thông báo lỗi và nút "Thử tải lại" khi fetch thất bại.
- `B2_trang_thai_rong.png`: Trạng thái rỗng khi không tìm thấy kết quả phù hợp khi tìm kiếm/lọc.
- `B5_thoi_tiet_danang.png`: Dữ liệu thời tiết Đà Nẵng tải thành công từ Open-Meteo REST API.
- `C_darkmode.png`: Chuyển đổi giao diện Tối/Sáng lưu `theme=dark` trong Local Storage.
- `C_loc_ky_nang.png`: Lọc tương tác danh sách kỹ năng theo nhóm chuyên môn phía client.
- `C_mobile_360px_console.png`: Trang cá nhân hiển thị ở độ rộng 360px, Console 0 lỗi JS.
- `C_lighthouse_a11y.png`: Điểm Google Lighthouse Mobile đạt Accessibility $\ge 90$.
