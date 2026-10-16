<?php
/*
 * Tệp: gioithieu.php
 * Chức năng: Hiển thị thông tin cá nhân, kỹ năng và thời khóa biểu.
 * Cách thử: Mở trang, lọc kỹ năng, tìm kiếm môn học và gửi lời nhắn.
 */

$goc = '../../';
$trang = '';
$tieuDe = 'Trang giới thiệu cá nhân - Nguyễn Tuấn Nhật Thanh';
$goc = '../../';
$trang = '';
$tieuDe = 'Trang giới thiệu cá nhân - Nguyễn Tuấn Nhật Thanh';


if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['form_type'] ?? '') === 'loi_nhan') {

    $loiNhan = trim($_POST['loi_nhan'] ?? '');

    if ($loiNhan === '' || mb_strlen($loiNhan) > 500) {
        header('Location: gioithieu.php?gui=loi#form-loi-nhan');
        exit;
    }

    header('Location: gioithieu.php?gui=thanhcong#form-loi-nhan');
    exit;
}

// Ham ho tro ma hoa noi dung HTML
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../inc/header.php';
?>


<!-- CSS rieng cua trang ca nhan -->
<link rel="stylesheet" href="./style.css">

<main class="trang-ca-nhan">

    <h1>Thông Tin Cá Nhân Thành Viên</h1>
    <h2>Giới thiệu Nguyễn Tuấn Nhật Thanh</h2>

    <!-- THONG TIN CA NHAN -->
    <section class="accordion-item">
        <h3>
            <button
                type="button"
                class="accordion-button"
                aria-expanded="true"
                aria-controls="personal-content">
                Thông tin cá nhân
            </button>
        </h3>

        <div id="personal-content" class="accordion-content">
            <figure>
                <img
                    id="profileImage"
                    src="images/nguyen-tuan-nhat-thanh.jpg"
                    alt="Ảnh chân dung Nguyễn Tuấn Nhật Thanh"
                    width="200"
                    class="ho-so-ca-nhan_anh"
                    tabindex="0">

                <figcaption>Ảnh chân dung Nguyễn Tuấn Nhật Thanh</figcaption>
            </figure>

            <button id="downloadInfoBtn" type="button">
                Tải thông tin cá nhân
            </button>

            <ul>
                <li><strong>Họ và tên:</strong> Nguyễn Tuấn Nhật Thanh</li>
                <li><strong>Mã sinh viên:</strong> 3120224132</li>
                <li><strong>Vai trò:</strong> Thành viên nhóm</li>
                <li><strong>Lớp:</strong> 24CNTT1</li>
                <li><strong>Trường:</strong> Đại học Sư phạm - Đại học Đà Nẵng</li>

                <li>
                    <strong>Email:</strong>
                    <span id="email">nguyentuannhatthanh06092006@gmail.com</span>

                    <button id="copyEmailBtn" type="button">
                        Sao chép email
                    </button>

                    <p id="copyMessage" aria-live="polite"></p>
                </li>
            </ul>
        </div>
    </section>

    <!-- DU AN VA SO THICH -->
    <article class="accordion-item">
        <h3>
            <button
                type="button"
                class="accordion-button"
                aria-expanded="true"
                aria-controls="project-content">
                Dự án / Sở thích
            </button>
        </h3>

        <div id="project-content" class="accordion-content">
            <p>
                Tôi yêu thích lập trình web và tìm hiểu các công nghệ mới.
                Tôi đặc biệt quan tâm đến việc xây dựng các trang web có
                giao diện đơn giản, dễ sử dụng.
            </p>

            <p>
                Trong thời gian học tập, tôi muốn nâng cao kỹ năng HTML,
                CSS, JavaScript và khả năng làm việc nhóm.
            </p>
        </div>
    </article>

   
<?php
$dsKyNang = [
    'laptrinh' => [
        'HTML',
        'CSS',
        'C++',
        'Python',
        'JavaScript'
    ],
    'kynangmem' => [
        'Làm việc nhóm',
        'Thuyết trình',
        'Quản lý thời gian'
    ]
];

$nhom = $_GET['nhom'] ?? 'tatca';

if (!in_array($nhom, ['tatca', 'laptrinh', 'kynangmem'], true)) {
    $nhom = 'tatca';
}

$kyNangHienThi = [];

if ($nhom === 'tatca') {
    foreach ($dsKyNang as $danhSach) {
        $kyNangHienThi = array_merge($kyNangHienThi, $danhSach);
    }
} else {
    $kyNangHienThi = $dsKyNang[$nhom];
}
?>


<section class="accordion-item" id="ky-nang">
    <h3>
        <button
            type="button"
            class="accordion-button"
            aria-expanded="true"
            aria-controls="noi-dung-ky-nang"
            onclick="toggleKyNang(this)">
            Kỹ năng
        </button>
    </h3>

    <div class="accordion-content" id="noi-dung-ky-nang">
        <form >
            <label for="nhom">Chọn nhóm kỹ năng:</label>

            <select name="nhom" id="nhom">
                <option value="tatca" <?= $nhom === 'tatca' ? 'selected' : '' ?>>
                    Tất cả
                </option>
                <option value="laptrinh" <?= $nhom === 'laptrinh' ? 'selected' : '' ?>>
                     Lập trình
                </option>
                <option value="kynangmem" <?= $nhom === 'kynangmem' ? 'selected' : '' ?>>
                    Kỹ năng mềm
                </option>
            </select>

            <button type="submit">Lọc kỹ năng</button>
        </form>

        <ul class="skills">
            <?php foreach ($kyNangHienThi as $kyNang): ?>
                <li>
                   <?= e($kyNang) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>





<?php
$dsMonHoc = [
    [
        'ma' => '31221010',
        'ten' => 'An toàn thông tin',
        'thu' => 'Thứ 2',
        'phong' => 'B3-206'
    ],
    [
        'ma' => '31241283',
        'ten' => 'Hệ quản trị cơ sở dữ liệu',
        'thu' => 'Thứ 4',
        'phong' => 'A5-404B'
    ],
    [
        'ma' => '31231330',
        'ten' => 'Khai phá dữ liệu',
        'thu' => 'Thứ 5',
        'phong' => 'A5-210'
    ],
    [
        'ma' => '31231755',
        'ten' => 'Thiết kế và lập trình web',
        'thu' => 'Thứ 7',
        'phong' => 'B3-303'
    ],
    [
        'ma' => '31231016',
        'ten' => 'Công nghệ phần mềm',
        'thu' => 'Thứ 5',
        'phong' => 'B3-503'
    ],
];

$tuKhoa = trim($_GET['tim_mon'] ?? '');
$ketQuaMon = [];

if ($tuKhoa !== '') {
    foreach ($dsMonHoc as $mon) {
        if (
            stripos($mon['ten'], $tuKhoa) !== false ||
            stripos($mon['ma'], $tuKhoa) !== false
        ) {
            $ketQuaMon[] = $mon;
        }
    }
}
?>




<section class="accordion-item" id="tim-kiem-mon-hoc">
    <h3>Tìm kiếm môn học</h3>

    <div class="accordion-content">
        <form
            id="form-tim-mon"
            method="GET"
            action="gioithieu.php#tim-kiem-mon-hoc">

            <label for="tim_mon">
                Nhập tên môn học hoặc mã học phần:
            </label>

            <input
                type="text"
                id="tim_mon"
                name="tim_mon"
                value="<?= e($tuKhoa) ?>"
                placeholder="Ví dụ: cơ sở dữ liệu">

            <button type="submit">Tìm kiếm</button>
        </form>

        <?php if ($tuKhoa !== ''): ?>
            <h4>Kết quả tìm kiếm</h4>

            <?php if (count($ketQuaMon) > 0): ?>
                <ul>
                    <?php foreach ($ketQuaMon as $mon): ?>
                        <li>
                            <strong><?= e($mon['ten']) ?></strong>
                            — Mã: <?= e($mon['ma']) ?>
                            — <?= e($mon['thu']) ?>
                            — Phòng: <?= e($mon['phong']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Không tìm thấy môn học phù hợp.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>


    <!-- THOI KHOA BIEU -->
    <section class="accordion-item">
        <h3>
            <button
                type="button"
                class="accordion-button"
                aria-expanded="false"
                aria-controls="schedule-content">
                Thời khóa biểu tuần
            </button>
        </h3>

        <div id="schedule-content"
             class="accordion-content is-hidden">

            <div class="table-container">
                <table class="schedule">
                    <caption>
                        Thời khóa biểu học kỳ 1 năm học 2026 – 2027
                        (Nguyễn Tuấn Nhật Thanh)
                    </caption>

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
                            <td rowspan="2"><strong>31221010 - 24-0101</strong><br>An toàn thông tin<br>Phòng: B3-206</td>
                            <td></td>
                            <td rowspan="4"><strong>31241283 - 24-0102</strong><br>Hệ quản trị cơ sở dữ liệu<br>Phòng: A5-404B</td>
                            <td rowspan="3"><strong>31231330 - 24-0103</strong><br>Khai phá dữ liệu<br>Phòng: A5-210</td>
                            <td></td>
                            <td rowspan="3"><strong>31231755 - 24-0102</strong><br>Thiết kế và lập trình web<br>Phòng: B3-303</td>
                            <td></td>
                        </tr>

                        <tr>
                            <th scope="row">2</th>
                            <td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">3</th>
                            <td rowspan="2"><strong>21221904 - 24-0123</strong><br>Lịch sử Đảng Cộng sản Việt Nam<br>Phòng: A5-404C</td>
                            <td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">4</th>
                            <td></td><td></td><td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">5</th>
                            <td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">6</th>
                            <td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">7</th>
                            <td></td><td></td><td></td>
                            <td rowspan="3"><strong>31231016 - 24-0102</strong><br>Công nghệ phần mềm<br>Phòng: B3-503</td>
                            <td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">8</th>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">9</th>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">10</th>
                            <td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">11</th>
                            <td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <th scope="row">12</th>
                            <td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <section class="accordion-item" id="form-loi-nhan">
    <h2>Gửi lời nhắn</h2>

    <?php if (($_GET['gui'] ?? '') === 'thanhcong'): ?>
        <p role="status">Gửi lời nhắn thành công.</p>
    <?php elseif (($_GET['gui'] ?? '') === 'loi'): ?>
        <p role="alert">
            Vui lòng nhập lời nhắn từ 1 đến 500 ký tự.
        </p>
    <?php endif; ?>

    <form method="POST" action="gioithieu.php#form-loi-nhan">
        <input type="hidden" name="form_type" value="loi_nhan">

        <label for="loi_nhan">Nội dung lời nhắn:</label>
        <textarea
            id="loi_nhan"
            name="loi_nhan"
            maxlength="500"
            required></textarea>

        <button type="submit">Gửi lời nhắn</button>
    </form>
</section>


    <!-- DEM NGUOC -->
    <section class="countdown-section">
        <h2>Đếm ngược đến kỳ thi cuối kỳ</h2>

        <div id="countdown" class="countdown" aria-live="polite">
            <div>
                <strong id="days">0</strong>
                <span>Ngày</span>
            </div>

            <div>
                <strong id="hours">0</strong>
                <span>Giờ</span>
            </div>

            <div>
                <strong id="minutes">0</strong>
                <span>Phút</span>
            </div>

            <div>
                <strong id="seconds">0</strong>
                <span>Giây</span>
            </div>
        </div>

        <p id="countdownMessage"></p>
    </section>


</main>

<script>
function toggleKyNang(button) {
    const noiDung = document.getElementById('noi-dung-ky-nang');

    if (!noiDung) return;

    const dangMo = noiDung.dataset.dangMo === 'true';

    if (dangMo) {
        noiDung.style.setProperty('display', 'none', 'important');
        noiDung.dataset.dangMo = 'false';
        button.setAttribute('aria-expanded', 'false');
    } else {
        noiDung.style.setProperty('display', 'block', 'important');
        noiDung.dataset.dangMo = 'true';
        button.setAttribute('aria-expanded', 'true');
    }
}
</script>

<script src="./js/canhan.js" defer></script>

<?php
require_once __DIR__ . '/../../inc/footer.php';
?>
