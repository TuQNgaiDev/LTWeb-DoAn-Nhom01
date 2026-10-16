<?php
/**
 * Trang cá nhân của Bhling Trường - MSSV: 3120124040.
 * Chức năng 1: Bộ đếm lượt xem theo phiên (kết hợp $_SESSION và storage/3120124040_views.txt).
 * Chức năng 2: Sổ lưu bút (POST, PRG pattern, kiểm tra máy chủ, ghi storage/3120124040_luubut.jsonl).
 * Cách thử: F5 lượt xem không tăng; gửi biểu mẫu lưu bút rồi F5 không bị gửi lặp form.
 */

declare(strict_types=1);

$goc = '../../';
$trang = '';
$tieuDe = 'Giới thiệu thành viên: Bhling Trường';

require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/ham.php';

// Thư mục và tệp lưu trữ dữ liệu cá nhân
$thuMucStorage = __DIR__ . '/../../storage';
if (!is_dir($thuMucStorage)) {
    mkdir($thuMucStorage, 0755, true);
}
$tepDemViews = $thuMucStorage . '/3120124040_views.txt';
$tepLuuBut = $thuMucStorage . '/3120124040_luubut.jsonl';

//CHỨC NĂNG 1: BỘ ĐẾM LƯỢT XEM THEO PHIÊN
$soLuotXem = 0;
if (file_exists($tepDemViews)) {
    $soLuotXem = (int)file_get_contents($tepDemViews);
}

if (empty($_SESSION['da_xem_3120124040'])) {
    $soLuotXem++;
    file_put_contents($tepDemViews, (string)$soLuotXem, LOCK_EX);
    $_SESSION['da_xem_3120124040'] = true;
}

//CHỨC NĂNG 2: XỬ LÝ SỔ LƯU BÚT
$thongBaoLoi = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gui_luu_but'])) {
    $hoTen = trim($_POST['ho_ten'] ?? '');
    $loiNhan = trim($_POST['loi_nhan'] ?? '');

    if ($hoTen === '' || $loiNhan === '') {
        $thongBaoLoi = 'Vui lòng nhập đầy đủ họ tên và lời nhắn.';
    } elseif (mb_strlen($hoTen) > 50) {
        $thongBaoLoi = 'Họ tên không được vượt quá 50 ký tự.';
    } elseif (mb_strlen($loiNhan) > 500) {
        $thongBaoLoi = 'Lời nhắn không được vượt quá 500 ký tự.';
    } else {
        $dongMoi = [
            'thoi_gian' => date('d/m/Y H:i'),
            'ho_ten' => $hoTen,
            'loi_nhan' => $loiNhan,
        ];
        file_put_contents($tepLuuBut, json_encode($dongMoi, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Chuyển hướng PRG chống gửi lặp form khi F5
        header('Location: gioithieu.php?da_gui=1#so-luu-but');
        exit;
    }
}

$danhSachLuuBut = [];
if (file_exists($tepLuuBut)) {
    $cacDong = file($tepLuuBut, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($cacDong !== false) {
        $cacDong = array_reverse($cacDong);
        $danhSachLuuBut = array_slice($cacDong, 0, 5);
    }
}

require_once __DIR__ . '/../../inc/header.php';
?>

<!-- Nạp CSS riêng của cá nhân theo đúng gợi ý đề bài -->
<link rel="stylesheet" href="css/style.css?v=14">

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
          Mình là <strong>Bhling Trường</strong> (MSSV: <strong>3120124040</strong>), sinh viên ngành Sư phạm Tin học (Lớp 24SPT), thành viên nhóm dự án <strong>UEDBookHub</strong>. Hiện tại, mình đang tham gia thiết kế cấu trúc HTML5 chuẩn ngữ nghĩa và tối ưu hóa khả năng tiếp cận (Accessibility) cho trang web nhóm.
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
            <h3 class="the-ky-nang__tieu-de">Lập trình cơ bản</h3>
            <p class="the-ky-nang__mo-ta">Biết sử dụng cơ bản ngôn ngữ C++ và Python để giải các bài toán thuật toán nền tảng trong chương trình học.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🗄️</span>
            <h3 class="the-ky-nang__tieu-de">Cơ sở dữ liệu</h3>
            <p class="the-ky-nang__mo-ta">Nắm được cách tạo bảng, tạo khóa và viết các câu lệnh truy vấn dữ liệu đơn giản trên SQL Server và PostgreSQL.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🌐</span>
            <h3 class="the-ky-nang__tieu-de">Xây dựng giao diện Web</h3>
            <p class="the-ky-nang__mo-ta">Biết viết cấu trúc HTML5 cơ bản, dùng CSS để căn chỉnh màu sắc, bố cục và hỗ trợ xem trên điện thoại.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">👨‍🏫</span>
            <h3 class="the-ky-nang__tieu-de">Định hướng Sư phạm</h3>
            <p class="the-ky-nang__mo-ta">Đang rèn luyện kỹ năng soạn thảo nội dung bài học, chuẩn bị học liệu và tìm hiểu phương pháp dạy Tin học.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">🤝</span>
            <h3 class="the-ky-nang__tieu-de">Làm việc nhóm &amp; Git</h3>
            <p class="the-ky-nang__mo-ta">Sử dụng được các lệnh Git cơ bản (add, commit, push, pull) để phối hợp nộp bài cùng các thành viên trong nhóm.</p>
          </li>
          <li class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true">📚</span>
            <h3 class="the-ky-nang__tieu-de">Tinh thần học hỏi</h3>
            <p class="the-ky-nang__mo-ta">Chăm chỉ thực hành bài tập trên lớp, chủ động tra cứu tài liệu và hoàn thành các nhiệm vụ được giao.</p>
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
          <!-- Tiết 1 - 3 -->
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

          <!-- Tiết 4 - 6 -->
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

          <!-- Tiết 7 - 9 -->
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

          <!-- Tiết 10 -->
          <tr>
            <th scope="row">10</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 11 - 12 -->
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

  <!-- CHỨC NĂNG PHẦN B 1: BỘ ĐẾM SỐ LƯỢT XEM THEO PHIÊN -->
  <section class="hop-dem-views" style="margin: 25px 0; padding: 18px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <h2>Thống kê ghé thăm trang cá nhân</h2>
    <p>Tổng số lượt ghé thăm trang: <strong style="color: #0284c7; font-size: 1.25rem;"><?= e((string)$soLuotXem) ?></strong> lượt.</p>
    <p style="margin: 0; font-size: 0.9rem; color: #64748b;">(Cơ chế: Lưu trữ tệp txt trong thư mục storage; sử dụng Session của máy chủ nên làm mới trang không làm tăng số lượt đếm).</p>
  </section>

  <!-- CHỨC NĂNG PHẦN B 2: SỔ LƯU BÚT BẠN BÈ (POST & PRG) -->
  <section id="so-luu-but" class="hop-luu-but" style="margin: 25px 0; padding: 18px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <h2>Sổ lưu bút &amp; Lời nhắn cho Trường</h2>

    <?php if (isset($_GET['da_gui'])): ?>
      <p style="padding: 10px 14px; background: #dcfce7; color: #166534; border-radius: 4px; font-weight: bold;">
        ✓ Cảm ơn bạn! Lời nhắn của bạn đã được ghi lại thành công.
      </p>
    <?php endif; ?>

    <?php if ($thongBaoLoi !== ''): ?>
      <p style="padding: 10px 14px; background: #fee2e2; color: #991b1b; border-radius: 4px; font-weight: bold;">
        ✕ <?= e($thongBaoLoi) ?>
      </p>
    <?php endif; ?>

    <form method="post" action="gioithieu.php" style="margin-bottom: 25px;">
      <div style="margin-bottom: 12px;">
        <label for="ho_ten" style="display: block; font-weight: bold; margin-bottom: 4px;">Họ tên của bạn:</label>
        <input type="text" id="ho_ten" name="ho_ten" required maxlength="50" placeholder="Nhập tên của bạn..." style="width: 100%; max-width: 450px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px;">
      </div>
      <div style="margin-bottom: 12px;">
        <label for="loi_nhan" style="display: block; font-weight: bold; margin-bottom: 4px;">Lời nhắn:</label>
        <textarea id="loi_nhan" name="loi_nhan" rows="3" required maxlength="500" placeholder="Viết vài dòng lời nhắn cho Trường..." style="width: 100%; max-width: 550px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px;"></textarea>
      </div>
      <button type="submit" name="gui_luu_but" class="nut nut--chinh" style="padding: 8px 20px; cursor: pointer; border-radius: 4px;">Gửi lưu bút</button>
    </form>

    <h3>5 lời nhắn mới nhất</h3>
    <?php if (empty($danhSachLuuBut)): ?>
      <p style="font-style: italic; color: #64748b;">Chưa có lời nhắn nào được ghi lại. Hãy là người đầu tiên để lại lời nhắn nhé!</p>
    <?php else: ?>
      <ul style="list-style: none; padding-left: 0; margin: 0;">
        <?php foreach ($danhSachLuuBut as $dongJson): ?>
          <?php 
            $duLieu = json_decode($dongJson, true);
            if (!is_array($duLieu)) continue;
          ?>
          <li style="border-bottom: 1px dashed #cbd5e1; padding: 10px 0;">
            <strong style="color: #1e293b;"><?= e((string)($duLieu['ho_ten'] ?? '')) ?></strong> 
            <span style="font-size: 0.85rem; color: #64748b;">(<?= e((string)($duLieu['thoi_gian'] ?? '')) ?>)</span>
            <p style="margin: 4px 0 0; color: #334155;"><?= nl2br(e((string)($duLieu['loi_nhan'] ?? ''))) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
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