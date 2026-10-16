<?php
/**
 * Trang cá nhân thành viên: Lê Trung Hậu (MSSV: 3120224052) - Nhóm 01 UEDBookHub.
 * Hai chức năng PHP phía máy chủ theo yêu cầu Phần B:
 * 1. Thả tim & Đếm lượt thích cá nhân lưu trữ máy chủ PHP (POST/PRG, ghi storage/3120224052_likes.txt, Session).
 * 2. Máy tính xếp loại rèn luyện & Dự báo học bổng UED (POST/PRG, kiểm tra hợp lệ máy chủ 0-100 & 0-4, Session).
 * Thử nghiệm: Thao tác thả tim hoặc tính điểm tại #may-tinh-drl, hoạt động đầy đủ cả khi tắt JavaScript.
 */

declare(strict_types=1);

require __DIR__ . '/../../inc/config.php';

// Thư mục và tệp lưu trữ số lượt thích
$tepLikes = __DIR__ . '/../../storage/3120224052_likes.txt';

// Đọc số lượt thích hiện tại
$soLuotThich = 0;
if (file_exists($tepLikes)) {
    $soLuotThich = (int)trim((string)file_get_contents($tepLikes));
}

// Kiểm tra phiên đã thích chưa
$daThichPhien = !empty($_SESSION['hau_da_thich_3120224052']);

// --- Chức năng 1: Thả tim & Đếm lượt thích trang cá nhân (POST/PRG) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thich_trang'])) {
    if (!$daThichPhien) {
        $soLuotThich++;
        file_put_contents($tepLikes, (string)$soLuotThich, LOCK_EX);
        $_SESSION['hau_da_thich_3120224052'] = true;
        $daThichPhien = true;
    }

    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'thanhCong' => true,
            'soLuotThich' => $soLuotThich,
            'daThich' => true,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    chuyenHuong('gioithieu.php?da_thich=1#tuong-tac');
}

// Danh sách các câu giới thiệu/châm ngôn của Hậu
$danhSachGioiThieu = [
    '💻 Tôi yêu thích lập trình và phát triển website.',
    '🎯 Tôi đang cố gắng nâng cao kỹ năng lập trình mỗi ngày.',
    '🌱 Tôi thích khám phá, sáng tạo và thử nghiệm những điều mới.',
    '📚 Tôi luôn tìm kiếm kiến thức mới để hoàn thiện bản thân.',
    '✈️ Tôi thích đi du lịch và khám phá thế giới.',
    '🎧 Tôi thích nghe nhạc để thư giãn và tìm cảm hứng.',
    '🌊 Tôi thích đi dạo ngắm biển và tận hưởng gió trời.',
];

// Lấy ngẫu nhiên câu giới thiệu (khi tải hoặc người dùng bấm đổi khi tắt JS)
$cauHienTai = isset($_GET['cau_moi'])
    ? $danhSachGioiThieu[array_rand($danhSachGioiThieu)]
    : 'Bấm nút "Tạo câu giới thiệu" để khám phá thêm về mình nhé!';

// Lời chào hiển thị theo buổi (sinh trực tiếp từ máy chủ PHP)
$gioHienTai = (int)date('G');
if ($gioHienTai >= 5 && $gioHienTai < 12) {
    $loiChaoBuoi = '☀️ Buổi sáng rạng rỡ nha! Chúc bạn ngày mới đầy ắp tiếng cười và lượm lặt thêm thật nhiều điều hay ho nè ✨';
} elseif ($gioHienTai >= 12 && $gioHienTai < 18) {
    $loiChaoBuoi = '🌤️ Chiều an lành nhé! Chúc bạn nạp đầy năng lượng và có buổi học thật vui nha ✨';
} else {
    $loiChaoBuoi = '🌙 Tối an yên nhé! Đừng quên vừa học vừa thư giãn để nạp lại năng lượng cho bản thân nha ☁️';
}

// --- Chức năng 2: Máy tính xếp loại rèn luyện & Xét học bổng UED (POST/PRG) ---
$duDrl = ['drl' => '', 'gpa' => ''];
$loiDrl = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tinh_drl'])) {
    $duDrl['drl'] = chuoi($_POST, 'drl');
    $duDrl['gpa'] = chuoi($_POST, 'gpa');

    if ($duDrl['drl'] === '') {
        $loiDrl['drl'] = 'Vui lòng nhập điểm rèn luyện (0 - 100).';
    } else {
        $valDrl = filter_var($duDrl['drl'], FILTER_VALIDATE_FLOAT);
        if ($valDrl === false || !is_finite((float)$valDrl) || $valDrl < 0 || $valDrl > 100) {
            $loiDrl['drl'] = 'Điểm rèn luyện phải là số từ 0 đến 100.';
        }
    }

    if ($duDrl['gpa'] === '') {
        $loiDrl['gpa'] = 'Vui lòng nhập điểm trung bình học tập GPA (0.0 - 4.0).';
    } else {
        $valGpa = filter_var($duDrl['gpa'], FILTER_VALIDATE_FLOAT);
        if ($valGpa === false || !is_finite((float)$valGpa) || $valGpa < 0 || $valGpa > 4.0) {
            $loiDrl['gpa'] = 'Điểm GPA hệ 4 phải là số từ 0.00 đến 4.00.';
        }
    }

    if (!$loiDrl) {
        $drl = (float)$duDrl['drl'];
        $gpa = (float)$duDrl['gpa'];

        // Xếp loại rèn luyện theo quy chế
        $xepLoaiDrl = match (true) {
            $drl >= 90 => 'Xuất sắc',
            $drl >= 80 => 'Tốt',
            $drl >= 65 => 'Khá',
            $drl >= 50 => 'Trung bình',
            default => 'Yếu / Kém'
        };

        // Xét điều kiện dự báo học bổng khuyến khích học tập sinh viên UED
        $datHocBong = false;
        if ($gpa >= 3.6 && $drl >= 90) {
            $hocBong = 'Học bổng Xuất sắc (Mức 1 - Tối đa)';
            $datHocBong = true;
        } elseif ($gpa >= 3.2 && $drl >= 80) {
            $hocBong = 'Học bổng Giỏi (Mức 2)';
            $datHocBong = true;
        } elseif ($gpa >= 2.5 && $drl >= 70) {
            $hocBong = 'Học bổng Khá (Mức 3)';
            $datHocBong = true;
        } else {
            $hocBong = 'Chưa đạt điều kiện xét học bổng khuyến khích (Cần GPA ≥ 2.5 và ĐRL ≥ 70)';
        }

        $_SESSION['hau_ket_qua_drl'] = [
            'drl' => $drl,
            'gpa' => $gpa,
            'xepLoaiDrl' => $xepLoaiDrl,
            'hocBong' => $hocBong,
            'datHocBong' => $datHocBong,
        ];
        chuyenHuong('gioithieu.php#may-tinh-drl');
    }
}

$ketQuaDrl = $_SESSION['hau_ket_qua_drl'] ?? null;
unset($_SESSION['hau_ket_qua_drl']);

$goc = '../../';
$tieuDe = 'Giới thiệu thành viên: Lê Trung Hậu';
$trang = '';
$moTa = 'Trang giới thiệu cá nhân sinh viên Lê Trung Hậu - Thành viên nhóm 01 UEDBookHub với 2 chức năng PHP: Thả tim máy chủ và Máy tính xếp loại rèn luyện / Học bổng UED.';
$cssThem = ['thanhvien/3120224052_hau/css/style.css'];
$jsThem = ['thanhvien/3120224052_hau/js/canhan.js'];

require __DIR__ . '/../../inc/header.php';
?>
  <main class="noi-dung-chinh">

    <article class="ho-so-ca-nhan">
      <div class="ho-so-ca-nhan__than">
        <div class="ho-so-ca-nhan__hang-dau">
          <img class="ho-so-ca-nhan__anh" src="anhchandung.jpg" alt="Ảnh chân dung sinh viên Lê Trung Hậu" width="160" height="160">
          <div class="ho-so-ca-nhan__thong-tin">
            <h1 class="ho-so-ca-nhan__ten">Lê Trung Hậu</h1>
            <div class="ho-so-ca-nhan__vai-tro">Thành viên của nhóm 01 – Đề tài UEDBookHub</div>
            <div class="nhom-nut">
              <span class="huy-hieu huy-hieu--con">MSSV: 3120224052</span>
              <span class="huy-hieu huy-hieu--con">Khoa Toán – Tin</span>
              <span class="huy-hieu huy-hieu--sap-het">Lớp học phần: 24-0102</span>
              <span class="huy-hieu huy-hieu--con">Trường ĐH Sư phạm – ĐH Đà Nẵng</span>
            </div>
          </div>
        </div>

        <!-- Khối tương tác cá nhân (Phần B: Thả tim lưu máy chủ PHP & Lời chào máy chủ) -->
        <section class="tuong-tac" id="tuong-tac" aria-labelledby="tieu-de-tuong-tac">
          <h2 id="tieu-de-tuong-tac" class="tuong-tac__tieu-de">✨ Góc tương tác cá nhân (Phần B)</h2>

          <!-- Hộp lời chào hiển thị theo buổi (Sinh trực tiếp từ PHP, client JS tự động cập nhật) -->
          <div id="loi-chao" class="tuong-tac__loi-chao" role="status" aria-live="polite"><?= e($loiChaoBuoi) ?></div>

          <?php if (isset($_GET['da_thich'])): ?>
          <p class="thong-bao-thich" role="status" aria-live="polite">
            ❤️ Cảm ơn bạn đã thả tim ủng hộ trang cá nhân của Hậu!
          </p>
          <?php endif; ?>

          <!-- Nhóm các nút bấm tương tác -->
          <div class="tuong-tac__hanh-dong">
            <form class="form-thich" action="gioithieu.php#tuong-tac" method="post">
              <input type="hidden" name="thich_trang" value="1">
              <button id="nut-thich" class="nut nut--thich <?= $daThichPhien ? 'nut-thich--da-thich' : '' ?>" type="submit" aria-label="Thả tim thích trang">
                <span class="nut-thich__bieu-tuong" aria-hidden="true">❤️</span>
                <span class="nut-thich__nhan"><?= $daThichPhien ? 'Đã thích' : 'Thích trang' ?></span>
                <span class="nut-thich__so" id="so-luot-thich"><?= e((string)$soLuotThich) ?></span>
              </button>
            </form>

            <a id="nut-gioi-thieu" class="nut nut--gioi-thieu" href="gioithieu.php?cau_moi=1#tuong-tac" role="button">
              <span class="nut-gioi-thieu__bieu-tuong" aria-hidden="true">🎲</span>
              <span>Tạo câu giới thiệu</span>
            </a>
          </div>

          <!-- Khung hiển thị câu giới thiệu ngẫu nhiên -->
          <div class="tuong-tac__khung-noi-dung">
            <p id="cau-gioi-thieu" class="tuong-tac__cau-noi"><?= e($cauHienTai) ?></p>
          </div>
        </section>

        <section>
          <h2>Giới thiệu bản thân</h2>
          <p class="doan-van-dai">
            Mình là <strong>Lê Trung Hậu</strong> (MSSV: <strong>3120224052</strong>), Thành viên của nhóm 1 <strong>UEDBookHub</strong>, thành viên dự án <strong>CareHomeAI</strong> đạt giải Nhì cuộc thi khởi nghiệp UED. Mình hiện đang là sinh viên năm 3, ngành <strong>Công nghệ thông tin</strong> tại <strong>Khoa Toán – Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng</strong>. Mình có niềm đam mê với lập trình, công nghệ mới và luôn tìm kiếm cơ hội để học hỏi và phát triển kỹ năng của mình. Mình mong muốn đóng góp vào các dự án công nghệ thông tin có ý nghĩa và mang lại giá trị cho cộng đồng.
          </p>
        </section>

        <section>
          <h2>Kỹ năng chuyên môn</h2>
          <ul class="luoi-ky-nang">
            <li class="the-ky-nang">
              <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🎯</span>
              <h3 class="the-ky-nang__tieu-de">Quản lý &amp; Thời gian tốt</h3>
              <p class="the-ky-nang__mo-ta">Hoàn thành đúng hạn các nhiệm vụ được giao, đảm bảo tiến độ và chất lượng công việc của nhóm.</p>
            </li>
            <li class="the-ky-nang">
              <span class="the-ky-nang__bieu-tuong" aria-hidden="true">💻</span>
              <h3 class="the-ky-nang__tieu-de">Lập trình &amp; Các ngôn ngữ lập trình</h3>
              <p class="the-ky-nang__mo-ta">Sử dụng các ngôn ngữ lập trình như C++, Java, Python.</p>
            </li>
            <li class="the-ky-nang">
              <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🌐</span>
              <h3 class="the-ky-nang__tieu-de">Thiết kế &amp; Figma</h3>
              <p class="the-ky-nang__mo-ta">Sử dụng Figma để thiết kế giao diện người dùng đẹp mắt và thân thiện.</p>
            </li>
          </ul>
        </section>

        <section>
          <h2>Sở thích cá nhân</h2>
          <ul class="danh-sach-so-thich">
            <li class="the-so-thich"><span aria-hidden="true">🎵</span> Nghe nhạc, sáng tạo nhạc &amp; Podcast</li>
            <li class="the-so-thich"><span aria-hidden="true">🍜</span> Ẩm thực &amp; Trải nghiệm</li>
            <li class="the-so-thich"><span aria-hidden="true">✈️</span> Du lịch khám phá</li>
            <li class="the-so-thich"><span aria-hidden="true">📚</span> Đọc sách, nghiên cứu &amp; học hỏi</li>
          </ul>
        </section>
      </div>
    </article>

    <!-- Chức năng 2 Phần B: Máy tính xếp loại rèn luyện & Dự báo học bổng UED -->
    <section id="may-tinh-drl" class="may-tinh-drl" aria-labelledby="tieu-de-may-tinh-drl">
      <div>
        <h2 id="tieu-de-may-tinh-drl">Máy tính xếp loại rèn luyện &amp; Xét học bổng UED (Xử lý máy chủ PHP)</h2>
        <p class="may-tinh-drl__mo-ta">
          Công cụ hỗ trợ sinh viên Trường ĐH Sư phạm – ĐH Đà Nẵng tra cứu xếp loại điểm rèn luyện và đối chiếu tiêu chuẩn học bổng khuyến khích học tập. Dữ liệu được xác thực an toàn và tính toán tại máy chủ PHP (POST/PRG).
        </p>
      </div>

      <?php if (is_array($ketQuaDrl)): ?>
      <div class="ket-qua-drl <?= $ketQuaDrl['datHocBong'] ? 'ket-qua-drl--dat' : 'ket-qua-drl--chua-dat' ?>" role="status" aria-live="polite">
        <p><strong>Kết quả phân tích học tập &amp; rèn luyện:</strong></p>
        <p class="ket-qua-drl__chi-tiet">
          Điểm rèn luyện: <strong><?= e(number_format((float)$ketQuaDrl['drl'], 1, ',', '.')) ?> / 100</strong> &bull; Xếp loại rèn luyện: <strong><?= e($ketQuaDrl['xepLoaiDrl']) ?></strong>
        </p>
        <p class="ket-qua-drl__chi-tiet">
          Điểm trung bình học tập: GPA = <strong><?= e(number_format((float)$ketQuaDrl['gpa'], 2, ',', '.')) ?> / 4.00</strong>
        </p>
        <p class="ket-qua-drl__tong-ket">
          Dự báo học bổng UED: <strong><?= e($ketQuaDrl['hocBong']) ?></strong>
          <?= $ketQuaDrl['datHocBong'] ? '🎉' : 'ℹ️' ?>
        </p>
      </div>
      <?php endif; ?>

      <?php if ($loiDrl): ?>
      <p class="bieu-mau__loi" role="alert">Chưa thể tính kết quả. Vui lòng kiểm tra và sửa lại các ô báo lỗi bên dưới.</p>
      <?php endif; ?>

      <form class="bieu-mau bieu-mau-drl" action="gioithieu.php#may-tinh-drl" method="post" novalidate>
        <input type="hidden" name="tinh_drl" value="1">
        <div class="bieu-mau__nhom">
          <label for="drl">Điểm rèn luyện (Thang 100) <span class="dau-sao" aria-hidden="true">*</span></label>
          <input class="bieu-mau__o-nhap <?= isset($loiDrl['drl']) ? 'bieu-mau__o-nhap--loi' : '' ?>" id="drl" name="drl" type="number" min="0" max="100" step="0.5" value="<?= e($duDrl['drl']) ?>" required aria-describedby="loi-drl" aria-invalid="<?= isset($loiDrl['drl']) ? 'true' : 'false' ?>" placeholder="0 - 100 (Ví dụ: 88.5)">
          <p class="bieu-mau__loi" id="loi-drl"><?= e($loiDrl['drl'] ?? '') ?></p>
        </div>

        <div class="bieu-mau__nhom">
          <label for="gpa">Điểm trung bình học tập GPA (Thang 4) <span class="dau-sao" aria-hidden="true">*</span></label>
          <input class="bieu-mau__o-nhap <?= isset($loiDrl['gpa']) ? 'bieu-mau__o-nhap--loi' : '' ?>" id="gpa" name="gpa" type="number" min="0" max="4" step="0.01" value="<?= e($duDrl['gpa']) ?>" required aria-describedby="loi-gpa" aria-invalid="<?= isset($loiDrl['gpa']) ? 'true' : 'false' ?>" placeholder="0.00 - 4.00 (Ví dụ: 3.45)">
          <p class="bieu-mau__loi" id="loi-gpa"><?= e($loiDrl['gpa'] ?? '') ?></p>
        </div>

        <button class="nut nut--chinh" type="submit">Tính toán &amp; Xếp loại trên máy chủ PHP</button>
      </form>
    </section>

    <section class="khoi-thoi-khoa-bieu">
      <h2>Thời khóa biểu học tập trong tuần</h2>

      <div class="bang-cuon">
        <table class="bang-tkb">
          <caption>Lịch học các học phần chính khóa (Lê Trung Hậu - MSSV: 3120224052)</caption>
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

