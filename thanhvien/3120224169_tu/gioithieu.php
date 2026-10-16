<?php
/**
 * Trang cá nhân thành viên: Trần Văn Tú (MSSV: 3120224169) - Nhóm 01 UEDBookHub.
 * Hai chức năng PHP phía máy chủ theo yêu cầu Phần B:
 * 1. Chuyển đổi giao diện Sáng / Tối ghi nhớ bằng Cookie phía máy chủ (POST/PRG).
 * 2. Máy tính điểm học phần (0,2*A1 + 0,3*A2 + 0,5*A3), kiểm tra dữ liệu 0-10 tại máy chủ (POST/PRG).
 * Thử nghiệm: Thao tác đổi giao diện hoặc nhập điểm tại #may-tinh-diem, hoạt động đầy đủ cả khi tắt JavaScript.
 */
require __DIR__ . '/../../inc/config.php';

// --- Chức năng 1: Giao diện Sáng / Tối ghi nhớ bằng Cookie máy chủ ---
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
}

// --- Chức năng 2: Máy tính điểm học phần (POST / PRG) ---
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
    }
}

$ketQuaDiem = $_SESSION['tu_ket_qua_diem'] ?? null;
unset($_SESSION['tu_ket_qua_diem']);

$cacKyNang = [
    ['nhom' => 'quan-ly', 'bieuTuong' => '🎯', 'ten' => 'Quản lý & Vận hành nhóm', 'moTa' => 'Lập kế hoạch phân công công việc, điều phối mã nguồn Git/GitHub, chịu trách nhiệm chính về kiến trúc Website.'],
    ['nhom' => 'lap-trinh', 'bieuTuong' => '💻', 'ten' => 'Thiết kế & Lập trình Web', 'moTa' => 'Sử dụng HTML5 ngữ nghĩa, CSS (Grid, Flexbox, BEM), tối ưu hóa SEO và chuẩn trợ năng Accessibility.'],
    ['nhom' => 'lap-trinh', 'bieuTuong' => '⚡', 'ten' => 'Lập trình thuật toán & Ứng dụng', 'moTa' => 'Nắm vững tư duy thuật toán, cấu trúc dữ liệu và lập trình hướng đối tượng với ngôn ngữ C/C++, Java và Python.'],
    ['nhom' => 'du-lieu', 'bieuTuong' => '🗄️', 'ten' => 'Hệ quản trị Cơ sở dữ liệu', 'moTa' => 'Hiểu biết mô hình dữ liệu quan hệ, thiết kế lược đồ thực thể ERD và truy vấn CSDL SQL.'],
    ['nhom' => 'du-lieu', 'bieuTuong' => '🔒', 'ten' => 'An toàn & Bảo mật Web', 'moTa' => 'Nhận thức các nguyên lý an toàn thông tin, bảo mật dữ liệu học liệu và giao thức mạng cơ bản.'],
    ['nhom' => 'quan-ly', 'bieuTuong' => '🚀', 'ten' => 'Tự học & Nghiên cứu công nghệ', 'moTa' => 'Khả năng đọc hiểu tài liệu kỹ thuật tiếng Anh (MDN Web Docs, W3C Standards) và chủ động giải quyết sự cố kỹ thuật.'],
];

$goc = '../../';
$tieuDe = 'Giới thiệu thành viên: Trần Văn Tú';
$trang = '';
$moTa = 'Trang giới thiệu thành viên Trần Văn Tú - Nhóm 01 UEDBookHub với 2 chức năng PHP: Giao diện Sáng/Tối ghi nhớ Cookie và Máy tính điểm học phần.';
$cssThem = ['thanhvien/3120224169_tu/css/style.css'];
$jsThem = ['thanhvien/3120224169_tu/js/canhan.js'];
$dataGiaoDien = $giaoDien;
require __DIR__ . '/../../inc/header.php';
?>
  <main class="noi-dung-chinh">

    <section class="bang-dieu-khien-ca-nhan" aria-labelledby="tieu-de-tuong-tac">
      <div>
        <p id="tieu-de-tuong-tac" class="bang-dieu-khien-ca-nhan__tieu-de">Góc tương tác của Tú (Phần B)</p>
        <p>Giao diện Sáng / Tối được xử lý và ghi nhớ qua <strong>Cookie máy chủ PHP</strong>. Hiện tại: <strong><?= $giaoDien === 'toi' ? 'Chế độ Tối 🌙' : 'Chế độ Sáng ☀️' ?></strong>.</p>
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
              <td></td>
            </tr>
            <tr>
              <th scope="row">9</th>
              <td></td>
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
              <td></td>
            </tr>
            <tr>
              <th scope="row">11</th>
              <td></td>
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
