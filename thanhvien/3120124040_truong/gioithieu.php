<?php
/**
 * Trang cá nhân thành viên: Bhling Trường (MSSV: 3120124040) - Nhóm 01 UEDBookHub.
 * Hai chức năng CSDL MySQL (PDO) theo yêu cầu Phần B - Bài tập nhóm 6:
 * 1. Sổ lưu bút bạn bè (bảng tv_luu_but):
 *    - Chuyển đổi từ tệp JSONL Bài 5: Lưu trữ CSDL, phân trang chuẩn 5 lời nhắn/trang (COUNT + LIMIT OFFSET), mới nhất xếp trước.
 *    - Phân quyền chủ trang: Chỉ khi chủ trang Trường đăng nhập (truong@ued.vn) mới thấy và thực hiện được nút Xóa (chống IDOR bằng WHERE id = ? AND mssv = ?).
 * 2. Bộ đếm lượt xem theo ngày (bảng tv_truong_luot_xem):
 *    - Chuyển đổi từ tệp views.txt Bài 5: Sử dụng MySQL ON DUPLICATE KEY UPDATE kết hợp Session phía máy chủ để chống F5 tăng lặp.
 *    - Thống kê tổng số lượt xem, lượt xem hôm nay và bảng chi tiết 7 ngày gần nhất.
 * Cách thử nghiệm:
 * - Gửi lời nhắn: Báo lỗi nếu thiếu trường; gửi thành công chuyển hướng PRG (F5 không gửi lại form).
 * - Kiểm tra phân trang: Bấm sang trang 2 ở danh sách lưu bút.
 * - Kiểm tra phân quyền: Khi chưa đăng nhập, không thấy nút Xóa. Nhấp "Đăng nhập nhanh chủ trang" hoặc đăng nhập truong@ued.vn (mật khẩu: matkhau123), nút Xóa sẽ xuất hiện.
 */

declare(strict_types=1);

const MSSV = '3120124040';
$goc = '../../';
$trang = '';
$tieuDe = 'Giới thiệu thành viên: Bhling Trường';

require_once __DIR__ . '/../../inc/config.php';

// Chế độ dev: Hỗ trợ mô phỏng đăng nhập nhanh cho kiểm thử & chụp ảnh minh chứng
if (defined('MOI_TRUONG') && MOI_TRUONG === 'dev') {
    if (isset($_GET['dang_nhap_chu_trang'])) {
        $_SESSION['uid'] = 4; // Tài khoản truong@ued.vn
        chuyenHuong('gioithieu.php#so-luu-but');
    } elseif (isset($_GET['dang_xuat_chu_trang'])) {
        unset($_SESSION['uid']);
        chuyenHuong('gioithieu.php#so-luu-but');
    }
}

// --- Xác thực thông tin thành viên và quyền chủ trang ---
$stTv = db()->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$stTv->execute([MSSV]);
$tv = $stTv->fetch();

$ndHienTai = nguoiDung();
$laChuTrang = $tv && !empty($tv['nguoi_dung_id']) && ($ndHienTai['id'] ?? 0) === (int) $tv['nguoi_dung_id'];

// --- CHỨC NĂNG 1: BỘ ĐẾM LƯỢT XEM THEO NGÀY (MySQL) ---
if (empty($_SESSION['da_xem_3120124040'])) {
    $stGhiViews = db()->prepare('
        INSERT INTO tv_truong_luot_xem (mssv, ngay, so_luot)
        VALUES (?, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE so_luot = so_luot + 1
    ');
    $stGhiViews->execute([MSSV]);
    $_SESSION['da_xem_3120124040'] = true;
}

// Lấy tổng số lượt xem qua các ngày
$stTongViews = db()->prepare('SELECT COALESCE(SUM(so_luot), 0) FROM tv_truong_luot_xem WHERE mssv = ?');
$stTongViews->execute([MSSV]);
$tongLuotXem = (int) $stTongViews->fetchColumn();

// Lấy số lượt xem hôm nay
$stHomNay = db()->prepare('SELECT COALESCE(so_luot, 0) FROM tv_truong_luot_xem WHERE mssv = ? AND ngay = CURDATE()');
$stHomNay->execute([MSSV]);
$luotXemHomNay = (int) $stHomNay->fetchColumn();

// Lấy lịch sử 7 ngày gần nhất
$st7Ngay = db()->prepare('
    SELECT DATE_FORMAT(ngay, "%d/%m/%Y") AS ngay_dinh_dang, so_luot
    FROM tv_truong_luot_xem
    WHERE mssv = ?
    ORDER BY ngay DESC
    LIMIT 7
');
$st7Ngay->execute([MSSV]);
$lichSu7Ngay = $st7Ngay->fetchAll();

// --- CHỨC NĂNG 2: XỬ LÝ SỔ LƯU BÚT (POST / PRG + PHÂN TRANG + PHÂN QUYỀN) ---
$thongBaoLoi = '';
$duLuuBut = ['ho_ten' => '', 'loi_nhan' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // 2.1 Xóa lời nhắn (chỉ chủ trang đăng nhập mới được xóa)
    if (isset($_POST['xoa_luu_but'])) {
        $xoaId = filter_var($_POST['xoa_id'] ?? '', FILTER_VALIDATE_INT);
        if ($xoaId && $laChuTrang) {
            $stXoa = db()->prepare('DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?');
            $stXoa->execute([$xoaId, MSSV]);
            $_SESSION['flash'] = 'Đã xóa lời nhắn thành công.';
        }
        chuyenHuong('gioithieu.php#so-luu-but');
    }

    // 2.2 Gửi lời nhắn mới
    if (isset($_POST['gui_luu_but'])) {
        $duLuuBut['ho_ten'] = trim($_POST['ho_ten'] ?? '');
        $duLuuBut['loi_nhan'] = trim($_POST['loi_nhan'] ?? '');

        if ($duLuuBut['ho_ten'] === '' || $duLuuBut['loi_nhan'] === '') {
            $thongBaoLoi = 'Vui lòng nhập đầy đủ họ tên và lời nhắn.';
        } elseif (mb_strlen($duLuuBut['ho_ten']) < 2 || mb_strlen($duLuuBut['ho_ten']) > 50) {
            $thongBaoLoi = 'Họ tên cần từ 2 đến 50 ký tự.';
        } elseif (mb_strlen($duLuuBut['loi_nhan']) < 2 || mb_strlen($duLuuBut['loi_nhan']) > 500) {
            $thongBaoLoi = 'Lời nhắn cần từ 2 đến 500 ký tự.';
        } else {
            $stThem = db()->prepare('INSERT INTO tv_luu_but (mssv, ten_khach, loi_nhan) VALUES (?, ?, ?)');
            $stThem->execute([MSSV, $duLuuBut['ho_ten'], $duLuuBut['loi_nhan']]);
            $_SESSION['flash'] = 'Cảm ơn bạn! Lời nhắn của bạn đã được ghi lại thành công.';
            chuyenHuong('gioithieu.php#so-luu-but');
        }
    }
}

// 2.3 Phân trang sổ lưu bút (5 lời nhắn / trang)
$moiTrang = 5;
$stTong = db()->prepare('SELECT COUNT(*) FROM tv_luu_but WHERE mssv = ?');
$stTong->execute([MSSV]);
$tongSoLuuBut = (int) $stTong->fetchColumn();
$soTrang = max(1, (int) ceil($tongSoLuuBut / $moiTrang));

$trangHienTai = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1]
]);
$trangHienTai = min($trangHienTai, $soTrang);
$boQua = ($trangHienTai - 1) * $moiTrang;

$stDs = db()->prepare('
    SELECT id, ten_khach, loi_nhan, DATE_FORMAT(thoi_gian, "%d/%m/%Y %H:%i") AS ngay_gio
    FROM tv_luu_but
    WHERE mssv = ?
    ORDER BY thoi_gian DESC, id DESC
    LIMIT 5 OFFSET ?
');
$stDs->execute([MSSV, $boQua]);
$danhSachLuuBut = $stDs->fetchAll();

$thongBaoThanhCong = flash();

require_once __DIR__ . '/../../inc/header.php';
?>

<!-- Nạp CSS riêng của cá nhân theo đúng gợi ý đề bài -->
<link rel="stylesheet" href="css/style.css?v=15">

<main class="noi-dung-chinh">

  <article class="ho-so-ca-nhan">
    <div class="ho-so-ca-nhan__than">
      <div class="ho-so-ca-nhan__hang-dau">
        <img class="ho-so-ca-nhan__anh" src="../../images/avata-truong.jpg" alt="Ảnh chân dung sinh viên Bhling Trường" width="160" height="160">
        <div class="ho-so-ca-nhan__thong-tin">
          <h1 class="ho-so-ca-nhan__ten">Bhling Trường</h1>
          <div class="ho-so-ca-nhan__vai-tro">Thành viên Nhóm 01 – Đề tài UEDBookHub</div>
          <div class="nhom-nut">
            <span class="huy-hieu huy-hieu--con">MSSV: 3120124040</span>
            <span class="huy-hieu huy-hieu--con">Khoa Toán – Tin</span>
            <span class="huy-hieu huy-hieu--sap-het">Lớp: 24SPT - Sư phạm Tin học</span>
            <span class="huy-hieu huy-hieu--con">Trường ĐH Sư phạm – ĐH Đà Nẵng</span>
            <p class="dong-email" style="margin-top: 10px;">
              Email: <strong id="email-ca-nhan">truongbhlingnttinh@gmail.com</strong>
              <button id="nut-chep-email" type="button" class="nut-tien-ich" aria-label="Sao chép địa chỉ email">
                📋 Sao chép
              </button>
              <span id="thong-bao-chep" class="thong-bao-chep" aria-live="polite"></span>
            </p>
          </div>

          <!-- KHỐI ĐẾM NGƯỢC THI CUỐI KỲ -->
          <div class="hop-dem-nguoc" role="timer" aria-live="polite">
            <span class="nhan-dem-nguoc">⏳ Đếm ngược thi cuối kỳ (25/12/2026):</span>
            <span id="dong-ho-dem-nguoc" class="dong-ho-dem-nguoc">Đang tính toán...</span>
          </div>
        </div>
      </div>

      <section>
        <h2>Giới thiệu bản thân</h2>
        <p class="doan-van-dai">
          Mình là <strong>Bhling Trường</strong> (MSSV: <strong>3120124040</strong>), sinh viên ngành Sư phạm Tin học (Lớp 24SPT), thành viên nhóm dự án <strong>UEDBookHub</strong>. Trong Bài tập 6, mình phụ trách triển khai CSDL MySQL, chuẩn hóa truy vấn bằng Prepared Statement PDO, xây dựng tính năng Sổ lưu bút phân trang có phân quyền và hệ thống thống kê lượt truy cập theo ngày.
        </p>
        <blockquote class="the-phuong-cham">
          <p>“Vững nền tảng sư phạm, sáng tạo cùng công nghệ số!”</p>
        </blockquote>
      </section>

      <section>
        <h2>Kỹ năng chuyên môn</h2>
        <ul class="luoi-ky-nang">
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">💻</span>
            <h3 class="the-ky-nang__tieu-de">Lập trình PHP &amp; MySQL</h3>
            <p class="the-ky-nang__mo-ta">Thành thạo kết nối PDO, Prepared Statements chống SQL Injection, quản lý phiên và phân quyền người dùng.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🗄️</span>
            <h3 class="the-ky-nang__tieu-de">Cơ sở dữ liệu</h3>
            <p class="the-ky-nang__mo-ta">Nắm vững thiết kế bảng chuẩn hóa, khóa chính, khóa ngoại CASCADE, ràng buộc CHECK và tối ưu chỉ mục.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🌐</span>
            <h3 class="the-ky-nang__tieu-de">Xây dựng giao diện Web</h3>
            <p class="the-ky-nang__mo-ta">Viết cấu trúc HTML5 chuẩn ngữ nghĩa, CSS Mobile-First responsive 360px và đạt điểm chuẩn Accessibility WCAG.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">👨‍🏫</span>
            <h3 class="the-ky-nang__tieu-de">Định hướng Sư phạm</h3>
            <p class="the-ky-nang__mo-ta">Đang rèn luyện kỹ năng soạn thảo nội dung bài học, chuẩn bị học liệu số và đổi mới phương pháp dạy Tin học.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🤝</span>
            <h3 class="the-ky-nang__tieu-de">Làm việc nhóm &amp; Git</h3>
            <p class="the-ky-nang__mo-ta">Phối hợp nhịp nhàng trên GitHub, quản lý nhánh, giải quyết xung đột và lưu trữ lịch sử commit rõ ràng.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">📚</span>
            <h3 class="the-ky-nang__tieu-de">Tinh thần học hỏi</h3>
            <p class="the-ky-nang__mo-ta">Chăm chỉ thực hành bài tập trên lớp, chủ động nghiên cứu tài liệu kỹ thuật và hoàn thành xuất sắc nhiệm vụ.</p>
          </li>
        </ul>
      </section>

      <section>
        <h2>Sở thích cá nhân</h2>
        <ul class="danh-sach-so-thich">
          <li class="the-so-thich"><span aria-hidden="true">✈️</span> Đi du lịch &amp; Khám phá</li>
          <li class="the-so-thich"><span aria-hidden="true">🏊</span> Bơi lội</li>
          <li class="the-so-thich"><span aria-hidden="true">🏐</span> Bóng chuyền</li>
          <li class="the-so-thich"><span aria-hidden="true">🎵</span> Nghe nhạc thư giãn</li>
          <li class="the-so-thich"><span aria-hidden="true">🎬</span> Xem phim</li>
        </ul>
      </section>
    </div>
  </article>

  <section class="khoi-thoi-khoa-bieu">
    <h2>Thời khóa biểu học tập trong tuần</h2>

    <div class="bang-cuon">
      <table class="bang-tkb">
        <caption>Lịch học các học phần chính khóa (Bhling Trường - MSSV: 3120124040)</caption>
        <thead>
          <tr>
            <th scope="col">Tiết</th>
            <th scope="col">Thứ 2</th>
            <th scope="col">Thứ 3</th>
            <th scope="col">Thứ 4</th>
            <th scope="col">Thứ 5</th>
            <th scope="col">Thứ 6</th>
            <th scope="col">Thứ 7</th>
            <th scope="col">Chủ nhật</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">1</th>
            <td></td>
            <td rowspan="3">
              <div class="the-mon-hoc the-mon-hoc--khai-pha">
                <span class="the-mon-hoc__ma">31231546 - 24-0301</span>
                <span class="the-mon-hoc__ten">PT &amp; Thiết kế HTTT</span>
                <span class="the-mon-hoc__phong">Phòng B1.106</span>
              </div>
            </td>
            <td></td>
            <td rowspan="3">
              <div class="the-mon-hoc the-mon-hoc--lich-su">
                <span class="the-mon-hoc__ma">21221904 - 24-0319</span>
                <span class="the-mon-hoc__ten">Lịch sử Đảng CSVN</span>
                <span class="the-mon-hoc__phong">Phòng A6-502</span>
              </div>
            </td>
            <td></td>
            <td rowspan="3">
              <div class="the-mon-hoc the-mon-hoc--web">
                <span class="the-mon-hoc__ma">31231755 - 24-0102</span>
                <span class="the-mon-hoc__ten">Thiết kế &amp; Lập trình web</span>
                <span class="the-mon-hoc__phong">Phòng B3-303</span>
              </div>
            </td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">2</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">3</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">4</th>
            <td></td>
            <td></td>
            <td rowspan="3">
              <div class="the-mon-hoc the-mon-hoc--lap-trinh-mang">
                <span class="the-mon-hoc__ma">31231040 - 24-0301</span>
                <span class="the-mon-hoc__ten">PPDH bộ môn Tin học</span>
                <span class="the-mon-hoc__phong">Phòng A5-406</span>
              </div>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">5</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">6</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">7</th>
            <td></td>
            <td rowspan="3">
              <div class="the-mon-hoc the-mon-hoc--attt">
                <span class="the-mon-hoc__ma">31241014 - 26-0101</span>
                <span class="the-mon-hoc__ten">Toán rời rạc</span>
                <span class="the-mon-hoc__phong">Phòng B3-206</span>
              </div>
            </td>
            <td rowspan="4">
              <div class="the-mon-hoc the-mon-hoc--csdl">
                <span class="the-mon-hoc__ma">31241283 - 24-0301</span>
                <span class="the-mon-hoc__ten">Hệ quản trị CSDL</span>
                <span class="the-mon-hoc__phong">Phòng B1.104</span>
              </div>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">8</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">9</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">10</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">11</th>
            <td rowspan="2">
              <div class="the-mon-hoc the-mon-hoc--cnpm">
                <span class="the-mon-hoc__ma">31241014 - 26-0101</span>
                <span class="the-mon-hoc__ten">Toán rời rạc</span>
                <span class="the-mon-hoc__phong">Phòng B3-201</span>
              </div>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">12</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- CHỨC NĂNG PHẦN B 1: BỘ ĐẾM SỐ LƯỢT XEM THEO NGÀY (CSDL MySQL: bảng tv_truong_luot_xem) -->
  <section class="hop-dem-views" style="margin: 25px 0; padding: 20px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <h2>📊 Thống kê ghé thăm trang cá nhân (MySQL)</h2>
    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 16px;">
      <div style="background: #ffffff; padding: 12px 18px; border-radius: 6px; border-left: 4px solid #0284c7; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="font-size: 0.9rem; color: #64748b; display: block;">Tổng số lượt ghé thăm</span>
        <strong style="color: #0284c7; font-size: 1.5rem;"><?= e((string)$tongLuotXem) ?></strong> lượt
      </div>
      <div style="background: #ffffff; padding: 12px 18px; border-radius: 6px; border-left: 4px solid #16a34a; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="font-size: 0.9rem; color: #64748b; display: block;">Lượt xem hôm nay</span>
        <strong style="color: #16a34a; font-size: 1.5rem;"><?= e((string)$luotXemHomNay) ?></strong> lượt
      </div>
    </div>

    <p style="margin: 0 0 12px; font-size: 0.9rem; color: #64748b;">
      (Cơ chế: Lưu trữ trực tiếp vào bảng <code>tv_truong_luot_xem</code> qua <code>INSERT ... ON DUPLICATE KEY UPDATE</code>; kết hợp Session PHP máy chủ nên F5 tải lại trang không tăng lượt đếm).
    </p>

    <h3 style="font-size: 1.05rem; margin-bottom: 8px; color: #334155;">Lịch sử ghé thăm 7 ngày gần nhất</h3>
    <div class="bang-cuon" style="max-width: 480px;">
      <table style="width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 6px; overflow: hidden; font-size: 0.95rem;">
        <thead>
          <tr style="background: #e2e8f0; color: #1e293b; text-align: left;">
            <th scope="col" style="padding: 8px 12px; border-bottom: 1px solid #cbd5e1;">Ngày ghi nhận</th>
            <th scope="col" style="padding: 8px 12px; border-bottom: 1px solid #cbd5e1; text-align: right;">Số lượt ghé thăm</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($lichSu7Ngay)): ?>
            <tr><td colspan="2" style="padding: 10px 12px; text-align: center; color: #94a3b8;">Chưa có dữ liệu thống kê.</td></tr>
          <?php else: ?>
            <?php foreach ($lichSu7Ngay as $dongNgay): ?>
              <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 8px 12px;"><?= e((string)$dongNgay['ngay_dinh_dang']) ?></td>
                <td style="padding: 8px 12px; text-align: right; font-weight: bold; color: #0284c7;"><?= e((string)$dongNgay['so_luot']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- CHỨC NĂNG PHẦN B 2: SỔ LƯU BÚT BẠN BÈ (CSDL MySQL: bảng tv_luu_but) -->
  <section id="so-luu-but" class="hop-luu-but" style="margin: 25px 0; padding: 20px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <h2>✍️ Sổ lưu bút &amp; Lời nhắn cho Trường (MySQL)</h2>

    <!-- THANH TRỢ GIÚP DEV & PHÂN QUYỀN CHỦ TRANG -->
    <div style="margin-bottom: 15px; padding: 10px 14px; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 6px; font-size: 0.9rem;">
      <?php if ($laChuTrang): ?>
        <span style="color: #0369a1; font-weight: bold;">
          🔑 Bạn đang đăng nhập với tư cách chủ trang (Bhling Trường - <code>truong@ued.vn</code>). Bạn có quyền Xóa lời nhắn.
        </span>
        <a href="gioithieu.php?dang_xuat_chu_trang=1#so-luu-but" style="margin-left: 10px; color: #b91c1c; font-weight: bold;">[Đăng xuất chủ trang]</a>
      <?php else: ?>
        <span style="color: #475569;">
          👤 Bạn đang xem với tư cách khách. Muốn thử tính năng Xóa lời nhắn của chủ trang?
        </span>
        <a href="gioithieu.php?dang_nhap_chu_trang=1#so-luu-but" class="nut nut--phu" style="padding: 4px 10px; font-size: 0.85rem; margin-left: 8px;">Đăng nhập nhanh chủ trang (Trường)</a>
      <?php endif; ?>
    </div>

    <?php if ($thongBaoThanhCong !== ''): ?>
      <p style="padding: 10px 14px; background: #dcfce7; color: #166534; border-radius: 4px; font-weight: bold;" role="status">
        ✓ <?= e($thongBaoThanhCong) ?>
      </p>
    <?php endif; ?>

    <?php if ($thongBaoLoi !== ''): ?>
      <p style="padding: 10px 14px; background: #fee2e2; color: #991b1b; border-radius: 4px; font-weight: bold;" role="alert">
        ✕ <?= e($thongBaoLoi) ?>
      </p>
    <?php endif; ?>

    <!-- BIỂU MẪU GỬI LỜI NHẮN (POST & PRG) -->
    <form method="post" action="gioithieu.php#so-luu-but" style="margin-bottom: 25px;">
      <div style="margin-bottom: 12px;">
        <label for="ho_ten" style="display: block; font-weight: bold; margin-bottom: 4px;">Họ tên của bạn (bắt buộc):</label>
        <input type="text" id="ho_ten" name="ho_ten" required minlength="2" maxlength="50" value="<?= e($duLuuBut['ho_ten']) ?>" placeholder="Nhập tên của bạn (2 - 50 ký tự)..." style="width: 100%; max-width: 450px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px;">
      </div>
      <div style="margin-bottom: 12px;">
        <label for="loi_nhan" style="display: block; font-weight: bold; margin-bottom: 4px;">Lời nhắn (bắt buộc):</label>
        <textarea id="loi_nhan" name="loi_nhan" rows="3" required minlength="2" maxlength="500" placeholder="Viết vài dòng lời nhắn cho Trường (2 - 500 ký tự)..." style="width: 100%; max-width: 550px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px;"><?= e($duLuuBut['loi_nhan']) ?></textarea>
      </div>
      <button type="submit" name="gui_luu_but" class="nut nut--chinh" style="padding: 8px 20px; cursor: pointer; border-radius: 4px;">Gửi lưu bút</button>
    </form>

    <!-- DANH SÁCH LỜI NHẮN ĐÃ NHẬN -->
    <div style="display: flex; justify-content: space-between; align-items: baseline; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px;">
      <h3 style="margin: 0;">Danh sách lời nhắn (Tổng: <?= e((string)$tongSoLuuBut) ?>)</h3>
      <span style="font-size: 0.9rem; color: #64748b;">Trang <?= e((string)$trangHienTai) ?> / <?= e((string)$soTrang) ?></span>
    </div>

    <?php if (empty($danhSachLuuBut)): ?>
      <p style="font-style: italic; color: #64748b;">Chưa có lời nhắn nào được ghi lại. Hãy là người đầu tiên để lại lời nhắn nhé!</p>
    <?php else: ?>
      <ul style="list-style: none; padding-left: 0; margin: 0 0 20px 0;">
        <?php foreach ($danhSachLuuBut as $dong): ?>
          <li style="border-bottom: 1px dashed #cbd5e1; padding: 12px 0; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
            <div style="flex: 1;">
              <strong style="color: #1e293b; font-size: 1rem;"><?= e((string)$dong['ten_khach']) ?></strong> 
              <span style="font-size: 0.85rem; color: #64748b; margin-left: 6px;">(<?= e((string)$dong['ngay_gio']) ?>)</span>
              <p style="margin: 6px 0 0; color: #334155; line-height: 1.5;"><?= nl2br(e((string)$dong['loi_nhan'])) ?></p>
            </div>

            <!-- Nút Xóa: Chỉ hiển thị khi chủ trang Trường đăng nhập -->
            <?php if ($laChuTrang): ?>
              <form method="post" action="gioithieu.php#so-luu-but" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lời nhắn này?');" style="margin: 0;">
                <input type="hidden" name="xoa_id" value="<?= e((string)$dong['id']) ?>">
                <button type="submit" name="xoa_luu_but" class="nut nut--phu" style="padding: 4px 8px; font-size: 0.8rem; color: #b91c1c; border-color: #fca5a5; background: #fff;" title="Chỉ chủ trang có quyền xóa">
                  ✕ Xóa
                </button>
              </form>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- BỘ PHÂN TRANG (PAGINATION) -->
      <?php if ($soTrang > 1): ?>
        <nav aria-label="Phân trang sổ lưu bút" style="display: flex; gap: 6px; align-items: center; justify-content: center; margin-top: 15px;">
          <?php for ($i = 1; $i <= $soTrang; $i++): ?>
            <a href="gioithieu.php?trang=<?= e((string)$i) ?>#so-luu-but" 
               class="nut <?= $i === $trangHienTai ? 'nut--chinh' : 'nut--phu' ?>"
               style="padding: 6px 12px; font-size: 0.9rem;"
               <?= $i === $trangHienTai ? 'aria-current="page"' : '' ?>>
              <?= e((string)$i) ?>
            </a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </section>

  <div class="khung-hanh-dong-ca-nhan">
    <a class="nut nut--vien" href="../../gioi-thieu.php">&larr; Quay về danh sách nhóm</a>
    <a class="nut nut--chinh" href="../../chi-tiet.php?id=1">Xem giáo trình tiêu biểu &rarr;</a>
  </div>
</main>

<!-- Nhúng JavaScript riêng của cá nhân -->
<script type="module" src="js/canhan.js"></script>

<?php
// Nhúng khung chân trang chung của nhóm
require_once __DIR__ . '/../../inc/footer.php';
?>
