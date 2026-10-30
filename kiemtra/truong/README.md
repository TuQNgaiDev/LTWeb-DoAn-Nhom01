# Minh chứng kiểm tra Bài tập 6 - Bhling Trường (3120124040)

Thư mục này chứa ảnh chụp màn hình kiểm thử cho trang cá nhân của mình (`thanhvien/3120124040_truong/gioithieu.php`).

---

## 1. Bảng CSDL cá nhân phụ trách
- `tv_truong_luot_xem`: Lưu số lượt truy cập theo từng ngày bằng câu lệnh.
- `tv_luu_but`: Lưu nội dung sổ lưu bút, hỗ trợ phân trang và quyền xóa.

## 2. Minh chứng hai chức năng CSDL (Phần B)
- **Thống kê lượt xem (MySQL):**
  - Đếm tổng số lượt truy cập và lượt xem hôm nay qua Session.
  - Hiển thị bảng chi tiết 7 ngày gần nhất.
  - *Ảnh đính kèm:* `Anh_2_MinhChungThucHanh6.png`

- **Sổ lưu bút phân trang & Quyền chủ trang:**
  - Form gửi lời nhắn có lọc dữ liệu tránh lỗi XSS.
  - Danh sách lời nhắn hiển thị phân trang rõ ràng.
  - Khi đăng nhập tài khoản chủ trang (`truong@ued.vn`) mới có nút xóa lời nhắn.
  - *Ảnh đính kèm:* `Anh_3_MinhChungThucHanh6.png`

## 3. Kiểm tra chuẩn kỹ thuật
- Trang HTML đạt chuẩn 0 lỗi W3C Validator (`canhan_w3c.png` / `Anh_1_MinhChungThucHanh6.png`).
- Code chạy ổn định, không có lỗi Warning hay Notice PHP.