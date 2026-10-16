# UEDBookHub — Bài tập nhóm 5

Website PHP thuần của Nhóm 01, chuyển từ bộ trang bài 4. Giỏ hàng dùng để đặt mua giáo trình. Phạm vi bản này: Phần A, Phần C và tài liệu báo cáo; không thực hiện Phần B cá nhân.

## Môi trường và chạy

- PHP **8.1 trở lên**, extension `mbstring`, `fileinfo`, `session`; Composer 2.
- Không cần MySQL, Node hay framework. Dữ liệu 12 giáo trình giữ trong `data/giao-trinh.json`.
- PHP cần quyền ghi `storage/`, `logs/`, `uploads/`.

Tại thư mục gốc:

```sh
composer install
php -S localhost:8000
```

Mở <http://localhost:8000/index.php>. Với XAMPP, chép kho vào `htdocs/ltweb-doan-nhom01`, chạy `composer install` tại đó và mở `http://localhost/ltweb-doan-nhom01/index.php`. Các URL tài nguyên, liên kết và chuyển hướng đều tương đối. Nếu XAMPP thiếu extension, bật chúng trong `php.ini` của XAMPP. Giới hạn PHP `upload_max_filesize >= 2M`, `post_max_size >= 4M` để gửi ảnh và trường văn bản; ứng dụng tự chặn ảnh vượt 2 MB, không tin MIME do trình duyệt gửi.

`MOI_TRUONG = 'dev'` trong `inc/config.php` khi nộp bài. Đổi tạm thành `prod` để thử ca 12 rồi trả lại `dev`.

## Tài khoản thử quản trị

- Tên đăng nhập: **admin**
- Mật khẩu minh họa: **UEDDemo2026!**
- `inc/tai-khoan.php` chỉ chứa hash tạo bằng `password_hash`, đăng nhập dùng `password_verify`.
- Đây là tài khoản công khai cho bài tập, không phải mật khẩu thật. Đăng xuất kết thúc phiên, gồm cả giỏ hàng của phiên đó.

## Chức năng – URL – Tệp PHP

| Chức năng | URL | Tệp PHP |
|---|---|---|
| Trang chủ, bốn mục đã xem | `index.php` | `index.php`, `inc/ham.php` |
| Tìm, lọc khoa, sắp xếp | `danh-sach.php?q=Giáo&sx=gia-tang` | `danh-sach.php`, `src/Data/KhoGiaoTrinh.php` |
| Chi tiết, cookie đã xem | `chi-tiet.php?id=1` | `chi-tiet.php` |
| Thêm, sửa, xóa giỏ | `gio-hang.php` | `gio-hang.php`, `src/Services/GioHang.php` |
| Liên hệ, đăng ký và upload | `lien-he.php` | `lien-he.php`, `src/Services/KiemTraLienHe.php`, `src/Data/KhoLienHe.php` |
| Đăng nhập | `dang-nhap.php` | `dang-nhap.php`, `inc/tai-khoan.php` |
| Xem phiếu mới nhất trước | `quan-tri.php` | `quan-tri.php`, `inc/bao-ve.php` |
| Đăng xuất | `dang-xuat.php` | `dang-xuat.php` |
| Giới thiệu nhóm | `gioi-thieu.php` | `gioi-thieu.php` |
| Lỗi không có ID | `chi-tiet.php?id=abc` | `404.php` |
| Trang lỗi máy chủ | `500.php` | `500.php` |

## Quy tắc dữ liệu

- Tìm kiếm tối đa 150 ký tự, theo tên/mã/tác giả, phân biệt dấu và không phân biệt hoa thường (`mb_stripos`). Khoa và kiểu sắp xếp phải thuộc danh sách cho phép.
- Giỏ: số nguyên 1–99 và không vượt số bản hiện có; giá, tên luôn lấy từ kho JSON. Thêm trùng cũng kiểm tra tổng số lượng. Số trên header là tổng số cuốn.
- Phiếu đăng ký giữ các trường của bài 4: họ tên 3–70 ký tự, MSSV 9–10 chữ số, email đúng định dạng và tên miền `@ued.udn.vn`, SĐT Việt Nam 10 chữ số; lớp tối đa 100 ký tự; hình thức mua/mượn; giáo trình tồn tại; số lượng 1–5 và không vượt kho; ngày hẹn hợp lệ trong khoảng **01/09/2026–31/01/2027** vốn có ở bài 4; ghi chú tối đa 2000 ký tự; cam kết bắt buộc.
- Mọi trường liên hệ phải là chuỗi UTF-8 hợp lệ và không chứa ký tự NUL. Dữ liệu lỗi được báo tại biểu mẫu, không lưu phiếu hoặc ảnh; các ô hợp lệ vẫn được giữ lại. Ngày được kiểm tra định dạng trước khi phân tích.
- Ảnh tùy chọn, JPG/PNG/WebP ≤ 2 MB; kiểm tra lỗi upload, `finfo` và chữ ký ảnh, tên ngẫu nhiên 32 ký tự hex. Chỉ lưu khi các ô đều đúng. Khi có lỗi cần chọn lại ảnh vì trình duyệt không cho điền lại input file.
- Khi gửi phiếu có hình thức mua, máy chủ lưu thêm bản chụp giỏ hiện tại (`gioHang`) gồm ID, tên, số lượng, đơn giá. Giỏ không tự xóa sau gửi. Đây là yêu cầu tiếp nhận, chưa có thanh toán hoặc quản lý đơn hàng ngoài đề.
- Nút **Gửi yêu cầu đặt mua** trong giỏ mở `lien-he.php?hinh-thuc=mua` và tự chọn hình thức mua ở máy chủ. Tham số chỉ nhận `mua` hoặc `muon`; lựa chọn được giữ lại khi biểu mẫu có lỗi và luồng này dùng được khi tắt JavaScript.
- POST thành công dùng `302 → GET`, flash một lần. Liên hệ lưu một JSON mỗi dòng trong `storage/lien-he.jsonl`, khóa khi ghi.
- `da_xem`: tối đa 4 ID hợp lệ, không trùng, mới nhất trước, 30 ngày, HttpOnly, SameSite=Lax; Secure khi HTTPS. Cookie được kiểm tra lại khi đọc.
- Yêu thích localStorage và thời tiết Open-Meteo giữ từ bài 4. Tất cả sáu chức năng bắt buộc dùng được khi tắt JavaScript.
- `.htaccess` trong `storage/`, `logs/` chặn Apache truy cập trực tiếp; PHP development server không xử lý `.htaccess`, chỉ dùng trên localhost cho bài tập. Không đưa nó lên máy chủ công cộng thay Apache.

## Kiến trúc

PSR-4 `App\\ → src/`; Composer sinh `vendor/`, không commit thư mục này. Lớp `GiaoTrinh` dùng thuộc tính readonly; `KhoGiaoTrinh` là nơi duy nhất đọc JSON giáo trình; `KhoLienHe` đọc/ghi JSONL; `GioHang` bọc session; `KiemTraLienHe` kiểm tra POST/FILES. Khung chung trong `inc/`, hàm escape `e()` áp dụng trong ngữ cảnh văn bản và thuộc tính HTML được đặt trong dấu nháy.

Các trang cá nhân còn `.html` theo phạm vi loại trừ Phần B; chỉ sửa liên kết về các trang chính `.php`. Header/footer sẵn hỗ trợ `$goc` để thành viên tự tích hợp sau.

## Kiểm thử và minh chứng

```sh
python3 tests/kiem-thu.py
```

Script dùng thư viện chuẩn Python và PHP, tự sao chép ứng dụng sang thư mục tạm và khởi động cổng 8011/8012; không làm thay đổi dữ liệu thật. Script làm đủ 12 ca HTTP và các thử nghiệm C2/C3, ghi kết quả vào `tests/ket-qua/`. Các Warning trong `tests/ket-qua/log-thu-nghiem.txt` thuộc thử nghiệm cố ý thêm output trước header ở bản sao, không phải log vận hành ứng dụng.

`kiemtra/btn5/` chỉ chứa ảnh minh chứng bài tập nhóm 5: ảnh chức năng, giao diện 360 px và điểm Lighthouse. `btn5` là tên thư mục con để phân biệt với ảnh các bài trước, không phải tên bắt buộc trong đề.

`tests/kiem-thu.py` là mã kiểm thử; `tests/ket-qua/` lưu các tài liệu kiểm thử phụ trợ: bản HTML do PHP sinh, báo cáo Lighthouse HTML/JSON, phản hồi W3C, kết quả HTTP/giao diện và lệnh curl. Đây là tài liệu hỗ trợ đối chiếu, không phải mã nguồn chức năng hay ảnh minh chứng. Kiểm tra 360 px trên tám trang; Lighthouse Accessibility đo ở ba trang đề bắt buộc. Kiểm tra đường dẫn con thực hiện bằng PHP server, chưa chạy trực tiếp Apache/XAMPP trên máy này.

## Git và nộp bài

Thẻ `btn4` chỉ phiên bản tĩnh trước khi chuyển PHP. Kiểm tra `git show btn4 --stat`, rồi nhóm tự push mốc đã xác nhận bằng `git push origin btn4`. Bản làm việc PHP chưa được commit/push; sau khi các thành viên kiểm tra và commit phần việc thực tế, điền mã commit cuối vào trang bìa. Không dùng mã `btn4` thay cho mã commit PHP cuối.

Tài liệu trong `bao-cao/` là bản nội dung không có bìa, không bao gồm kết quả Phần B. Nhóm bổ sung Phần B/Bảng 5, trang bìa, mã commit cuối và phần đóng góp thực tế trước khi xuất PDF và nộp. Không tự điền tỷ lệ đóng góp hoặc tạo lịch sử commit thay thành viên.

AI (Codex) đã hỗ trợ chuyển mã PHP, soạn kiểm thử và nội dung báo cáo. Thành viên cần chạy lại, hiểu và giải thích phần được phân công; bảng phân công mô tả trách nhiệm, không phải bằng chứng đã tự viết/commit.
