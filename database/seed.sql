-- Dữ liệu mẫu UEDBookHub; chạy sau schema.sql.
-- Mật khẩu của ba tài khoản thử: matkhau123
SET NAMES utf8mb4;
USE ltweb_nhom01;

INSERT INTO danh_muc (id, ten, slug) VALUES
 (1, 'Tâm lý - Giáo dục học', 'tam-ly-giao-duc-hoc'),
 (2, 'Toán - Tin', 'toan-tin'),
 (3, 'Ngữ văn', 'ngu-van'),
 (4, 'Hóa học', 'hoa-hoc'),
 (5, 'Vật lý', 'vat-ly'),
 (6, 'Giáo dục Tiểu học', 'giao-duc-tieu-hoc'),
 (7, 'Sinh - Môi trường', 'sinh-moi-truong'),
 (8, 'Lịch sử', 'lich-su');

INSERT INTO nguoi_dung (id, email, ho_ten, mat_khau, vai_tro) VALUES
 (1, 'admin@ued.vn', 'Quản trị viên', '$2y$12$QQ6wiD.RCevq5OzZhMc1v.mhHNmGDuIoZTG4gNQ1zq2snB5Qta08i', 'quan_tri'),
 (2, 'lan@ued.vn', 'Nguyễn Thị Lan', '$2y$12$qgy./oJIhP8ACNMOJD.3POR0A.anxKdKDgsWAtWkKP9GTZxa4WAUm', 'khach_hang'),
 (3, 'an@ued.vn', 'Trần Văn An', '$2y$12$SDkHi50JUahksHWJQYazl.n9Wcy4M.uhYDJgFgNIoFDhZogcAlacu', 'khach_hang');

INSERT INTO giao_trinh
 (id, danh_muc_id, ma, ten, tac_gia, nam_xuat_ban, nha_xuat_ban, gia, gia_goc, ton_kho, so_trang, anh, mo_ta) VALUES
 (1,1,'GT-GDH-01','Giáo trình Giáo dục học','PGS.TS. Trần Xuân Bách, TS. Hoàng Thế Hải',2020,'NXB Đại học Đà Nẵng',55000,85000,42,284,'images/BiaSachGiaoDucHoc.svg','Hệ thống kiến thức nền tảng về lý luận dạy học, giáo dục nhân cách và nghiệp vụ sư phạm.'),
 (2,2,'GT-CSDL-02','Giáo trình Cơ sở dữ liệu','PGS.TS. Nguyễn Trần Quốc Vinh',2021,'NXB Đại học Đà Nẵng',68000,98000,28,326,'images/BiaSachGiaoDucHoc.svg','Trình bày mô hình dữ liệu quan hệ, chuẩn hóa, ngôn ngữ SQL và các nguyên lý quản trị cơ sở dữ liệu.'),
 (3,3,'GT-VHDG-03','Văn học dân gian Việt Nam','PGS.TS. Lê Đức Luận',2019,'NXB Đại học Sư phạm',62000,90000,35,312,'images/BiaSachGiaoDucHoc.svg','Khái quát các thể loại, đặc trưng thi pháp và giá trị văn hóa của văn học dân gian Việt Nam.'),
 (4,4,'GT-HDC-04','Giáo trình Hóa đại cương','GS.TS. Lê Tự Hải',2020,'NXB Đại học Đà Nẵng',72000,105000,19,348,'images/BiaSachGiaoDucHoc.svg','Cung cấp kiến thức về cấu tạo chất, nhiệt động hóa học, cân bằng và động học phản ứng.'),
 (5,5,'GT-TNVL-05','Thí nghiệm Vật lí ở trường THPT','TS. Phùng Việt Hải',2021,'NXB Đại học Đà Nẵng',59000,86000,15,236,'images/BiaSachGiaoDucHoc.svg','Hướng dẫn thiết kế, tổ chức và đánh giá các thí nghiệm Vật lí trong dạy học trung học phổ thông.'),
 (6,1,'GT-TVHD-06','Kỹ năng tham vấn học đường','TS. Nguyễn Thị Trâm Anh và cộng sự',2019,'NXB Đại học Đà Nẵng',57000,82000,22,248,'images/BiaSachGiaoDucHoc.svg','Trang bị quy trình, nguyên tắc đạo đức và kỹ năng giao tiếp trong hoạt động tham vấn học đường.'),
 (7,6,'GT-SLTE-07','Sinh lý học trẻ em lứa tuổi tiểu học','TS. Nguyễn Công Thùy Trâm',2024,'NXB Đại học Đà Nẵng',65000,94000,30,272,'images/BiaSachGiaoDucHoc.svg','Phân tích đặc điểm phát triển cơ thể và hệ thần kinh của trẻ em trong giai đoạn tiểu học.'),
 (8,7,'GT-CNSH-08','Công nghệ sinh học thực vật','PGS.TS. Võ Châu Tuấn, TS. Trần Quang Dần',2023,'NXB Đại học Đà Nẵng',78000,112000,14,356,'images/BiaSachGiaoDucHoc.svg','Giới thiệu nuôi cấy mô, chọn giống phân tử và ứng dụng công nghệ sinh học thực vật.'),
 (9,2,'GT-MDV-09','Giáo trình Môđun và Vành','GS.TSKH. Lê Văn Thuyết, TS. Trương Công Quỳnh',2019,'NXB Đại học Đà Nẵng',70000,102000,12,304,'images/BiaSachGiaoDucHoc.svg','Tài liệu chuyên ngành Đại số về vành, iđêan và môđun.'),
 (10,6,'GT-TRTH-10','Toán rời rạc ứng dụng trong Tin học và Công nghệ Tiểu học','TS. Kiều Mạnh Hùng',2022,'NXB Đại học Sư phạm',64000,92000,25,288,'images/BiaSachGiaoDucHoc.svg','Các chủ đề logic, tổ hợp, đồ thị và bài toán ứng dụng.'),
 (11,2,'GT-PPDH-11','Lý luận và phương pháp dạy học Tin học','Tập thể giảng viên Khoa Toán - Tin',2023,'NXB Đại học Đà Nẵng',69000,99000,18,296,'images/BiaSachGiaoDucHoc.svg','Hướng dẫn xây dựng kế hoạch bài dạy môn Tin học theo định hướng năng lực.'),
 (12,3,'GT-NNVH-12','Ngôn ngữ học tiếng Việt','Tập thể giảng viên Khoa Ngữ văn',2022,'NXB Đại học Đà Nẵng',61000,88000,0,280,'images/BiaSachGiaoDucHoc.svg','Khảo sát hệ thống ngữ âm, từ vựng, ngữ pháp và ngữ dụng của tiếng Việt hiện đại.'),
 (13,2,'GT-CTDL-13','Cấu trúc Dữ liệu & Phân tích thuật toán theo chuẩn Levi''s Research','TS. Nguyễn Văn Hùng',2022,'NXB Đại học Sư phạm',75000,110000,18,340,'images/BiaSachGiaoDucHoc.svg','Giáo trình chuyên sâu về cấu trúc dữ liệu, giải thuật tối ưu theo tài liệu phân tích Levi''s Standard.'),
 (14,2,'GT-XSTK-14','Xác suất Thống kê & 50% Bài tập Thực hành','PGS.TS. Trần Văn Nam',2023,'NXB Đại học Đà Nẵng',65000,95000,25,260,'images/BiaSachGiaoDucHoc.svg','Lý thuyết xác suất thống kê toán và 50% thời lượng bài tập ứng dụng.'),
 (15,6,'GT-TKTM-15','Giáo trình Thiết kế Trang phục, Áo khoác & May mặc Tiểu học','ThS. Lê Thị Thanh',2021,'NXB Giáo dục Việt Nam',58000,85000,12,210,'images/BiaSachGiaoDucHoc.svg','Hướng dẫn kỹ thuật may mặc, thiết kế mẫu áo khoác đồng phục học sinh tiểu học.'),
 (16,1,'GT-TLPT-16','Tâm lý học Phát triển','PGS.TS. Lê Thị Mai',2021,'NXB Đại học Sư phạm',56000,80000,30,264,'images/BiaSachGiaoDucHoc.svg','Các giai đoạn phát triển tâm lý từ sơ sinh đến trưởng thành.'),
 (17,2,'GT-LTM-17','Lập trình mạng và Hệ phân tán','TS. Nguyễn Hữu Dũng',2023,'NXB Đại học Đà Nẵng',74000,105000,16,320,'images/BiaSachGiaoDucHoc.svg','Lập trình socket, kiến trúc client-server và các giao thức mạng phổ biến.'),
 (18,3,'GT-VHHĐ-18','Văn học hiện đại Việt Nam','PGS.TS. Phạm Ngọc Lan',2020,'NXB Văn học',67000,92000,22,336,'images/BiaSachGiaoDucHoc.svg','Phân tích các trào lưu hiện thực và lãng mạn trong văn học thế kỷ XX.'),
 (19,4,'GT-HHHC-19','Hóa học Hữu cơ Nâng cao','GS.TS. Đặng Văn Long',2022,'NXB Đại học Quốc gia',82000,115000,14,412,'images/BiaSachGiaoDucHoc.svg','Cơ chế phản ứng hữu cơ, tổng hợp phân tử và hóa học lập thể.'),
 (20,5,'GT-VLLT-20','Vật lý Lượng tử Đại cương','PGS.TS. Trần Quốc Tuấn',2020,'NXB Khoa học và Kỹ thuật',79000,110000,11,298,'images/BiaSachGiaoDucHoc.svg','Các tiên đề lượng tử, phương trình Schrodinger và ứng dụng quang phổ.'),
 (21,6,'GT-PPDHT-21','Phương pháp Dạy học Toán Tiểu học','TS. Vũ Minh Đức',2023,'NXB Giáo dục Việt Nam',63000,89000,27,280,'images/BiaSachGiaoDucHoc.svg','Quy trình và phương pháp hình thành khái niệm số và phép tính cho học sinh.'),
 (22,7,'GT-SHPT-22','Sinh học Phân tử và Di truyền','TS. Nguyễn Thị Thu Hà',2024,'NXB Đại học Sư phạm',85000,120000,19,368,'images/BiaSachGiaoDucHoc.svg','Cấu trúc DNA, RNA, quá trình phiên mã, dịch mã và kỹ thuật PCR.'),
 (23,2,'GT-TKW-23','Thiết kế Web và Trải nghiệm Người dùng','ThS. Trần Văn Tú',2024,'NXB Thông tin và Truyền thông',69000,99000,40,276,'images/BiaSachGiaoDucHoc.svg','Tiêu chuẩn HTML5, CSS hiện đại, chuẩn trợ năng Accessibility WCAG và UI/UX.'),
 (24,2,'GT-TTNT-24','Trí tuệ Nhân tạo và Ứng dụng','PGS.TS. Hoàng Thế Hải',2023,'NXB Đại học Đà Nẵng',88000,125000,15,350,'images/BiaSachGiaoDucHoc.svg','Các thuật toán tìm kiếm, mạng nơ-ron nhân tạo và xử lý ngôn ngữ tự nhiên.'),
 (25,3,'GT-TVTH-25','Tiếng Việt Thực hành','TS. Đỗ Thị Kim Chi',2021,'NXB Đại học Sư phạm',52000,75000,34,220,'images/BiaSachGiaoDucHoc.svg','Rèn luyện kỹ năng chính tả, dùng từ, đặt câu và tạo lập văn bản.'),
 (26,4,'GT-HHPT-26','Hóa học Phân tích','PGS.TS. Bùi Xuân Vững',2022,'NXB Đại học Đà Nẵng',71000,98000,20,310,'images/BiaSachGiaoDucHoc.svg','Phương pháp chuẩn độ thể tích, đo quang và phân tích sắc ký hiện đại.'),
 (27,5,'GT-QDTH-27','Quang học và Quang phổ Thực hành','TS. Phan Thị Thùy',2021,'NXB Đại học Đà Nẵng',66000,93000,17,245,'images/BiaSachGiaoDucHoc.svg','Nhiễu xạ, giao thoa ánh sáng và phân tích cấu trúc nguyên tử qua quang phổ.'),
 (28,6,'GT-PPDHTV-28','Dạy học Tiếng Việt ở Tiểu học','PGS.TS. Trần Thị Minh',2022,'NXB Giáo dục Việt Nam',61000,87000,29,260,'images/BiaSachGiaoDucHoc.svg','Kỹ thuật dạy đọc, viết, nói và nghe cho học sinh các lớp 1 đến 5.'),
 (29,7,'GT-STH-29','Sinh thái học và Môi trường','TS. Nguyễn Văn Thịnh',2023,'NXB Nông Nghiệp',73000,104000,21,305,'images/BiaSachGiaoDucHoc.svg','Hệ sinh thái, chu trình sinh địa hóa và bảo tồn đa dạng sinh học.'),
 (30,1,'GT-GDDB-30','Giáo dục Hòa nhập và Trẻ đặc biệt','TS. Phan Thị Mai Hương',2020,'NXB Đại học Sư phạm',59000,84000,23,230,'images/BiaSachGiaoDucHoc.svg','Phương pháp hỗ trợ tâm lý và giáo dục hòa nhập cho học sinh có nhu cầu đặc biệt.'),
 (31,2,'GT-ATTT-31','An toàn Thông tin và An ninh Mạng','TS. Lê Tấn Hùng',2024,'NXB Bách Khoa',83000,118000,19,360,'images/BiaSachGiaoDucHoc.svg','Mật mã học, an toàn ứng dụng web, phòng chống tấn công SQLi và XSS.'),
 (32,2,'GT-KPDL-32','Khai phá Dữ liệu & Học máy','PGS.TS. Nguyễn Trần Quốc Vinh',2024,'NXB Đại học Đà Nẵng',89000,128000,16,380,'images/BiaSachGiaoDucHoc.svg','Các mô hình gom cụm, phân lớp, luật kết hợp và học sâu ứng dụng.'),
 (33,4,'GT-HMT-33','Hóa học Môi trường','GS.TS. Lê Tự Hải',2023,'NXB Đại học Đà Nẵng',76000,108000,22,315,'images/BiaSachGiaoDucHoc.svg','Hóa học khí quyển, thủy quyển, xử lý ô nhiễm và chất thải nguy hại.');

INSERT INTO don_hang (id, nguoi_dung_id, trang_thai, dia_chi, ghi_chu, ngay_tao) VALUES
 (1, 2, 'da_giao', 'Thư viện UED, 459 Tôn Đức Thắng', 'Đơn mẫu của Lan', '2026-10-10 09:00:00'),
 (2, 3, 'dang_xu_ly', 'Thư viện UED, 459 Tôn Đức Thắng', 'Đơn mẫu của An', '2026-10-12 14:30:00');

-- don_gia là ảnh chụp giá tại thời điểm đặt, được lưu lặp có chủ đích.
INSERT INTO chi_tiet_don (don_hang_id, giao_trinh_id, so_luong, don_gia) VALUES
 (1, 1, 1, 55000), (1, 2, 1, 68000), (2, 3, 2, 62000);

INSERT INTO lien_he
 (giao_trinh_id, ho_ten, ma_sinh_vien, email, so_dien_thoai, lop_hoc_phan, hinh_thuc, so_luong, ngay_hen, ghi_chu) VALUES
 (1, 'Nguyễn Văn Minh', '3120220001', 'minh@ued.udn.vn', '0901234567', '22CNTT1', 'muon', 1, '2026-11-05', 'Phiếu liên hệ mẫu');

INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) VALUES
 ('3120224169', 'Trần Văn Tú', 1),
 ('3120224052', 'Lê Trung Hậu', NULL),
 ('3120224132', 'Nguyễn Tuấn Nhật Thanh', NULL),
 ('3120124040', 'Bhling Trường', NULL);

-- Dữ liệu mẫu Phần B: Sổ lưu bút trang cá nhân Trần Văn Tú (3120224169) - có 7 lời nhắn để kiểm thử phân trang (5 mục/trang)
INSERT INTO tv_luu_but (mssv, ten_khach, loi_nhan, thoi_gian) VALUES
 ('3120224169', 'Nguyễn Thị Lan', 'Giao diện website UEDBookHub rất đẹp và dễ sử dụng! Chúc trưởng nhóm hoàn thành xuất sắc đồ án.', '2026-10-24 09:15:00'),
 ('3120224169', 'Trần Văn An', 'Kho giáo trình phong phú, tính năng tìm kiếm rất nhanh và mượt mà.', '2026-10-25 14:30:00'),
 ('3120224169', 'Lê Trung Hậu', 'Trang cá nhân của Tú code mượt mà, layout responsive chuẩn chỉ.', '2026-10-26 10:20:00'),
 ('3120224169', 'Bhling Trường', 'Chúc mừng anh Tú đã dẫn dắt nhóm 01 hoàn thành tốt bài tập lớn!', '2026-10-27 16:45:00'),
 ('3120224169', 'Nguyễn Tuấn Nhật Thanh', 'Hệ thống chạy rất ổn định, tính năng đặt hàng trừ kho quá xịn.', '2026-10-28 08:30:00'),
 ('3120224169', 'Thầy Nguyễn Hữu Hải', 'Thiết kế CSDL chuẩn hóa tốt, mã nguồn phân tầng rõ ràng.', '2026-10-29 15:10:00'),
 ('3120224169', 'Bạn học K22', 'Rất ấn tượng với các dự án và kỹ năng chuyên môn của bạn Tú.', '2026-10-30 11:00:00');

-- Dữ liệu mẫu Phần B: Thống kê lượt xem theo ngày của Trần Văn Tú (7 ngày gần nhất)
INSERT INTO tv_tu_luot_xem (mssv, ngay, so_luot) VALUES
 ('3120224169', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 12),
 ('3120224169', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 18),
 ('3120224169', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 25),
 ('3120224169', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 31),
 ('3120224169', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 28),
 ('3120224169', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 42),
 ('3120224169', CURDATE(), 56);
