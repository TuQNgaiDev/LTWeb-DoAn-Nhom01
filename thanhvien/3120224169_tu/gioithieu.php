<?php
/**
 * Trang cá nhân thành viên: Trần Văn Tú (MSSV: 3120224169) - Nhóm 01 UEDBookHub.
 * Hai chức năng CSDL MySQL (PDO) theo yêu cầu Phần B - Bài tập nhóm 6:
 * 1. Quản lý Bảng điểm học phần & Tính GPA tích lũy CSDL (bảng tv_tu_bang_diem):
 *    - Nâng cấp từ Máy tính điểm Bài 5: Lưu học phần, số tín chỉ, điểm A1-A2-A3, tự tính tổng kết hệ 10, quy đổi điểm chữ và hệ 4.
 *    - Phân quyền chủ trang: Chỉ khi chủ trang Tú đăng nhập (admin@ued.vn) mới được quyền xóa môn học (chống IDOR).
 * 2. Bảng điều khiển Thống kê Đồ án (Business Intelligence Dashboard từ CSDL UEDBookHub):
 *    - Khai thác trực tiếp CSDL đồ án bằng JOIN + GROUP BY + SUM + COUNT: Top giáo trình bán chạy và doanh thu theo danh mục.
 * Cách thử nghiệm:
 * - Nhập điểm học phần: Báo lỗi nếu thiếu hoặc điểm ngoài [0, 10]; nhập hợp lệ lưu CSDL và F5 không bị gửi lặp form (PRG pattern).
 * - Phân quyền xóa môn: Khi chưa đăng nhập, không có nút Xóa. Khi đăng nhập admin@ued.vn (mật khẩu matkhau123), nút Xóa xuất hiện cạnh từng môn học.
 * - Thống kê đồ án: Đọc dữ liệu thực tế từ các bảng chi_tiet_don, giao_trinh, don_hang.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

const MSSV = '3120224169';
$goc = '../../';

// Chế độ dev: Hỗ trợ mô phỏng đăng nhập nhanh cho kiểm thử & chụp ảnh
if (defined('MOI_TRUONG') && MOI_TRUONG === 'dev') {
    if (isset($_GET['dang_nhap_chu_trang'])) {
        $_SESSION['uid'] = 1;
    } elseif (isset($_GET['dang_xuat_chu_trang'])) {
        unset($_SESSION['uid']);
    }
}

// --- Xác thực thông tin thành viên và quyền chủ trang ---
$stTv = db()->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$stTv->execute([MSSV]);
$tv = $stTv->fetch();

$ndHienTai = nguoiDung();
$laChuTrang = $tv && !empty($tv['nguoi_dung_id']) && ($ndHienTai['id'] ?? 0) === (int) $tv['nguoi_dung_id'];

// --- Hàm hỗ trợ quy đổi điểm chữ và thang điểm 4 ---
function quyDoiDiem(float $diem10): array {
    if ($diem10 >= 8.5) return ['A', 4.0, 'xuất sắc'];
    if ($diem10 >= 8.0) return ['B+', 3.5, 'giỏi'];
    if ($diem10 >= 7.0) return ['B', 3.0, 'khá'];
    if ($diem10 >= 6.5) return ['C+', 2.5, 'trung bình khá'];
    if ($diem10 >= 5.5) return ['C', 2.0, 'trung bình'];
    if ($diem10 >= 5.0) return ['D+', 1.5, 'trung bình yếu'];
    if ($diem10 >= 4.0) return ['D', 1.0, 'yếu'];
    return ['F', 0.0, 'kém'];
}

// --- CHỨC NĂNG CSDL 1: Xử lý Thêm & Xóa Bảng điểm học phần (POST / PRG) ---
$loiBangDiem = [];
$duBangDiem = ['ten_hoc_phan' => '', 'so_tin_chi' => '3', 'a1' => '', 'a2' => '', 'a3' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1.1 Xóa môn học (chỉ chủ trang đăng nhập mới được xóa)
    if (isset($_POST['xoa_mon_hoc'])) {
        $xoaId = filter_var($_POST['xoa_id'] ?? '', FILTER_VALIDATE_INT);
        if ($xoaId && $laChuTrang) {
            $stXoa = db()->prepare('DELETE FROM tv_tu_bang_diem WHERE id = ? AND mssv = ?');
            $stXoa->execute([$xoaId, MSSV]);
            $_SESSION['flash'] = 'Đã xóa môn học thành công khỏi bảng điểm.';
        } elseif (!$laChuTrang) {
            $_SESSION['flash_loi'] = 'Bạn không có quyền xóa môn học này.';
        }
        chuyenHuong('gioithieu.php#bang-diem-csdl');
        exit;
    }

    // 1.2 Thêm môn học và tính điểm mới
    if (isset($_POST['them_mon_hoc'])) {
        $duBangDiem['ten_hoc_phan'] = trim($_POST['ten_hoc_phan'] ?? '');
        $soTinChi = filter_var($_POST['so_tin_chi'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
        $a1 = filter_var($_POST['a1'] ?? '', FILTER_VALIDATE_FLOAT);
        $a2 = filter_var($_POST['a2'] ?? '', FILTER_VALIDATE_FLOAT);
        $a3 = filter_var($_POST['a3'] ?? '', FILTER_VALIDATE_FLOAT);

        if ($duBangDiem['ten_hoc_phan'] === '') {
            $loiBangDiem['ten_hoc_phan'] = 'Vui lòng nhập tên học phần.';
        } elseif (mb_strlen($duBangDiem['ten_hoc_phan']) > 120) {
            $loiBangDiem['ten_hoc_phan'] = 'Tên học phần không được vượt quá 120 ký tự.';
        }

        if ($soTinChi === false) {
            $loiBangDiem['so_tin_chi'] = 'Số tín chỉ từ 1 đến 10.';
        }

        if ($a1 === false || $a1 < 0 || $a1 > 10) {
            $loiBangDiem['a1'] = 'Điểm A1 từ 0 đến 10.';
        }
        if ($a2 === false || $a2 < 0 || $a2 > 10) {
            $loiBangDiem['a2'] = 'Điểm A2 từ 0 đến 10.';
        }
        if ($a3 === false || $a3 < 0 || $a3 > 10) {
            $loiBangDiem['a3'] = 'Điểm A3 từ 0 đến 10.';
        }

        if (!$loiBangDiem) {
            $tongKet = round(0.2 * (float)$a1 + 0.3 * (float)$a2 + 0.5 * (float)$a3, 1);
            $stThem = db()->prepare('INSERT INTO tv_tu_bang_diem (mssv, ten_hoc_phan, so_tin_chi, a1, a2, a3, tong_ket) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stThem->execute([MSSV, $duBangDiem['ten_hoc_phan'], $soTinChi, $a1, $a2, $a3, $tongKet]);
            $_SESSION['flash'] = "Đã lưu học phần '{$duBangDiem['ten_hoc_phan']}' với điểm tổng kết {$tongKet} vào CSDL!";
            chuyenHuong('gioithieu.php#bang-diem-csdl');
            exit;
        }
    }
}

// Đọc danh sách bảng điểm và tính GPA tích lũy
$dsBangDiem = [];
$tongTinChi = 0;
$tongDiemHe10NhanTinChi = 0.0;
$tongDiemHe4NhanTinChi = 0.0;

try {
    $stBd = db()->prepare('SELECT id, ten_hoc_phan, so_tin_chi, a1, a2, a3, tong_ket, ngay_tao FROM tv_tu_bang_diem WHERE mssv = ? ORDER BY id DESC');
    $stBd->execute([MSSV]);
    $dsBangDiem = $stBd->fetchAll();

    foreach ($dsBangDiem as $row) {
        $tc = (int)$row['so_tin_chi'];
        $tk = (float)$row['tong_ket'];
        $qd = quyDoiDiem($tk);
        $tongTinChi += $tc;
        $tongDiemHe10NhanTinChi += ($tk * $tc);
        $tongDiemHe4NhanTinChi += ($qd[1] * $tc);
    }
} catch (PDOException $e) {
    error_log('Lỗi đọc bảng điểm: ' . $e->getMessage());
}

$gpa10 = $tongTinChi > 0 ? round($tongDiemHe10NhanTinChi / $tongTinChi, 2) : 0.0;
$gpa4 = $tongTinChi > 0 ? round($tongDiemHe4NhanTinChi / $tongTinChi, 2) : 0.0;

// --- CHỨC NĂNG CSDL 2: Thống kê đồ án UEDBookHub (JOIN + GROUP BY) ---
$topSachBanChay = [];
$doanhThuDanhMuc = [];
$tongDoanhThuHeThong = 0;
$tongDonHangHeThong = 0;

try {
    // 2.1 Top 5 giáo trình bán chạy nhất
    $sqlTop = 'SELECT g.id, g.ten, g.gia, d.ten AS ten_danh_muc,
                      COALESCE(SUM(ct.so_luong), 0) AS tong_ban,
                      COALESCE(SUM(ct.so_luong * ct.don_gia), 0) AS doanh_thu
               FROM chi_tiet_don ct
               JOIN giao_trinh g ON ct.giao_trinh_id = g.id
               JOIN danh_muc d ON g.danh_muc_id = d.id
               GROUP BY g.id, g.ten, g.gia, d.ten
               ORDER BY tong_ban DESC, doanh_thu DESC
               LIMIT 5';
    $topSachBanChay = db()->query($sqlTop)->fetchAll();

    // 2.2 Doanh thu và số lượng đầu sách theo từng danh mục
    $sqlDm = 'SELECT d.ten AS ten_danh_muc,
                     COUNT(DISTINCT g.id) AS so_dau_sach,
                     COALESCE(SUM(ct.so_luong), 0) AS tong_sach_ban,
                     COALESCE(SUM(ct.so_luong * ct.don_gia), 0) AS doanh_thu
              FROM danh_muc d
              LEFT JOIN giao_trinh g ON d.id = g.danh_muc_id
              LEFT JOIN chi_tiet_don ct ON g.id = ct.giao_trinh_id
              GROUP BY d.id, d.ten
              ORDER BY doanh_thu DESC';
    $doanhThuDanhMuc = db()->query($sqlDm)->fetchAll();

    // 2.3 Tổng quan toàn hệ thống
    $tongDoanhThuHeThong = (int)db()->query('SELECT COALESCE(SUM(so_luong * don_gia), 0) FROM chi_tiet_don')->fetchColumn();
    $tongDonHangHeThong = (int)db()->query('SELECT COUNT(*) FROM don_hang')->fetchColumn();
} catch (PDOException $e) {
    error_log('Lỗi thống kê đồ án: ' . $e->getMessage());
}

// --- Tiện ích chuyển đổi giao diện Sáng / Tối ghi nhớ bằng Cookie ---
$giaoDien = chuoi($_COOKIE, 'giao_dien_tu');
if ($giaoDien !== 'toi' && $giaoDien !== 'sang') {
    $giaoDien = 'sang';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doi_giao_dien'])) {
    $giaoDienMoi = $giaoDien === 'toi' ? 'sang' : 'toi';
    setcookie('giao_dien_tu', $giaoDienMoi, [
        'expires' => time() + 30 * 24 * 3600,
        'path' => '/',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
    chuyenHuong('gioithieu.php');
    exit;
}

$cacKyNang = [
    ['nhom' => 'quan-ly', 'bieuTuong' => '🎯', 'ten' => 'Quản lý & Vận hành nhóm', 'moTa' => 'Lập kế hoạch phân công công việc, điều phối mã nguồn Git/GitHub, chịu trách nhiệm chính về kiến trúc Website.'],
    ['nhom' => 'lap-trinh', 'bieuTuong' => '💻', 'ten' => 'Thiết kế & Lập trình Web', 'moTa' => 'Sử dụng HTML5 ngữ nghĩa, CSS (Grid, Flexbox, BEM), PHP chuẩn PDO, REST API và bảo mật Web.'],
    ['nhom' => 'lap-trinh', 'bieuTuong' => '⚡', 'ten' => 'Lập trình thuật toán & Ứng dụng', 'moTa' => 'Nắm vững tư duy thuật toán, cấu trúc dữ liệu và lập trình hướng đối tượng với C/C++, Java và Python.'],
    ['nhom' => 'du-lieu', 'bieuTuong' => '🗄️', 'ten' => 'Hệ quản trị Cơ sở dữ liệu', 'moTa' => 'Thiết kế CSDL quan hệ chuẩn 3NF, lập trình prepared statements, giao dịch ACID và phân trang MySQL.'],
    ['nhom' => 'du-lieu', 'bieuTuong' => '🔒', 'ten' => 'An toàn & Bảo mật Web', 'moTa' => 'Bảo vệ hệ thống chống SQL Injection, XSS, CSRF, kiểm soát phân quyền RBAC và băm mật khẩu an toàn.'],
    ['nhom' => 'quan-ly', 'bieuTuong' => '🚀', 'ten' => 'Tự học & Nghiên cứu công nghệ', 'moTa' => 'Đọc hiểu tài liệu kỹ thuật tiếng Anh (MDN, W3C, PHP Manual) và giải quyết sự cố kỹ thuật độc lập.'],
];

$tieuDe = 'Giới thiệu thành viên: Trần Văn Tú';
$trang = '';
$moTa = 'Trang cá nhân Trần Văn Tú - Nhóm 01 UEDBookHub với 2 chức năng CSDL: Quản lý Bảng điểm học phần tính GPA và Thống kê đồ án.';
$cssThem = ['thanhvien/3120224169_tu/css/style.css'];
$dataGiaoDien = $giaoDien;
require __DIR__ . '/../../inc/header.php';
?>
  <main class="noi-dung-chinh">

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="thong-bao-flash thong-bao-flash--thanh-cong" role="status" aria-live="polite">
        <?= e($_SESSION['flash']) ?>
      </div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_loi'])): ?>
      <div class="thong-bao-flash thong-bao-flash--loi" role="alert">
        <?= e($_SESSION['flash_loi']) ?>
      </div>
      <?php unset($_SESSION['flash_loi']); ?>
    <?php endif; ?>

    <section class="bang-dieu-khien-ca-nhan" aria-labelledby="tieu-de-tuong-tac">
      <div>
        <h2 id="tieu-de-tuong-tac" class="bang-dieu-khien-ca-nhan__tieu-de">Góc tương tác của Tú (Phần B)</h2>
        <p>Giao diện Sáng / Tối ghi nhớ qua Cookie. Hiện tại: <strong><?= $giaoDien === 'toi' ? 'Chế độ Tối 🌙' : 'Chế độ Sáng ☀️' ?></strong>.</p>
        <p>Quyền chủ trang:
          <?php if ($laChuTrang): ?>
            <span class="huy-hieu huy-hieu--con">Đã xác thực chủ trang (<?= e($ndHienTai['email'] ?? '') ?>) 🔑</span>
          <?php else: ?>
            <span class="huy-hieu huy-hieu--sap-het">Khách truy cập (Chưa đăng nhập tài khoản chủ trang)</span>
          <?php endif; ?>
        </p>
      </div>
      <form class="form-doi-giao-dien" action="gioithieu.php" method="post">
        <input type="hidden" name="doi_giao_dien" value="1">
        <button id="doi-giao-dien" class="nut nut--phu" type="submit" aria-pressed="<?= $giaoDien === 'toi' ? 'true' : 'false' ?>">
          <?= $giaoDien === 'toi' ? '☀️ Dùng giao diện sáng' : '🌙 Dùng giao diện tối' ?>
        </button>
      </form>
    </section>

    <article class="ho-so-ca-nhan">
      <div class="ho-so-ca-nhan__than">
        <div class="ho-so-ca-nhan__hang-dau">
          <img class="ho-so-ca-nhan__anh" src="../../images/TranVanTu.webp" alt="Ảnh chân dung sinh viên Trần Văn Tú" width="160" height="160">
          <div class="ho-so-ca-nhan__thong-tin">
            <h1 class="ho-so-ca-nhan__ten">Trần Văn Tú</h1>
            <div class="ho-so-ca-nhan__vai-tro">Trưởng nhóm 01 – Đề tài UEDBookHub</div>
            <div class="nhom-nut">
              <span class="huy-hieu huy-hieu--con">MSSV: 3120224169</span>
              <span class="huy-hieu huy-hieu--con">Khoa Toán – Tin</span>
              <span class="huy-hieu huy-hieu--sap-het">Lớp học phần: 24-0102</span>
              <span class="huy-hieu huy-hieu--con">Trường ĐH Sư phạm – ĐH Đà Nẵng</span>
            </div>
          </div>
        </div>

        <section>
          <h2>Giới thiệu bản thân</h2>
          <p class="doan-van-dai">
            Mình là <strong>Trần Văn Tú</strong> (MSSV: <strong>3120224169</strong>), Trưởng nhóm <strong>UEDBookHub</strong>, thành viên dự án <strong>CareHomeAI</strong> đạt giải Nhì cuộc thi khởi nghiệp UED và là <strong>Freelance Photographer</strong>.
          </p>
          <blockquote class="the-phuong-cham">
            <p>“Code bao mượt, rượt lại Deadline !”</p>
          </blockquote>
        </section>

        <section>
          <h2>Kỹ năng chuyên môn</h2>
          <ul class="luoi-ky-nang">
            <?php foreach ($cacKyNang as $kyNang): ?>
            <li class="the-ky-nang">
              <span class="the-ky-nang__bieu-tuong" aria-hidden="true"><?= e($kyNang['bieuTuong']) ?></span>
              <h3 class="the-ky-nang__tieu-de"><?= e($kyNang['ten']) ?></h3>
              <p class="the-ky-nang__mo-ta"><?= e($kyNang['moTa']) ?></p>
            </li>
            <?php endforeach; ?>
          </ul>
        </section>

        <section>
          <h2>Sở thích cá nhân</h2>
          <ul class="danh-sach-so-thich">
            <li class="the-so-thich"><span aria-hidden="true">🎵</span> Âm nhạc &amp; Podcast</li>
            <li class="the-so-thich"><span aria-hidden="true">📸</span> Chụp ảnh Concept</li>
            <li class="the-so-thich"><span aria-hidden="true">☕</span> Cà phê &amp; Coding đêm</li>
            <li class="the-so-thich"><span aria-hidden="true">📖</span> Đọc sách công nghệ</li>
          </ul>
        </section>

        <section>
          <h2>Mục tiêu nghề nghiệp</h2>
          <ol class="danh-sach-muc-tieu">
            <li><strong>Ngắn hạn:</strong> Nắm vững toàn bộ kiến thức Thiết kế &amp; Lập trình Web, dẫn dắt Nhóm 01 hoàn thành xuất sắc đồ án website UEDBookHub.</li>
            <li><strong>Trung hạn:</strong> Trở thành một <strong>Full-stack Web Developer</strong> thành thạo xây dựng hệ thống web động bảo mật, kiến trúc mở rộng và REST API chuẩn mực.</li>
            <li><strong>Dài hạn:</strong> Phát triển bản thân thành <strong>Solution Architect</strong> hoặc Lead Developer trong lĩnh vực công nghệ giáo dục và chuyển đổi số.</li>
          </ol>
        </section>
      </div>
    </article>

    <section class="khoi-thoi-khoa-bieu" aria-labelledby="tieu-de-tkb">
      <h2 id="tieu-de-tkb">Thời khóa biểu cá nhân</h2>
      <div class="bang-cuon">
        <table>
          <caption>Thời khóa biểu học kỳ 1 năm học 2026 – 2027 (Trần Văn Tú)</caption>
          <thead>
            <tr>
              <th scope="col">Thứ</th>
              <th scope="col">Tiết</th>
              <th scope="col">Môn học</th>
              <th scope="col">Phòng học</th>
            </tr>
          </thead>
          <tbody>
            <tr><th scope="row" rowspan="2">Thứ Hai</th><td>Tiết 1 – 3</td><td>Cơ sở dữ liệu</td><td>A5-404C</td></tr>
            <tr><td>Tiết 7 – 9</td><td>Xác suất thống kê</td><td>A6-401</td></tr>
            <tr><th scope="row" rowspan="2">Thứ Ba</th><td>Tiết 1 – 3</td><td>Cấu trúc dữ liệu &amp; Giải thuật</td><td>B1.104</td></tr>
            <tr><td>Tiết 7 – 9</td><td>Toán rời rạc</td><td>A5-404B</td></tr>
            <tr><th scope="row">Thứ Tư</th><td>Tiết 1 – 3</td><td>Lập trình Web (Thực hành)</td><td>B3-503</td></tr>
            <tr><th scope="row">Thứ Năm</th><td>Tiết 7 – 9</td><td>Mạng máy tính</td><td>A5-403</td></tr>
            <tr><th scope="row">Thứ Sáu</th><td>Tiết 7 – 9</td><td>Kiến trúc máy tính</td><td>B3-303</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- CHỨC NĂNG CSDL 1: QUẢN LÝ BẢNG ĐIỂM & TÍNH GPA TÍCH LŨY (BẢNG tv_tu_bang_diem) -->
    <section id="bang-diem-csdl" class="khoi-bang-diem" aria-labelledby="tieu-de-bang-diem">
      <div class="so-luu-but__dau">
        <div>
          <h2 id="tieu-de-bang-diem">Bảng điểm học phần &amp; Tính GPA tích lũy (CSDL MySQL)</h2>
          <p class="so-luu-but__mo-ta">
            Chuyển đổi &amp; nâng cấp từ <em>Máy tính điểm học phần Bài 5</em> sang lưu trữ CSDL bảng <code>tv_tu_bang_diem</code>.
            Tự động tính điểm tổng kết hệ 10: <code>0.2×A1 + 0.3×A2 + 0.5×A3</code>, quy đổi điểm chữ và thang điểm 4.
            <?php if ($laChuTrang): ?>
              <br><strong class="chu-xanh">Bạn đang đăng nhập quyền chủ trang (Tú) nên có quyền xóa môn học khỏi bảng điểm.</strong>
            <?php else: ?>
              <br><span>(Chỉ chủ trang đăng nhập <code>admin@ued.vn</code> mới hiển thị nút Xóa môn học).</span>
            <?php endif; ?>
          </p>
        </div>
      </div>

      <!-- Khối tổng quan GPA tích lũy -->
      <div class="luoi-thong-ke" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 16px 0;">
        <div class="the-thong-ke" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px; text-align: center;">
          <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">TỔNG TÍN CHỈ TÍCH LŨY</div>
          <div style="font-size: 1.8rem; font-weight: 700; color: #1e3a8a; margin-top: 4px;"><?= $tongTinChi ?> tín chỉ</div>
        </div>
        <div class="the-thong-ke" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px; text-align: center;">
          <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">ĐIỂM TRUNG BÌNH HỆ 10</div>
          <div style="font-size: 1.8rem; font-weight: 700; color: #0284c7; margin-top: 4px;"><?= number_format($gpa10, 2) ?> / 10</div>
        </div>
        <div class="the-thong-ke" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px; text-align: center;">
          <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">GPA HỆ 4 &amp; XẾP LOẠI</div>
          <div style="font-size: 1.8rem; font-weight: 700; color: #15803d; margin-top: 4px;">
            <?= number_format($gpa4, 2) ?> / 4.0
            <span style="font-size: 1rem; font-weight: 600; display: block; color: #166534; text-transform: uppercase;">
              <?= $gpa4 >= 3.6 ? 'Xuất sắc' : ($gpa4 >= 3.2 ? 'Giỏi' : ($gpa4 >= 2.5 ? 'Khá' : 'Trung bình')) ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Form nhập môn học mới -->
      <form class="bieu-mau" action="gioithieu.php#bang-diem-csdl" method="post" novalidate style="margin-top: 16px;">
        <input type="hidden" name="them_mon_hoc" value="1">
        <h3 style="font-size: 1.15rem; margin-bottom: 12px; color: #1e293b;">+ Thêm học phần và lưu vào CSDL</h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
          <div class="bieu-mau__nhom" style="grid-column: span 2;">
            <label for="ten_hoc_phan">Tên học phần <span class="dau-sao" aria-hidden="true">*</span></label>
            <input class="bieu-mau__o-nhap <?= isset($loiBangDiem['ten_hoc_phan']) ? 'bieu-mau__o-nhap--loi' : '' ?>"
                   id="ten_hoc_phan" name="ten_hoc_phan" type="text" maxlength="120"
                   value="<?= e($duBangDiem['ten_hoc_phan']) ?>" required
                   placeholder="Ví dụ: An toàn thông tin, Trí tuệ nhân tạo...">
            <?php if (isset($loiBangDiem['ten_hoc_phan'])): ?>
              <p class="bieu-mau__loi"><?= e($loiBangDiem['ten_hoc_phan']) ?></p>
            <?php endif; ?>
          </div>

          <div class="bieu-mau__nhom">
            <label for="so_tin_chi">Số tín chỉ <span class="dau-sao" aria-hidden="true">*</span></label>
            <input class="bieu-mau__o-nhap <?= isset($loiBangDiem['so_tin_chi']) ? 'bieu-mau__o-nhap--loi' : '' ?>"
                   id="so_tin_chi" name="so_tin_chi" type="number" min="1" max="10"
                   value="<?= e($duBangDiem['so_tin_chi']) ?>" required>
            <?php if (isset($loiBangDiem['so_tin_chi'])): ?>
              <p class="bieu-mau__loi"><?= e($loiBangDiem['so_tin_chi']) ?></p>
            <?php endif; ?>
          </div>

          <div class="bieu-mau__nhom">
            <label for="a1">Điểm A1 (20%) <span class="dau-sao" aria-hidden="true">*</span></label>
            <input class="bieu-mau__o-nhap <?= isset($loiBangDiem['a1']) ? 'bieu-mau__o-nhap--loi' : '' ?>"
                   id="a1" name="a1" type="number" step="0.1" min="0" max="10"
                   value="<?= e($duBangDiem['a1']) ?>" required placeholder="0 - 10">
            <?php if (isset($loiBangDiem['a1'])): ?>
              <p class="bieu-mau__loi"><?= e($loiBangDiem['a1']) ?></p>
            <?php endif; ?>
          </div>

          <div class="bieu-mau__nhom">
            <label for="a2">Điểm A2 (30%) <span class="dau-sao" aria-hidden="true">*</span></label>
            <input class="bieu-mau__o-nhap <?= isset($loiBangDiem['a2']) ? 'bieu-mau__o-nhap--loi' : '' ?>"
                   id="a2" name="a2" type="number" step="0.1" min="0" max="10"
                   value="<?= e($duBangDiem['a2']) ?>" required placeholder="0 - 10">
            <?php if (isset($loiBangDiem['a2'])): ?>
              <p class="bieu-mau__loi"><?= e($loiBangDiem['a2']) ?></p>
            <?php endif; ?>
          </div>

          <div class="bieu-mau__nhom">
            <label for="a3">Điểm A3 (50%) <span class="dau-sao" aria-hidden="true">*</span></label>
            <input class="bieu-mau__o-nhap <?= isset($loiBangDiem['a3']) ? 'bieu-mau__o-nhap--loi' : '' ?>"
                   id="a3" name="a3" type="number" step="0.1" min="0" max="10"
                   value="<?= e($duBangDiem['a3']) ?>" required placeholder="0 - 10">
            <?php if (isset($loiBangDiem['a3'])): ?>
              <p class="bieu-mau__loi"><?= e($loiBangDiem['a3']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <button class="nut nut--chinh" type="submit" style="margin-top: 10px;">Lưu học phần vào CSDL</button>
      </form>

      <!-- Bảng danh sách môn học từ CSDL -->
      <div class="bang-cuon" style="margin-top: 20px;">
        <table>
          <caption>Danh sách học phần tích lũy của Trần Văn Tú trong CSDL (tv_tu_bang_diem)</caption>
          <thead>
            <tr>
              <th scope="col">STT</th>
              <th scope="col">Tên học phần</th>
              <th scope="col">Số TC</th>
              <th scope="col">A1 (20%)</th>
              <th scope="col">A2 (30%)</th>
              <th scope="col">A3 (50%)</th>
              <th scope="col">Tổng kết (10)</th>
              <th scope="col">Điểm chữ</th>
              <th scope="col">Thang 4</th>
              <?php if ($laChuTrang): ?>
                <th scope="col">Thao tác</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (!$dsBangDiem): ?>
              <tr><td colspan="<?= $laChuTrang ? 10 : 9 ?>" style="text-align: center; color: #64748b;">Chưa có học phần nào được lưu trong CSDL.</td></tr>
            <?php else: ?>
              <?php foreach ($dsBangDiem as $idx => $mh): ?>
                <?php $qd = quyDoiDiem((float)$mh['tong_ket']); ?>
                <tr>
                  <td style="text-align: center;"><?= $idx + 1 ?></td>
                  <td><strong><?= e($mh['ten_hoc_phan']) ?></strong></td>
                  <td style="text-align: center;"><?= (int)$mh['so_tin_chi'] ?></td>
                  <td style="text-align: center;"><?= number_format((float)$mh['a1'], 1) ?></td>
                  <td style="text-align: center;"><?= number_format((float)$mh['a2'], 1) ?></td>
                  <td style="text-align: center;"><?= number_format((float)$mh['a3'], 1) ?></td>
                  <td style="text-align: center; font-weight: bold; color: #1e3a8a;"><?= number_format((float)$mh['tong_ket'], 1) ?></td>
                  <td style="text-align: center; font-weight: bold;"><?= $qd[0] ?></td>
                  <td style="text-align: center; font-weight: bold; color: #15803d;"><?= number_format($qd[1], 1) ?></td>
                  <?php if ($laChuTrang): ?>
                    <td style="text-align: center;">
                      <form action="gioithieu.php#bang-diem-csdl" method="post" onsubmit="return confirm('Bạn có chắc muốn xóa học phần này khỏi bảng điểm?');" style="display: inline;">
                        <input type="hidden" name="xoa_mon_hoc" value="1">
                        <input type="hidden" name="xoa_id" value="<?= (int)$mh['id'] ?>">
                        <button class="nut nut--nguy-hiem nut--nho" type="submit">Xóa</button>
                      </form>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- CHỨC NĂNG CSDL 2: BẢNG ĐIỀU KHIỂN THỐNG KÊ ĐỒ ÁN UEDBOOKHUB (JOIN + GROUP BY) -->
    <section id="thong-ke-do-an" class="khoi-thong-ke-do-an" aria-labelledby="tieu-de-thong-ke" style="margin-top: 36px;">
      <h2 id="tieu-de-thong-ke">Thống kê dữ liệu Đồ án UEDBookHub (CSDL MySQL)</h2>
      <p class="so-luu-but__mo-ta">
        Truy vấn trực tiếp từ CSDL đồ án bằng các câu lệnh phức hợp <code>JOIN + GROUP BY + SUM + COUNT</code> để phân tích kinh doanh.
      </p>

      <!-- Khối tóm tắt toàn hệ thống -->
      <div class="luoi-thong-ke" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin: 16px 0;">
        <div class="the-thong-ke" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px; text-align: center;">
          <div style="font-size: 0.9rem; color: #166534; font-weight: 600;">TỔNG DOANH THU ĐỒ ÁN</div>
          <div style="font-size: 1.8rem; font-weight: 700; color: #15803d; margin-top: 4px;"><?= vnd($tongDoanhThuHeThong) ?></div>
        </div>
        <div class="the-thong-ke" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; text-align: center;">
          <div style="font-size: 0.9rem; color: #1e40af; font-weight: 600;">TỔNG SỐ ĐƠN HÀNG ĐÃ ĐẶT</div>
          <div style="font-size: 1.8rem; font-weight: 700; color: #1d4ed8; margin-top: 4px;"><?= $tongDonHangHeThong ?> đơn hàng</div>
        </div>
      </div>

      <!-- 2.1 Bảng Top 5 giáo trình bán chạy nhất -->
      <h3 style="font-size: 1.15rem; margin-top: 20px; color: #1e3a8a;">1. Top 5 Giáo trình bán chạy nhất (chi_tiet_don JOIN giao_trinh)</h3>
      <div class="bang-cuon">
        <table>
          <caption>Top 5 giáo trình có số lượng đặt mua nhiều nhất trong hệ thống</caption>
          <thead>
            <tr>
              <th scope="col">Top</th>
              <th scope="col">Tên giáo trình</th>
              <th scope="col">Danh mục</th>
              <th scope="col">Đơn giá</th>
              <th scope="col">Số lượng đã bán</th>
              <th scope="col">Tổng doanh thu</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$topSachBanChay): ?>
              <tr><td colspan="6" style="text-align: center; color: #64748b;">Chưa có dữ liệu đặt hàng.</td></tr>
            <?php else: ?>
              <?php foreach ($topSachBanChay as $idx => $s): ?>
                <tr>
                  <td style="text-align: center; font-weight: bold;">#<?= $idx + 1 ?></td>
                  <td><strong><?= e($s['ten']) ?></strong></td>
                  <td><?= e($s['ten_danh_muc']) ?></td>
                  <td style="text-align: right;"><?= vnd((int)$s['gia']) ?></td>
                  <td style="text-align: center; font-weight: bold; color: #0284c7;"><?= (int)$s['tong_ban'] ?> cuốn</td>
                  <td style="text-align: right; font-weight: bold; color: #15803d;"><?= vnd((int)$s['doanh_thu']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- 2.2 Bảng Thống kê doanh thu theo danh mục -->
      <h3 style="font-size: 1.15rem; margin-top: 24px; color: #1e3a8a;">2. Doanh thu &amp; Số lượng đầu sách theo Danh mục (danh_muc JOIN giao_trinh)</h3>
      <div class="bang-cuon">
        <table>
          <caption>Thống kê số lượng đầu sách và doanh thu phân bổ theo từng chuyên ngành</caption>
          <thead>
            <tr>
              <th scope="col">STT</th>
              <th scope="col">Danh mục chuyên ngành</th>
              <th scope="col">Số đầu sách hiện có</th>
              <th scope="col">Số lượng sách đã bán</th>
              <th scope="col">Tổng doanh thu phát sinh</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($doanhThuDanhMuc as $idx => $dm): ?>
              <tr>
                <td style="text-align: center;"><?= $idx + 1 ?></td>
                <td><strong><?= e($dm['ten_danh_muc']) ?></strong></td>
                <td style="text-align: center;"><?= (int)$dm['so_dau_sach'] ?> giáo trình</td>
                <td style="text-align: center;"><?= (int)$dm['tong_sach_ban'] ?> cuốn</td>
                <td style="text-align: right; font-weight: bold; color: <?= (int)$dm['doanh_thu'] > 0 ? '#15803d' : '#64748b' ?>;">
                  <?= (int)$dm['doanh_thu'] > 0 ? vnd((int)$dm['doanh_thu']) : 'Chưa có đơn' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
