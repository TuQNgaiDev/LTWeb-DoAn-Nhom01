<?php
/**
 * Trang cá nhân thành viên: Nguyễn Tuấn Nhật Thanh (MSSV: 3120224132) - Nhóm 01 UEDBookHub.
 * Phụ trách Chức năng 3 (Quản trị CRUD thực thể chính & Thiết kế ERD hệ thống).
 * Hai chức năng CSDL MySQL (PDO) theo yêu cầu Phần B - Bài tập nhóm 6:
 * 1. Lọc kỹ năng theo nhóm từ CSDL (bảng tv_thanh_ky_nang):
 *    - Chuyển đổi từ mảng tĩnh PHP Bài 5 sang lưu trữ và truy vấn trong CSDL MySQL.
 *    - Hỗ trợ lọc theo các nhóm: Lập trình, Cơ sở dữ liệu, Kỹ năng mềm bằng Prepared Statement.
 * 2. Tìm kiếm môn học trong thời khóa biểu từ CSDL (bảng tv_thanh_tkb):
 *    - Chuyển đổi từ dữ liệu mảng Bài 5 sang bảng CSDL MySQL.
 *    - Tìm kiếm an toàn với LIKE + addcslashes chống SQL Injection theo mã môn, tên môn, phòng học.
 * Cách thử nghiệm:
 * - Lọc kỹ năng: Chọn nhóm trong ô chọn hoặc bấm các nút lọc nhanh để tải danh sách kỹ năng tương ứng từ CSDL.
 * - Tìm kiếm môn học: Nhập từ khóa (ví dụ: 'web', 'cơ sở dữ liệu', 'b3') để lọc các học phần trong TKB.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

const MSSV = '3120224132';
$goc = '../../';

// --- XỬ LÝ GỬI LỜI NHẮN (POST / PRG) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'loi_nhan') {
    $tenNguoiGui = trim($_POST['ten_nguoi_gui'] ?? '');
    $loiNhan = trim($_POST['loi_nhan'] ?? '');

    if ($tenNguoiGui === '') {
        $tenNguoiGui = 'Khách ghé thăm';
    }

    if ($loiNhan === '') {
        $_SESSION['flash_loi'] = 'Vui lòng nhập nội dung lời nhắn.';
    } elseif (mb_strlen($loiNhan) > 500) {
        $_SESSION['flash_loi'] = 'Lời nhắn không được vượt quá 500 ký tự.';
    } else {
        // Lưu tạm vào flash session thông báo thành công theo mô hình PRG
        $_SESSION['flash'] = "Cảm ơn $tenNguoiGui đã để lại lời nhắn: \"$loiNhan\"!";
    }

    chuyenHuong('gioithieu.php#form-loi-nhan');
    exit;
}

// --- CHỨC NĂNG CSDL 1: LỌC KỸ NĂNG THEO NHÓM (BẢNG tv_thanh_ky_nang) ---
$cacNhomKyNang = [
    'tatca'     => 'Tất cả nhóm kỹ năng',
    'laptrinh'  => 'Lập trình & Phát triển Web',
    'csdl'      => 'Cơ sở dữ liệu & Phân tích',
    'kynangmem' => 'Kỹ năng mềm & Làm việc nhóm'
];

$nhomChon = trim($_GET['nhom'] ?? 'tatca');
if (!array_key_exists($nhomChon, $cacNhomKyNang)) {
    $nhomChon = 'tatca';
}

$dsKyNang = [];
try {
    if ($nhomChon === 'tatca') {
        $stKn = db()->prepare('SELECT id, nhom, ten_ky_nang, muc_do, bieu_tuong FROM tv_thanh_ky_nang WHERE mssv = ? ORDER BY id ASC');
        $stKn->execute([MSSV]);
    } else {
        $stKn = db()->prepare('SELECT id, nhom, ten_ky_nang, muc_do, bieu_tuong FROM tv_thanh_ky_nang WHERE mssv = ? AND nhom = ? ORDER BY id ASC');
        $stKn->execute([MSSV, $nhomChon]);
    }
    $dsKyNang = $stKn->fetchAll();
} catch (PDOException $e) {
    error_log('Lỗi đọc kỹ năng của Thanh: ' . $e->getMessage());
}

// Thống kê số lượng kỹ năng theo từng nhóm để hiển thị huy hiệu số lượng
$soLuongTheoNhom = [];
try {
    $stCount = db()->prepare('SELECT nhom, COUNT(*) as sl FROM tv_thanh_ky_nang WHERE mssv = ? GROUP BY nhom');
    $stCount->execute([MSSV]);
    while ($r = $stCount->fetch()) {
        $soLuongTheoNhom[$r['nhom']] = (int)$r['sl'];
    }
} catch (PDOException $e) {
    error_log('Lỗi đếm kỹ năng của Thanh: ' . $e->getMessage());
}
$tongSoKyNang = array_sum($soLuongTheoNhom);

// --- CHỨC NĂNG CSDL 2: TÌM KIẾM MÔN HỌC TRONG THỜI KHÓA BIỂU (BẢNG tv_thanh_tkb) ---
$tuKhoa = trim($_GET['tim_mon'] ?? '');
$ketQuaTimKiem = [];
$daTimKiem = ($tuKhoa !== '');

if ($daTimKiem) {
    try {
        $like = '%' . addcslashes($tuKhoa, '%_\\') . '%';
        $stTkb = db()->prepare('SELECT id, ma_hoc_phan, ten_mon, so_tin_chi, thu, tiet_hoc, phong_hoc, giang_vien 
                                FROM tv_thanh_tkb 
                                WHERE mssv = ? AND (ten_mon LIKE ? OR ma_hoc_phan LIKE ? OR phong_hoc LIKE ? OR giang_vien LIKE ?) 
                                ORDER BY id ASC');
        $stTkb->execute([MSSV, $like, $like, $like, $like]);
        $ketQuaTimKiem = $stTkb->fetchAll();
    } catch (PDOException $e) {
        error_log('Lỗi tìm kiếm TKB của Thanh: ' . $e->getMessage());
    }
}

// Đọc toàn bộ danh sách môn học thời khóa biểu
$tatCaTkb = [];
try {
    $stAll = db()->prepare('SELECT * FROM tv_thanh_tkb WHERE mssv = ? ORDER BY id ASC');
    $stAll->execute([MSSV]);
    $tatCaTkb = $stAll->fetchAll();
} catch (PDOException $e) {
    error_log('Lỗi đọc toàn bộ TKB của Thanh: ' . $e->getMessage());
}

// Cấu hình thông tin Header dùng chung
$tieuDe = 'Giới thiệu thành viên: Nguyễn Tuấn Nhật Thanh';
$trang = '';
$moTa = 'Trang cá nhân Nguyễn Tuấn Nhật Thanh (3120224132) - Nhóm 01 UEDBookHub. Cài đặt 2 chức năng CSDL MySQL: Lọc kỹ năng theo nhóm và Tìm kiếm thời khóa biểu.';
$cssThem = ['thanhvien/3120224132_thanh/style.css'];

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh">

  <!-- Breadcrumb điều hướng đồng bộ -->
  <nav class="duong-dan" aria-label="Đường dẫn trang">
    <ol class="duong-dan__danh-sach">
      <li class="duong-dan__muc"><a class="duong-dan__lien-ket" href="../../index.php">Trang chủ</a></li>
      <li class="duong-dan__muc"><a class="duong-dan__lien-ket" href="../../gioi-thieu.php">Giới thiệu nhóm</a></li>
      <li class="duong-dan__muc" aria-current="page">Nguyễn Tuấn Nhật Thanh</li>
    </ol>
  </nav>

  <!-- Thông báo Flash (PRG) -->
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

  <!-- KHỐI HỒ SƠ CÁ NHÂN (ĐỒNG BỘ THEO DESIGN TOKENS CỦA NHÓM) -->
  <article class="ho-so-ca-nhan">
    <div class="ho-so-ca-nhan__than">
      <div class="ho-so-ca-nhan__hang-dau">
        <img class="ho-so-ca-nhan__anh" src="images/nguyen-tuan-nhat-thanh.jpg" alt="Ảnh chân dung Nguyễn Tuấn Nhật Thanh" width="160" height="160">
        <div class="ho-so-ca-nhan__thong-tin">
          <h1 class="ho-so-ca-nhan__ten">Nguyễn Tuấn Nhật Thanh</h1>
          <div class="ho-so-ca-nhan__vai-tro">Thành viên Nhóm 01 – Đề tài UEDBookHub</div>
          <p class="ho-so-ca-nhan__mo-ta">
            Sinh viên chuyên ngành Công nghệ Thông tin, Trường Đại học Sư phạm – Đại học Đà Nẵng.
            Đảm nhận <strong>Chức năng 3 (Quản trị CRUD thực thể chính & Thiết kế lược đồ ERD CSDL)</strong> cho hệ thống UEDBookHub.
          </p>
          <div class="ho-so-ca-nhan__the-nhanh">
            <span class="huy-hieu huy-hieu--chinh">MSSV: 3120224132</span>
            <span class="huy-hieu huy-hieu--phu">Lớp: 24CNTT1</span>
            <span class="huy-hieu">Khoa Toán – Tin</span>
          </div>
        </div>
      </div>

      <!-- Danh sách thông tin chi tiết & tiện ích tương tác -->
      <div class="ho-so-ca-nhan__chi-tiet">
        <div class="ho-so-ca-nhan__thong-tin-nhanh">
          <p><strong>📧 Email:</strong> <span id="email-text">nguyentuannhatthanh06092006@gmail.com</span></p>
          <p><strong>🏫 Trường:</strong> Đại học Sư phạm – Đại học Đà Nẵng</p>
          <p><strong>💻 Định hướng:</strong> Lập trình Web Fullstack, Thiết kế CSDL quan hệ, Kiến trúc phần mềm</p>
        </div>
        <div class="ho-so-ca-nhan__nut-hanh-dong">
          <button id="btn-copy-email" class="nut nut--phu nut--nho" type="button" onclick="saoChepEmail()">📋 Sao chép Email</button>
          <button id="btn-download-info" class="nut nut--phu nut--nho" type="button" onclick="taiThongTin()">💾 Tải thông tin cá nhân</button>
          <span id="thong-bao-copy" class="chu-thanh-cong" style="display:none; font-size:0.85rem; margin-left:8px;" aria-live="polite">✓ Đã sao chép!</span>
        </div>
      </div>
    </div>
  </article>

  <!-- CHỨC NĂNG CSDL 1: LỌC KỸ NĂNG THEO NHÓM (BẢNG tv_thanh_ky_nang) -->
  <section id="ky-nang-csdl" class="khoi-chuc-nang-ca-nhan" aria-labelledby="tieu-de-ky-nang">
    <div class="khoi-chuc-nang-ca-nhan__dau">
      <div>
        <h2 id="tieu-de-ky-nang" class="khoi-chuc-nang-ca-nhan__tieu-de">1. Kỹ năng chuyên môn (CSDL MySQL: <code>tv_thanh_ky_nang</code>)</h2>
        <p class="khoi-chuc-nang-ca-nhan__mo-ta">
          Chuyển đổi từ mảng dữ liệu Bài 5 sang lưu trữ lâu dài trong bảng CSDL <code>tv_thanh_ky_nang</code>.
          Truy vấn lọc theo nhóm bằng <strong>Prepared Statement</strong> an toàn, chống SQL Injection.
        </p>
      </div>
      <div class="khoi-chuc-nang-ca-nhan__so-luong">
        <span class="huy-hieu huy-hieu--chinh"><?= count($dsKyNang) ?> / <?= $tongSoKyNang ?> Kỹ năng</span>
      </div>
    </div>

    <!-- Bộ lọc nhóm kỹ năng -->
    <div class="bo-loc-ky-nang">
      <form class="form-loc-ky-nang" method="get" action="gioithieu.php#ky-nang-csdl">
        <label for="nhom-ky-nang" class="nhan-loc">Chọn nhóm kỹ năng:</label>
        <div class="dieu-khien-loc">
          <select id="nhom-ky-nang" name="nhom" class="o-chon" onchange="this.form.submit()">
            <?php foreach ($cacNhomKyNang as $ma => $tenNhom): ?>
              <?php $so = ($ma === 'tatca') ? $tongSoKyNang : ($soLuongTheoNhom[$ma] ?? 0); ?>
              <option value="<?= e($ma) ?>" <?= $nhomChon === $ma ? 'selected' : '' ?>>
                <?= e($tenNhom) ?> (<?= $so ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="nut nut--chinh nut--nho">Lọc kỹ năng</button>
        </div>
      </form>

      <!-- Các thẻ chuyển nhóm nhanh -->
      <div class="danh-sach-the-loc" role="tablist" aria-label="Bộ lọc nhanh">
        <?php foreach ($cacNhomKyNang as $ma => $tenNhom): ?>
          <a href="gioithieu.php?nhom=<?= e($ma) ?>#ky-nang-csdl" 
             class="the-tab <?= $nhomChon === $ma ? 'the-tab--kich-hoat' : '' ?>"
             role="tab" aria-selected="<?= $nhomChon === $ma ? 'true' : 'false' ?>">
            <?= e($tenNhom) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Danh sách kỹ năng tải từ CSDL -->
    <div class="luoi-ky-nang">
      <?php if (!$dsKyNang): ?>
        <p class="thong-bao-rong">Không tìm thấy kỹ năng nào trong nhóm này.</p>
      <?php else: ?>
        <?php foreach ($dsKyNang as $kn): ?>
          <article class="the-ky-nang">
            <span class="the-ky-nang__bieu-tuong" aria-hidden="true"><?= e($kn['bieu_tuong']) ?></span>
            <div class="the-ky-nang__noi-dung">
              <h3 class="the-ky-nang__ten"><?= e($kn['ten_ky_nang']) ?></h3>
              <div class="the-ky-nang__hang-duoi">
                <span class="the-ky-nang__nhom"><?= e($cacNhomKyNang[$kn['nhom']] ?? $kn['nhom']) ?></span>
                <span class="huy-hieu <?= $kn['muc_do'] === 'Thành thạo' ? 'huy-hieu--con' : 'huy-hieu--sap-het' ?>">
                  <?= e($kn['muc_do']) ?>
                </span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- CHỨC NĂNG CSDL 2: TÌM KIẾM MÔN HỌC TRONG THỜI KHÓA BIỂU (BẢNG tv_thanh_tkb) -->
  <section id="tim-kiem-tkb" class="khoi-chuc-nang-ca-nhan" aria-labelledby="tieu-de-tkb" style="margin-top: 36px;">
    <div class="khoi-chuc-nang-ca-nhan__dau">
      <div>
        <h2 id="tieu-de-tkb" class="khoi-chuc-nang-ca-nhan__tieu-de">2. Tìm kiếm môn học trong Thời khóa biểu (CSDL MySQL: <code>tv_thanh_tkb</code>)</h2>
        <p class="khoi-chuc-nang-ca-nhan__mo-ta">
          Truy vấn trực tiếp từ bảng <code>tv_thanh_tkb</code> với Prepared Statement <code>LIKE + addcslashes</code>.
          Tìm kiếm linh hoạt theo tên học phần, mã môn học, phòng học hoặc tên giảng viên.
        </p>
      </div>
    </div>

    <!-- Biểu mẫu tìm kiếm môn học -->
    <form class="bieu-mau bieu-mau--tim-kiem" method="get" action="gioithieu.php#tim-kiem-tkb">
      <div class="bieu-mau__hang-ngang">
        <div class="bieu-mau__nhom" style="flex: 1;">
          <label for="tim_mon">Nhập tên môn học, mã môn hoặc phòng học:</label>
          <input class="bieu-mau__o-nhap" id="tim_mon" name="tim_mon" type="text"
                 value="<?= e($tuKhoa) ?>" placeholder="Ví dụ: Lập trình web, An toàn thông tin, B3-206...">
        </div>
        <div class="bieu-mau__nhom" style="align-self: flex-end;">
          <button type="submit" class="nut nut--chinh">🔍 Tìm môn học</button>
          <?php if ($daTimKiem): ?>
            <a href="gioithieu.php#tim-kiem-tkb" class="nut nut--phu">Xóa tìm kiếm</a>
          <?php endif; ?>
        </div>
      </div>
    </form>

    <!-- Kết quả tìm kiếm (nếu có từ khóa) -->
    <?php if ($daTimKiem): ?>
      <div class="ket-qua-tim-kiem" style="margin-top: 20px;">
        <h3 class="tieu-de-phu">
          Kết quả tìm kiếm cho từ khóa: <em>"<?= e($tuKhoa) ?>"</em>
          <span class="huy-hieu huy-hieu--chinh" style="margin-left: 8px;"><?= count($ketQuaTimKiem) ?> môn học</span>
        </h3>

        <?php if (!$ketQuaTimKiem): ?>
          <div class="thong-bao-flash thong-bao-flash--loi" style="margin-top: 12px;">
            Không tìm thấy môn học nào khớp với từ khóa "<strong><?= e($tuKhoa) ?></strong>". Vui lòng thử từ khóa khác!
          </div>
        <?php else: ?>
          <div class="bang-cuon" style="margin-top: 12px;">
            <table>
              <caption>Danh sách học phần khớp với từ khóa tìm kiếm trong CSDL</caption>
              <thead>
                <tr>
                  <th scope="col">STT</th>
                  <th scope="col">Mã học phần</th>
                  <th scope="col">Tên học phần</th>
                  <th scope="col">Số TC</th>
                  <th scope="col">Thứ</th>
                  <th scope="col">Tiết học</th>
                  <th scope="col">Phòng học</th>
                  <th scope="col">Giảng viên</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ketQuaTimKiem as $idx => $m): ?>
                  <tr>
                    <td style="text-align: center;"><?= $idx + 1 ?></td>
                    <td style="font-family: monospace; font-weight: bold; color: #1e3a8a;"><?= e($m['ma_hoc_phan']) ?></td>
                    <td><strong><?= e($m['ten_mon']) ?></strong></td>
                    <td style="text-align: center;"><?= (int)$m['so_tin_chi'] ?></td>
                    <td style="text-align: center;"><span class="huy-hieu huy-hieu--con"><?= e($m['thu']) ?></span></td>
                    <td style="text-align: center;"><?= e($m['tiet_hoc']) ?></td>
                    <td style="text-align: center; font-weight: 600;"><?= e($m['phong_hoc']) ?></td>
                    <td><?= e($m['giang_vien'] ?? 'Đang cập nhật') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Toàn bộ Thời khóa biểu tuần tổng quan (Bảng chuẩn) -->
    <div style="margin-top: 28px;">
      <h3 class="tieu-de-phu">Lịch học Thời khóa biểu tuần tổng quan (Học kỳ 1 năm học 2026 – 2027)</h3>
      <div class="bang-cuon" style="margin-top: 12px;">
        <table>
          <caption>Thời khóa biểu toàn bộ các học phần học kỳ 1 của sinh viên Nguyễn Tuấn Nhật Thanh</caption>
          <thead>
            <tr>
              <th scope="col">Mã HP</th>
              <th scope="col">Tên học phần</th>
              <th scope="col">Số TC</th>
              <th scope="col">Thứ</th>
              <th scope="col">Tiết học</th>
              <th scope="col">Phòng học</th>
              <th scope="col">Giảng viên phụ trách</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tatCaTkb as $tkb): ?>
              <tr>
                <td style="font-family: monospace; font-weight: bold; color: #1e3a8a;"><?= e($tkb['ma_hoc_phan']) ?></td>
                <td><strong><?= e($tkb['ten_mon']) ?></strong></td>
                <td style="text-align: center;"><?= (int)$tkb['so_tin_chi'] ?></td>
                <td style="text-align: center;"><span class="huy-hieu huy-hieu--con"><?= e($tkb['thu']) ?></span></td>
                <td style="text-align: center;"><?= e($tkb['tiet_hoc']) ?></td>
                <td style="text-align: center; font-weight: 600;"><?= e($tkb['phong_hoc']) ?></td>
                <td><?= e($tkb['giang_vien'] ?? 'ThS. Nguyễn Hữu Hải') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- KHỐI DỰ ÁN & SỞ THÍCH -->
  <section class="khoi-chuc-nang-ca-nhan" style="margin-top: 36px;">
    <h2 class="khoi-chuc-nang-ca-nhan__tieu-de">Dự án tham gia &amp; Sở thích cá nhân</h2>
    <div class="luoi-hai-cot" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 16px;">
      <div class="the-noi-dung" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px;">
        <h3 style="color: #1e3a8a; font-size: 1.15rem; margin-top: 0;">🚀 Dự án UEDBookHub (Bài tập nhóm)</h3>
        <p style="color: #475569; line-height: 1.6;">
          Tham gia xây dựng website động UEDBookHub: chịu trách nhiệm chính về module Quản trị CRUD giáo trình (Thêm, Sửa, Xóa kiểm tra ràng buộc FK 1451, Phân trang quản trị) và thiết kế sơ đồ quan hệ ERD chuẩn 3NF.
        </p>
      </div>
      <div class="the-noi-dung" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px;">
        <h3 style="color: #0284c7; font-size: 1.15rem; margin-top: 0;">💡 Sở thích &amp; Định hướng học tập</h3>
        <p style="color: #475569; line-height: 1.6;">
          Yêu thích khám phá các công nghệ Web hiện đại, tối ưu hóa câu truy vấn CSDL quan hệ MySQL, lập trình hướng đối tượng OOP và tích cực tham gia các hoạt động nghiên cứu khoa học sinh viên.
        </p>
      </div>
    </div>
  </section>

  <!-- KHỐI GỬI LỜI NHẮN (POST / PRG) -->
  <section id="form-loi-nhan" class="khoi-chuc-nang-ca-nhan" style="margin-top: 36px;">
    <h2 class="khoi-chuc-nang-ca-nhan__tieu-de">Gửi lời nhắn cho Thanh</h2>
    <p class="khoi-chuc-nang-ca-nhan__mo-ta">
      Bạn có góp ý hoặc câu hỏi nào về các module quản trị hay dự án? Hãy để lại lời nhắn bên dưới nhé!
    </p>

    <form class="bieu-mau" method="post" action="gioithieu.php#form-loi-nhan" style="max-width: 600px; margin-top: 16px;">
      <input type="hidden" name="form_type" value="loi_nhan">
      <div class="bieu-mau__nhom">
        <label for="ten_nguoi_gui">Họ và tên của bạn:</label>
        <input class="bieu-mau__o-nhap" id="ten_nguoi_gui" name="ten_nguoi_gui" type="text" placeholder="Ví dụ: Trần Văn Tú, Thầy Nguyễn Hữu Hải...">
      </div>
      <div class="bieu-mau__nhom">
        <label for="loi_nhan">Nội dung lời nhắn <span class="dau-sao" aria-hidden="true">*</span>:</label>
        <textarea class="bieu-mau__o-nhap" id="loi_nhan" name="loi_nhan" rows="3" maxlength="500" required placeholder="Nhập lời nhắn của bạn (tối đa 500 ký tự)..."></textarea>
      </div>
      <button class="nut nut--chinh" type="submit">Gửi lời nhắn</button>
    </form>
  </section>

  <!-- KHỐI ĐẾM NGƯỢC ĐẾN KỲ THI CUỐI KỲ -->
  <section class="khoi-chuc-nang-ca-nhan" style="margin-top: 36px; text-align: center;">
    <h2 class="khoi-chuc-nang-ca-nhan__tieu-de">⏳ Đếm ngược đến kỳ thi cuối kỳ</h2>
    <p style="color: #64748b; margin-bottom: 16px;">Mục tiêu học tập: Hoàn thành xuất sắc học phần Thiết kế &amp; Lập trình Web.</p>

    <div class="dem-nguoc" id="countdown-box" style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;" aria-live="polite">
      <div class="the-dem-nguoc" style="background: #1e3a8a; color: white; padding: 14px 20px; border-radius: 8px; min-width: 80px;">
        <span id="days" style="display: block; font-size: 2rem; font-weight: 700;">0</span>
        <span style="font-size: 0.85rem; text-transform: uppercase;">Ngày</span>
      </div>
      <div class="the-dem-nguoc" style="background: #0284c7; color: white; padding: 14px 20px; border-radius: 8px; min-width: 80px;">
        <span id="hours" style="display: block; font-size: 2rem; font-weight: 700;">0</span>
        <span style="font-size: 0.85rem; text-transform: uppercase;">Giờ</span>
      </div>
      <div class="the-dem-nguoc" style="background: #0d9488; color: white; padding: 14px 20px; border-radius: 8px; min-width: 80px;">
        <span id="minutes" style="display: block; font-size: 2rem; font-weight: 700;">0</span>
        <span style="font-size: 0.85rem; text-transform: uppercase;">Phút</span>
      </div>
      <div class="the-dem-nguoc" style="background: #d97706; color: white; padding: 14px 20px; border-radius: 8px; min-width: 80px;">
        <span id="seconds" style="display: block; font-size: 2rem; font-weight: 700;">0</span>
        <span style="font-size: 0.85rem; text-transform: uppercase;">Giây</span>
      </div>
    </div>
  </section>

</main>

<script>
// Sao chép email vào clipboard
function saoChepEmail() {
  const email = document.getElementById('email-text').innerText;
  navigator.clipboard.writeText(email).then(() => {
    const tb = document.getElementById('thong-bao-copy');
    tb.style.display = 'inline';
    setTimeout(() => { tb.style.display = 'none'; }, 2500);
  });
}

// Tải thông tin cá nhân dưới dạng JSON
function taiThongTin() {
  const info = {
    hoTen: 'Nguyễn Tuấn Nhật Thanh',
    mssv: '3120224132',
    lop: '24CNTT1',
    truong: 'Đại học Sư phạm - Đại học Đà Nẵng',
    email: 'nguyentuannhatthanh06092006@gmail.com',
    vaiTroDoAn: 'Thành viên Nhóm 01 (Phụ trách Chức năng 3: Quản trị CRUD và Sơ đồ ERD)'
  };
  const blob = new Blob([JSON.stringify(info, null, 2)], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = '3120224132_NguyenTuanNhatThanh_ThongTin.json';
  a.click();
}

// Đếm ngược ngày thi
(function() {
  const ngayThi = new Date('2026-12-25T08:00:00').getTime();
  function capNhatDemNguoc() {
    const bayGio = new Date().getTime();
    const khoangCach = ngayThi - bayGio;
    if (khoangCach > 0) {
      document.getElementById('days').innerText = Math.floor(khoangCach / (1000 * 60 * 60 * 24));
      document.getElementById('hours').innerText = Math.floor((khoangCach % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      document.getElementById('minutes').innerText = Math.floor((khoangCach % (1000 * 60 * 60)) / (1000 * 60));
      document.getElementById('seconds').innerText = Math.floor((khoangCach % (1000 * 60)) / 1000);
    }
  }
  capNhatDemNguoc();
  setInterval(capNhatDemNguoc, 1000);
})();
</script>

<?php
require __DIR__ . '/../../inc/footer.php';
