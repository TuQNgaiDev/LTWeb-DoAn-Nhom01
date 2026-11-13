# Báo cáo thực hiện công việc – Lê Trung Hậu (MSSV: 3120224052)
**Học phần:** Thiết kế & Lập trình Web | **Đề tài:** UEDBookHub (Nhóm 01)  
**Trang cá nhân:** `thanhvien/3120224052_hau/gioithieu.php`

---

## Bảng tổng hợp công việc và minh chứng

| STT | Công việc | Nội dung thực hiện | Minh chứng |
|:---:|:---|:---|:---|
| **1** | **Rà soát và sửa mã nguồn trang cá nhân** | Kiểm tra toàn diện mã nguồn PHP, HTML, CSS, JS; khắc phục lỗi lệch cấu trúc cột bảng Thời khóa biểu chuẩn W3C, gia cố bảo mật CSRF, chuẩn hóa cơ chế PRG và Prepared Statement. | [Xem mục 1 - Code đã sửa](#1-rà-soát-và-sửa-mã-nguồn-trang-cá-nhân) |
| **2** | **Kiểm thử ít nhất 5 trường hợp bảo mật** | Thực hiện kiểm thử 5 ca bảo mật quan trọng: Chống CSRF (403), Chống SQL Injection, Chống XSS (Escape HTML), Xác thực dữ liệu máy chủ (Validation), Chống Brute Force (Rate Limit 429). | [Xem mục 2 - Kết quả kiểm thử](#2-kiểm-thử-ít-nhất-5-trường-hợp-bảo-mật) |
| **3** | **Rà soát chéo mã nguồn thành viên khác** | Đánh giá độc lập mã nguồn trang cá nhân của 3 thành viên trong nhóm: Bhling Trường, Nguyễn Tuấn Nhật Thanh, Trần Văn Tú; ghi nhận xét ưu điểm và các điểm cần tối ưu. | [Xem mục 3 - Nhận xét rà soát chéo](#3-rà-soát-chéo-mã-nguồn-thành-viên-khác) |
| **4** | **Commit và push GitHub** | Đóng gói toàn bộ mã nguồn cập nhật, tài liệu minh chứng và đẩy lên kho lưu trữ GitHub chung của nhóm. | [Xem mục 4 - Lịch sử commit](#4-lịch-sử-commit-và-push-github) |

---

## 1. Rà soát và sửa mã nguồn trang cá nhân

### 1.1. Các lỗi phát hiện trong quá trình rà soát
1. **Lệch cấu trúc cột bảng Thời khóa biểu (`bang-tkb`)**:
   - Tiêu đề bảng có 8 cột: 1 cột `<th>Tiết</th>` và 7 cột thứ trong tuần (Thứ 2 đến Chủ nhật).
   - Ở các hàng Tiết 4, Tiết 5, Tiết 6, Tiết 10, Tiết 11, Tiết 12 bị thừa 1 thẻ `<td>` (tổng cộng 9 ô thay vì 8 ô).
   - Hàng Tiết 9 bị thiếu 1 ô `<td>` cho Thứ 2 sau khi hết `rowspan="2"` ở Tiết 7–8.
   - *Hậu quả:* Bảng bị vỡ khung trên một số màn hình và không vượt qua chuẩn kiểm định cú pháp W3C HTML5 Validator.
2. **Cơ chế lưu thông báo phản hồi (PRG Pattern)**:
   - Form thả tim trước đây điều hướng qua tham số URL `?da_thich=1`, nếu người dùng chia sẻ liên kết sẽ bị hiển thị thông báo cảm ơn sai thời điểm.
   - *Khắc phục:* Chuyển sang lưu thông báo qua Session Flash (`$_SESSION['flash']`), tự động xóa sau khi hiển thị (Flash message) giúp URL luôn sạch sẽ.
3. **Đồng bộ cơ sở dữ liệu MySQL**:
   - Chuẩn hóa hàm khởi tạo bảng CSDL cá nhân (`tv_hau_luot_thich`, `tv_hau_cau_chao`) với `utf8mb4_unicode_ci` và `ENGINE=InnoDB`.

### 1.2. Mã nguồn sau khi đã sửa
- Tệp tin chính: `thanhvien/3120224052_hau/gioithieu.php`
- Cấu trúc Thời khóa biểu được hiệu chỉnh chính xác 100% về số cột (8 cột chuẩn cho tất cả 12 tiết học).
- Đã kiểm tra cú pháp PHP: `php -l gioithieu.php` -> **No syntax errors detected**.

---

## 2. Kiểm thử ít nhất 5 trường hợp bảo mật

| Mã ca | Tên ca kiểm thử | Mô tả kịch bản kiểm thử | Kết quả mong đợi | Kết quả thực tế & Minh chứng | Đánh giá |
|:---:|:---|:---|:---|:---|:---:|
| **TC01** | **Chống tấn công CSRF** | Gửi yêu cầu HTTP POST (thả tim / tính điểm) không kèm token CSRF hoặc kèm token giả mạo. | Máy chủ chặn xử lý, ghi nhật ký bảo mật và trả về mã lỗi HTTP **403 Forbidden**. | ✅ **PASS**: Hệ thống middleware `inc/config.php` phát hiện thiếu/sai CSRF token và từ chối request ngay lập tức. Ảnh: `../../kiemtra/A_bao_mat_403.png`. | **ĐẠT** |
| **TC02** | **Chống SQL Injection** | Truyền chuỗi tấn công SQLi (`' OR '1'='1' UNION SELECT ...`) vào tham số truy vấn CSDL (`gio`, `mssv`). | PDO Prepared Statements tự động tham số hóa (parameter binding), không bị lỗi cú pháp SQL và không rò rỉ dữ liệu. | ✅ **PASS**: Câu lệnh `SELECT ... WHERE mssv = ?` dùng PDO Parameterization an toàn tuyệt đối, chuỗi SQLi được coi như chuỗi ký tự thuần. | **ĐẠT** |
| **TC03** | **Chống tấn công XSS** | Nhập mã độc JavaScript `<script>alert('XSS')</script>` vào nội dung hiển thị hoặc câu chào. | Ký tự nguy hiểm được mã hóa thành thực thể HTML (`&lt;script&gt;`), không thực thi script trên trình duyệt. | ✅ **PASS**: Toàn bộ dữ liệu xuất ra giao diện được bọc qua hàm `e()` (`htmlspecialchars` với cờ `ENT_QUOTES`), script bị vô hiệu hóa an toàn. | **ĐẠT** |
| **TC04** | **Xác thực dữ liệu đầu vào phía máy chủ (Validation)** | Nhập các giá trị điểm ngoài miền cho phép (ĐRL < 0 hoặc > 100, GPA < 0 hoặc > 4.0, chuỗi ký tự, NaN). | Máy chủ PHP từ chối tính toán, hiển thị thông báo lỗi cụ thể tại từng ô nhập và giữ lại giá trị cũ an toàn. | ✅ **PASS**: Hàm `filter_var(..., FILTER_VALIDATE_FLOAT)` kết hợp kiểm tra biên chặn hoàn toàn dữ liệu sai lệch trước khi tính toán. | **ĐẠT** |
| **TC05** | **Chống dò mật khẩu (Brute-Force Rate Limiting)** | Thử đăng nhập sai mật khẩu liên tiếp 5 lần với cùng một tài khoản email. | Từ lần thử thứ 6, hệ thống tự động khóa tạm thời 15 phút, trả về mã lỗi HTTP **429 Too Many Requests**. | ✅ **PASS**: Hệ thống lưu lịch sử đăng nhập sai trong CSDL/Session, tự động kích hoạt chế độ bảo vệ rate limit. | **ĐẠT** |

---

## 3. Rà soát chéo mã nguồn thành viên khác

### 3.1. Thành viên: Bhling Trường (MSSV: 3120124040)
- **Tệp rà soát:** `thanhvien/3120124040_truong/gioithieu.php`
- **Chức năng CSDL phụ trách:** Sổ lưu bút bạn bè (bảng `tv_luu_but`) & Thống kê lượt xem theo ngày (bảng `tv_truong_luot_xem`).
- **Nhận xét ưu điểm:**
  - Áp dụng kỹ thuật `ON DUPLICATE KEY UPDATE` kết hợp Session máy chủ rất hay để đếm lượt xem theo ngày mà không bị tăng lặp khi nhấn F5.
  - Phân trang sổ lưu bút rõ ràng với `COUNT(*)` và `LIMIT/OFFSET`.
  - Phân quyền xóa lưu bút chặt chẽ: chỉ tài khoản chủ trang (`truong@ued.vn`) mới có quyền xóa lời nhắn, chống IDOR an toàn bằng điều kiện `WHERE id = ? AND mssv = ?`.
- **Góp ý hoàn thiện:**
  - Cần bổ sung kiểm tra độ dài tối đa của họ tên và lời nhắn khi gửi lưu bút ở tầng máy chủ PHP để tránh việc người dùng nhập văn bản quá dài làm tràn bảng.

### 3.2. Thành viên: Nguyễn Tuấn Nhật Thanh (MSSV: 3120224132)
- **Tệp rà soát:** `thanhvien/3120224132_thanh/gioithieu.php`
- **Chức năng CSDL phụ trách:** Lọc kỹ năng theo nhóm (bảng `tv_thanh_ky_nang`) & Tìm kiếm thời khóa biểu (bảng `tv_thanh_tkb`).
- **Nhận xét ưu điểm:**
  - Viết câu lệnh `GROUP BY` để đếm số lượng kỹ năng của từng nhóm rất tối ưu, hiển thị huy hiệu trực quan.
  - Chức năng tìm kiếm môn học sử dụng `LIKE` có xử lý `addcslashes` để thoát các ký tự đặc biệt (`%`, `_`), bảo vệ chống injection.
  - Giao diện tra cứu danh mục thân thiện, có thông báo không tìm thấy khi nhập từ khóa lạ.
- **Góp ý hoàn thiện:**
  - Nên bổ sung phân trang hoặc giới hạn số lượng kết quả (`LIMIT 20`) cho bảng tìm kiếm nếu dữ liệu môn học mở rộng nhiều hơn trong tương lai.

### 3.3. Thành viên: Trần Văn Tú (MSSV: 3120224169)
- **Tệp rà soát:** `thanhvien/3120224169_tu/gioithieu.php`
- **Chức năng CSDL phụ trách:** Quản lý Bảng điểm học phần & Tính GPA (bảng `tv_tu_bang_diem`) & Dashboard Thống kê Đồ án UEDBookHub.
- **Nhận xét ưu điểm:**
  - Dashboard thống kê đồ án rất chuyên nghiệp: vận dụng đầy đủ các kỹ thuật nâng cao `JOIN`, `GROUP BY`, `SUM`, `COUNT(DISTINCT)` từ các bảng nghiệp vụ (`chi_tiet_don`, `giao_trinh`, `danh_muc`).
  - Kiểm tra hợp lệ dữ liệu điểm thành phần A1, A2, A3 từ 0 đến 10 chặt chẽ.
  - Phân quyền xóa môn học chống IDOR bằng cách ẩn nút xóa với khách và chỉ xác thực cho tài khoản `admin@ued.vn`.
- **Góp ý hoàn thiện:**
  - Có thể thêm hiệu ứng hover hoặc biểu đồ trực quan (chart) cho phần doanh thu danh mục để bảng điều khiển BI sinh động hơn.

---

## 4. Lịch sử commit và push GitHub

### 4.1. Lịch sử các commit gần nhất
```text
* [Commit mới nhất] Rà soát và hoàn thiện mã nguồn trang cá nhân, bổ sung báo cáo kiểm thử bảo mật của Lê Trung Hậu
* 0f0529d Hoan thien bao mat va kiem thu
* ca70269 Sua loi cat avatar va can chinh khung ho so cua Thanh
* dfdf4dd Sua loi breadcrumb va cap nhat anh minh chung trang ca nhan cua Thanh
* 21d814a Merge branch 'origin/main': Dong bo toan bo CSDL va ma nguon cua 4 thanh vien (Tu, Hau, Thanh, Truong)
* 34674a5 Cap nhat .gitignore chan file zip va hoan thien README kiem tra
```

### 4.2. Trạng thái kho lưu trữ
- Nhánh làm việc: `main`
- Toàn bộ 4 hạng mục công việc theo yêu cầu đã được hoàn thành đầy đủ, đạt chuẩn kỹ thuật và sẵn sàng nghiệm thu.
