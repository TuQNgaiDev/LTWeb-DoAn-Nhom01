<?php
/**
 * Trang cá nhân thành viên: Trần Văn Tú (MSSV: 3120224169) - Nhóm 01 UEDBookHub.
 * Hai chức năng CSDL MySQL (PDO) theo yêu cầu Phần B - Bài tập nhóm 6:
 * 1. Sổ lưu bút có phân trang (5 lời nhắn/trang, mới nhất trước) và chỉ chủ trang đăng nhập (admin@ued.vn) mới được quyền xóa lời nhắn (chống IDOR).
 * 2. Bộ đếm lượt xem theo ngày (INSERT ... ON DUPLICATE KEY UPDATE) kèm bảng thống kê 7 ngày gần nhất và tổng lượt xem.
 * Cách thử nghiệm:
 * - Gửi lời nhắn: Để trống (báo lỗi); nhập hợp lệ rồi gửi -> F5 không bị gửi lặp form (PRG pattern).
 * - Phân trang: Bấm trang 2 (?trang=2) xem các lời nhắn trước đó.
 * - Phân quyền xóa: Khi chưa đăng nhập (hoặc tài khoản khách), không có nút Xóa. Khi đăng nhập admin@ued.vn (mật khẩu matkhau123), nút Xóa xuất hiện cạnh từng lời nhắn.
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

// --- Chức năng CSDL 2: Bộ đếm lượt xem theo ngày (INSERT ... ON DUPLICATE KEY UPDATE) ---
$khoaSessionXem = 'tu_da_xem_ngay_' . date('Ymd');
if (empty($_SESSION[$khoaSessionXem])) {
    $_SESSION[$khoaSessionXem] = true;
    try {
        $stDemXem = db()->prepare('INSERT INTO tv_tu_luot_xem (mssv, ngay, so_luot)
            VALUES (?, CURDATE(), 1)
            ON DUPLICATE KEY UPDATE so_luot = so_luot + 1');
        $stDemXem->execute([MSSV]);
    } catch (PDOException $e) {
        error_log('Lỗi cập nhật lượt xem: ' . $e->getMessage());
    }
}

// Lấy thống kê lượt xem 7 ngày gần nhất và tổng lượt xem
$dsLuotXem = [];
$tongLuotXem = 0;
try {
    $stTk = db()->prepare('SELECT ngay, so_luot FROM tv_tu_luot_xem WHERE mssv = ? ORDER BY ngay DESC LIMIT 7');
    $stTk->execute([MSSV]);
    $dsLuotXem = $stTk->fetchAll();

    $stTong = db()->prepare('SELECT COALESCE(SUM(so_luot), 0) FROM tv_tu_luot_xem WHERE mssv = ?');
    $stTong->execute([MSSV]);
    $tongLuotXem = (int) $stTong->fetchColumn();
} catch (PDOException $e) {
    error_log('Lỗi đọc thống kê lượt xem: ' . $e->getMessage());
}

// --- Chức năng CSDL 1: Xử lý Gửi và Xóa lưu bút (POST / PRG) ---
$loiLuuBut = [];
$duLuuBut = ['ten_khach' => '', 'loi_nhan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1.1 Xóa lời nhắn (chỉ chủ trang đăng nhập mới được xóa)
    if (isset($_POST['xoa_luu_but'])) {
        $xoaId = filter_var($_POST['xoa_id'] ?? '', FILTER_VALIDATE_INT);
        if ($xoaId && $laChuTrang) {
            $stXoa = db()->prepare('DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?');
            $stXoa->execute([$xoaId, MSSV]);
            $_SESSION['flash'] = 'Đã xóa lời nhắn thành công.';
        } elseif (!$laChuTrang) {
            $_SESSION['flash_loi'] = 'Bạn không có quyền xóa lời nhắn này.';
        }
        chuyenHuong('gioithieu.php#so-luu-but');
        exit;
    }

    // 1.2 Gửi lời nhắn mới
    if (isset($_POST['gui_luu_but'])) {
        $duLuuBut['ten_khach'] = trim($_POST['ten_khach'] ?? '');
        $duLuuBut['loi_nhan'] = trim($_POST['loi_nhan'] ?? '');

        if ($duLuuBut['ten_khach'] === '') {
            $loiLuuBut['ten_khach'] = 'Vui lòng nhập họ và tên của bạn.';
        } elseif (mb_strlen($duLuuBut['ten_khach']) > 50) {
            $loiLuuBut['ten_khach'] = 'Tên không được vượt quá 50 ký tự.';
        }

        if ($duLuuBut['loi_nhan'] === '') {
            $loiLuuBut['loi_nhan'] = 'Vui lòng nhập nội dung lời nhắn.';
        } elseif (mb_strlen($duLuuBut['loi_nhan']) > 500) {
            $loiLuuBut['loi_nhan'] = 'Lời nhắn không được vượt quá 500 ký tự.';
        }

        if (!$loiLuuBut) {
            $stThem = db()->prepare('INSERT INTO tv_luu_but (mssv, ten_khach, loi_nhan) VALUES (?, ?, ?)');
            $stThem->execute([MSSV, $duLuuBut['ten_khach'], $duLuuBut['loi_nhan']]);
            $_SESSION['flash'] = 'Cảm ơn bạn đã để lại lời nhắn lưu bút!';
            chuyenHuong('gioithieu.php#so-luu-but');
            exit;
        }
    }
}

// --- Chức năng CSDL 1 (tiếp): Phân trang danh sách lưu bút ---
$tongLuuBut = 0;
$dsLoiNhan = [];
$moiTrangLuuBut = 5;
$soTrangLuuBut = 1;
$trangLuuBut = 1;

try {
    $stDem = db()->prepare('SELECT COUNT(*) FROM tv_luu_but WHERE mssv = ?');
    $stDem->execute([MSSV]);
    $tongLuuBut = (int) $stDem->fetchColumn();

    $soTrangLuuBut = max(1, (int) ceil($tongLuuBut / $moiTrangLuuBut));
    $trangLuuBut = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'default' => 1]
    ]);
    $trangLuuBut = min($trangLuuBut, $soTrangLuuBut);

    $stLb = db()->prepare('SELECT id, ten_khach, loi_nhan, thoi_gian
        FROM tv_luu_but WHERE mssv = ?
        ORDER BY thoi_gian DESC, id DESC
        LIMIT 5 OFFSET ?');
    $stLb->execute([MSSV, ($trangLuuBut - 1) * $moiTrangLuuBut]);
    $dsLoiNhan = $stLb->fetchAll();
} catch (PDOException $e) {
    error_log('Lỗi truy vấn lưu bút: ' . $e->getMessage());
}

// --- Tiện ích phụ: Chuyển đổi giao diện Sáng / Tối ghi nhớ bằng Cookie ---
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

// --- Tiện ích phụ: Máy tính điểm học phần (POST / PRG) ---
$duDiem = ['a1' => '', 'a2' => '', 'a3' => ''];
$loiDiem = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tinh_diem'])) {
    foreach ($duDiem as $key => $_) {
        $duDiem[$key] = chuoi($_POST, $key);
        if ($duDiem[$key] === '') {
            $loiDiem[$key] = 'Vui lòng nhập điểm.';
        } else {
            $giaTri = filter_var($duDiem[$key], FILTER_VALIDATE_FLOAT);
            if ($giaTri === false || !is_finite((float) $giaTri) || $giaTri < 0 || $giaTri > 10) {
                $loiDiem[$key] = 'Điểm phải là số từ 0 đến 10.';
            }
        }
    }

    if (!$loiDiem) {
        $a1 = (float) $duDiem['a1'];
        $a2 = (float) $duDiem['a2'];
        $a3 = (float) $duDiem['a3'];
        $tongKet = round($a1 * 0.2 + $a2 * 0.3 + $a3 * 0.5, 2);
        $xepLoai = match (true) {
            $tongKet >= 8.5 => 'Giỏi / Xuất sắc (A - Đạt)',
            $tongKet >= 7.0 => 'Khá (B - Đạt)',
            $tongKet >= 5.5 => 'Trung bình (C - Đạt)',
            $tongKet >= 4.0 => 'Trung bình yếu (D - Đạt)',
            default => 'Kém (F - Chưa đạt học phần)'
        };

        $_SESSION['tu_ket_qua_diem'] = [
            'a1' => $a1,
            'a2' => $a2,
            'a3' => $a3,
            'tongKet' => $tongKet,
            'xepLoai' => $xepLoai,
            'dat' => $tongKet >= 4.0,
        ];
        chuyenHuong('gioithieu.php#may-tinh-diem');
        exit;
    }
}

$ketQuaDiem = $_SESSION['tu_ket_qua_diem'] ?? null;
unset($_SESSION['tu_ket_qua_diem']);

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
$moTa = 'Trang giới thiệu thành viên Trần Văn Tú - Nhóm 01 UEDBookHub với 2 chức năng CSDL MySQL: Sổ lưu bút phân trang kèm kiểm soát quyền xóa và Bộ đếm lượt xem theo ngày.';
$cssThem = ['thanhvien/3120224169_tu/css/style.css'];
$jsThem = ['thanhvien/3120224169_tu/js/canhan.js'];
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
            <li class="the-so-thich"><span aria-hidden="true">🍜</span> Ẩm thực &amp; Trải nghiệm</li>
            <li class="the-so-thich"><span aria-hidden="true">✈️</span> Du lịch khám phá</li>
            <li class="the-so-thich"><span aria-hidden="true">🎮</span> Game thế giới mở &amp; Logic</li>
          </ul>
        </section>
      </div>
    </article>

    <!-- CHỨC NĂNG CSDL 2: BỘ ĐẾM LƯỢT XEM THEO NGÀY -->
    <section class="khoi-thong-ke-luot-xem" aria-labelledby="tieu-de-luot-xem">
      <div class="thong-ke-header">
        <div>
          <h2 id="tieu-de-luot-xem">Thống kê lượt xem trang (CSDL MySQL)</h2>
          <p class="thong-ke-mo-ta">Dữ liệu ghi nhận bằng câu lệnh <code>INSERT ... ON DUPLICATE KEY UPDATE</code> vào bảng <code>tv_tu_luot_xem</code>.</p>
        </div>
        <div class="the-tong-luot-xem">
          <span class="the-tong-luot-xem__nhan">Tổng lượt xem tích lũy</span>
          <span class="the-tong-luot-xem__so"><?= number_format($tongLuotXem, 0, ',', '.') ?></span>
        </div>
      </div>

      <div class="bang-cuon">
        <table class="bang-tk-xem">
          <caption>Lịch sử lượt xem 7 ngày gần nhất</caption>
          <thead>
            <tr>
              <th scope="col">Ngày</th>
              <th scope="col">Số lượt xem</th>
              <th scope="col">Trực quan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$dsLuotXem): ?>
              <tr><td colspan="3" class="o-trong">Chưa có dữ liệu thống kê.</td></tr>
            <?php else: ?>
              <?php
              $maxXem = max(array_map(fn($r) => (int)$r['so_luot'], $dsLuotXem));
              $maxXem = max(1, $maxXem);
              foreach ($dsLuotXem as $lx):
                $pt = round(((int)$lx['so_luot'] / $maxXem) * 100);
              ?>
              <tr>
                <td><strong><?= e(date('d/m/Y', strtotime($lx['ngay']))) ?></strong><?= $lx['ngay'] === date('Y-m-d') ? ' <span class="nhan-hom-nay">(Hôm nay)</span>' : '' ?></td>
                <td><strong><?= number_format((int)$lx['so_luot'], 0, ',', '.') ?></strong> lượt</td>
                <td>
                  <div class="thanh-truc-quan" style="--pt: <?= $pt ?>%;" title="<?= e((string)$lx['so_luot']) ?> lượt">
                    <span class="thanh-truc-quan__ruot"></span>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- CHỨC NĂNG CSDL 1: SỔ LƯU BÚT PHÂN TRANG & XÓA CÓ BẢO VỆ QUYỀN -->
    <section id="so-luu-but" class="khoi-so-luu-but" aria-labelledby="tieu-de-so-luu-but">
      <div class="so-luu-but__dau">
        <div>
          <h2 id="tieu-de-so-luu-but">Sổ lưu bút tương tác (CSDL MySQL)</h2>
          <p class="so-luu-but__mo-ta">
            Lời nhắn được lưu vào bảng <code>tv_luu_but</code>. Danh sách có phân trang (5 lời nhắn/trang).
            <?php if ($laChuTrang): ?>
              <strong class="chu-xanh">Bạn đang đăng nhập với tư cách chủ trang (Tú) nên có quyền xóa lời nhắn.</strong>
            <?php else: ?>
              <span>(Chỉ chủ trang đăng nhập <code>admin@ued.vn</code> mới được quyền xóa lời nhắn).</span>
            <?php endif; ?>
          </p>
        </div>
        <div class="the-tong-luu-but">
          <span class="huy-hieu huy-hieu--con"><?= $tongLuuBut ?> lời nhắn</span>
        </div>
      </div>

      <!-- Form gửi lưu bút -->
      <form class="bieu-mau bieu-mau-luu-but" action="gioithieu.php#so-luu-but" method="post" novalidate>
        <input type="hidden" name="gui_luu_but" value="1">
        <div class="bieu-mau__nhom">
          <label for="ten_khach">Họ tên của bạn <span class="dau-sao" aria-hidden="true">*</span></label>
          <input class="bieu-mau__o-nhap <?= isset($loiLuuBut['ten_khach']) ? 'bieu-mau__o-nhap--loi' : '' ?>"
                 id="ten_khach" name="ten_khach" type="text" maxlength="50"
                 value="<?= e($duLuuBut['ten_khach']) ?>" required
                 placeholder="Nhập tên của bạn...">
          <?php if (isset($loiLuuBut['ten_khach'])): ?>
            <p class="bieu-mau__loi"><?= e($loiLuuBut['ten_khach']) ?></p>
          <?php endif; ?>
        </div>

        <div class="bieu-mau__nhom">
          <label for="loi_nhan">Lời nhắn gửi đến Tú <span class="dau-sao" aria-hidden="true">*</span></label>
          <textarea class="bieu-mau__o-nhap bieu-mau__o-nhap--textarea <?= isset($loiLuuBut['loi_nhan']) ? 'bieu-mau__o-nhap--loi' : '' ?>"
                    id="loi_nhan" name="loi_nhan" rows="3" maxlength="500" required
                    placeholder="Viết lời nhắn lưu bút tại đây (tối đa 500 ký tự)..."><?= e($duLuuBut['loi_nhan']) ?></textarea>
          <?php if (isset($loiLuuBut['loi_nhan'])): ?>
            <p class="bieu-mau__loi"><?= e($loiLuuBut['loi_nhan']) ?></p>
          <?php endif; ?>
        </div>

        <button class="nut nut--chinh" type="submit">Gửi lưu bút</button>
      </form>

      <!-- Danh sách lời nhắn -->
      <div class="danh-sach-loi-nhan">
        <?php if (!$dsLoiNhan): ?>
          <p class="thong-bao-rong">Chưa có lời nhắn nào trong sổ lưu bút. Hãy là người đầu tiên để lại lời nhắn!</p>
        <?php else: ?>
          <?php foreach ($dsLoiNhan as $lb): ?>
          <div class="the-loi-nhan" id="loi-nhan-<?= (int)$lb['id'] ?>">
            <div class="the-loi-nhan__dau">
              <div>
                <strong class="the-loi-nhan__ten"><?= e($lb['ten_khach']) ?></strong>
                <time class="the-loi-nhan__ngay" datetime="<?= e($lb['thoi_gian']) ?>">
                  <?= e(date('H:i - d/m/Y', strtotime($lb['thoi_gian']))) ?>
                </time>
              </div>

              <?php if ($laChuTrang): ?>
              <!-- Nút xóa chỉ hiển thị khi đã xác thực chủ trang -->
              <form class="form-xoa-luu-but" action="gioithieu.php#so-luu-but" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lời nhắn này không?');">
                <input type="hidden" name="xoa_luu_but" value="1">
                <input type="hidden" name="xoa_id" value="<?= (int)$lb['id'] ?>">
                <button class="nut nut--nguy-hiem nut--nho" type="submit" title="Xóa lời nhắn này">Xóa</button>
              </form>
              <?php endif; ?>
            </div>
            <p class="the-loi-nhan__noi-dung"><?= nl2br(e($lb['loi_nhan'])) ?></p>
          </div>
          <?php endforeach; ?>

          <!-- Phân trang -->
          <?php if ($soTrangLuuBut > 1): ?>
          <nav class="dieu-huong-phan-trang" aria-label="Phân trang sổ lưu bút">
            <ul class="danh-sach-phan-trang">
              <?php if ($trangLuuBut > 1): ?>
                <li><a class="nut nut--phu nut--nho" href="?trang=<?= $trangLuuBut - 1 ?>#so-luu-but">&larr; Trước</a></li>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $soTrangLuuBut; $i++): ?>
                <li>
                  <a class="nut nut--nho <?= $i === $trangLuuBut ? 'nut--chinh' : 'nut--phu' ?>"
                     href="?trang=<?= $i ?>#so-luu-but"
                     <?= $i === $trangLuuBut ? 'aria-current="page"' : '' ?>>
                    <?= $i ?>
                  </a>
                </li>
              <?php endfor; ?>

              <?php if ($trangLuuBut < $soTrangLuuBut): ?>
                <li><a class="nut nut--phu nut--nho" href="?trang=<?= $trangLuuBut + 1 ?>#so-luu-but">Sau &rarr;</a></li>
              <?php endif; ?>
            </ul>
          </nav>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- TIỆN ÍCH PHỤ: MÁY TÍNH ĐIỂM HỌC PHẦN -->
    <section id="may-tinh-diem" class="may-tinh-diem" aria-labelledby="tieu-de-may-tinh-diem">
      <div>
        <h2 id="tieu-de-may-tinh-diem">Máy tính điểm học phần (Xử lý máy chủ PHP)</h2>
        <p class="may-tinh-diem__mo-ta">Công thức tính điểm: <strong>Điểm tổng kết = 0,2 × A1 + 0,3 × A2 + 0,5 × A3</strong>. Máy chủ kiểm tra dữ liệu hợp lệ trong khoảng từ 0 đến 10.</p>
      </div>

      <?php if (is_array($ketQuaDiem)): ?>
      <div class="ket-qua-diem <?= $ketQuaDiem['dat'] ? 'ket-qua-diem--dat' : 'ket-qua-diem--truot' ?>" role="status" aria-live="polite">
        <p><strong>Kết quả tính điểm:</strong></p>
        <p class="ket-qua-diem__chi-tiet">
          Điểm thành phần: A1 (Chuyên cần) = <strong><?= e(number_format((float) $ketQuaDiem['a1'], 2, ',', '.')) ?></strong> · A2 (Giữa kỳ) = <strong><?= e(number_format((float) $ketQuaDiem['a2'], 2, ',', '.')) ?></strong> · A3 (Cuối kỳ) = <strong><?= e(number_format((float) $ketQuaDiem['a3'], 2, ',', '.')) ?></strong>
        </p>
        <p class="ket-qua-diem__tong-ket">
          Điểm tổng kết: <strong><?= e(number_format((float) $ketQuaDiem['tongKet'], 2, ',', '.')) ?> / 10</strong> — Xếp loại: <strong><?= e($ketQuaDiem['xepLoai']) ?></strong>
        </p>
      </div>
      <?php endif; ?>

      <?php if ($loiDiem): ?>
      <p class="bieu-mau__loi" role="alert">Chưa thể tính điểm. Vui lòng kiểm tra và sửa lại các ô báo lỗi bên dưới.</p>
      <?php endif; ?>

      <form class="bieu-mau bieu-mau-diem" action="gioithieu.php#may-tinh-diem" method="post" novalidate>
        <input type="hidden" name="tinh_diem" value="1">
        <div class="bieu-mau__nhom">
          <label for="a1">Điểm A1 (Chuyên cần - 20%) <span class="dau-sao" aria-hidden="true">*</span></label>
          <input class="bieu-mau__o-nhap <?= isset($loiDiem['a1']) ? 'bieu-mau__o-nhap--loi' : '' ?>" id="a1" name="a1" type="number" min="0" max="10" step="0.01" value="<?= e($duDiem['a1']) ?>" required aria-describedby="loi-a1" aria-invalid="<?= isset($loiDiem['a1']) ? 'true' : 'false' ?>" placeholder="0.0 - 10.0">
          <p class="bieu-mau__loi" id="loi-a1"><?= e($loiDiem['a1'] ?? '') ?></p>
        </div>

        <div class="bieu-mau__nhom">
          <label for="a2">Điểm A2 (Giữa kỳ - 30%) <span class="dau-sao" aria-hidden="true">*</span></label>
          <input class="bieu-mau__o-nhap <?= isset($loiDiem['a2']) ? 'bieu-mau__o-nhap--loi' : '' ?>" id="a2" name="a2" type="number" min="0" max="10" step="0.01" value="<?= e($duDiem['a2']) ?>" required aria-describedby="loi-a2" aria-invalid="<?= isset($loiDiem['a2']) ? 'true' : 'false' ?>" placeholder="0.0 - 10.0">
          <p class="bieu-mau__loi" id="loi-a2"><?= e($loiDiem['a2'] ?? '') ?></p>
        </div>

        <div class="bieu-mau__nhom">
          <label for="a3">Điểm A3 (Cuối kỳ - 50%) <span class="dau-sao" aria-hidden="true">*</span></label>
          <input class="bieu-mau__o-nhap <?= isset($loiDiem['a3']) ? 'bieu-mau__o-nhap--loi' : '' ?>" id="a3" name="a3" type="number" min="0" max="10" step="0.01" value="<?= e($duDiem['a3']) ?>" required aria-describedby="loi-a3" aria-invalid="<?= isset($loiDiem['a3']) ? 'true' : 'false' ?>" placeholder="0.0 - 10.0">
          <p class="bieu-mau__loi" id="loi-a3"><?= e($loiDiem['a3'] ?? '') ?></p>
        </div>

        <button class="nut nut--chinh" type="submit">Tính điểm trên máy chủ PHP</button>
      </form>
    </section>

    <!-- THỜI KHÓA BIỂU -->
    <section class="khoi-thoi-khoa-bieu">
      <h2>Thời khóa biểu học tập trong tuần</h2>

      <div class="bang-cuon">
        <table class="bang-tkb">
          <caption>Lịch học các học phần chính khóa (Trần Văn Tú - MSSV: 3120224169)</caption>
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
              <td></td>
              <td></td>
              <td rowspan="3">
                <div class="the-mon-hoc the-mon-hoc--khai-pha">
                  <span class="the-mon-hoc__ma">31231330 - 24-0101</span>
                  <span class="the-mon-hoc__ten">Khai phá dữ liệu</span>
                  <span class="the-mon-hoc__phong">Phòng A5-404B</span>
                </div>
              </td>
              <td rowspan="3">
                <div class="the-mon-hoc the-mon-hoc--lap-trinh-mang">
                  <span class="the-mon-hoc__ma">31231398 - 24-0101</span>
                  <span class="the-mon-hoc__ten">Lập trình mạng</span>
                  <span class="the-mon-hoc__phong">Phòng A5-403</span>
                </div>
              </td>
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
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <th scope="row">5</th>
              <td rowspan="2">
                <div class="the-mon-hoc the-mon-hoc--lich-su">
                  <span class="the-mon-hoc__ma">21221904 - 24-0314</span>
                  <span class="the-mon-hoc__ten">Lịch sử Đảng CSVN</span>
                  <span class="the-mon-hoc__phong">Phòng A5-404C</span>
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
              <td rowspan="2">
                <div class="the-mon-hoc the-mon-hoc--attt">
                  <span class="the-mon-hoc__ma">31221010 - 24-0102</span>
                  <span class="the-mon-hoc__ten">An toàn thông tin</span>
                  <span class="the-mon-hoc__phong">Phòng A6-401</span>
                </div>
              </td>
              <td></td>
              <td rowspan="3">
                <div class="the-mon-hoc the-mon-hoc--csdl">
                  <span class="the-mon-hoc__ma">31241283 - 24-0301</span>
                  <span class="the-mon-hoc__ten">Hệ quản trị CSDL</span>
                  <span class="the-mon-hoc__phong">Phòng B1.104</span>
                </div>
              </td>
              <td rowspan="3">
                <div class="the-mon-hoc the-mon-hoc--cnpm">
                  <span class="the-mon-hoc__ma">31231016 - 24-0102</span>
                  <span class="the-mon-hoc__ten">Công nghệ phần mềm</span>
                  <span class="the-mon-hoc__phong">Phòng B3-503</span>
                </div>
              </td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <th scope="row">8</th>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <th scope="row">9</th>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div class="khung-hanh-dong-ca-nhan">
      <a class="nut nut--vien" href="../../gioi-thieu.php">&larr; Quay về danh sách nhóm</a>
      <a class="nut nut--chinh" href="../../chi-tiet.php?id=1">Xem giáo trình tiêu biểu &rarr;</a>
    </div>
  </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
