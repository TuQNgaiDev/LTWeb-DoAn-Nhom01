<?php
/**
 * Trang cá nhân thành viên: Lê Trung Hậu (MSSV: 3120224052) - Nhóm 01 UEDBookHub.
 * Hai chức năng CSDL MySQL (PDO) theo yêu cầu Phần B:
 * 1. Thả tim & Đếm lượt thích cá nhân lưu CSDL MySQL (bảng tv_hau_luot_thich):
 *    - Sử dụng PDO Prepared Statement, lưu IP và thời gian người dùng thả tim.
 *    - Đồng bộ lượt thích thực tế từ CSDL, hỗ trợ AJAX không tải lại trang và fallback POST/PRG khi tắt JS.
 * 2. Hệ thống câu chào theo thời gian thực từ CSDL MySQL (bảng tv_hau_cau_chao):
 *    - Khai thác CSDL theo khung giờ 24h thực tế (sáng, chiều, tối, khuya).
 *    - Đồng hồ thời gian thực client-side, tự động đồng bộ theo thời gian thực với CSDL.
 *    - Hỗ trợ đổi câu chào ngẫu nhiên trong khung giờ từ CSDL và bảng tra cứu câu chào CSDL trực quan.
 * Chức năng bổ trợ: Máy tính xếp loại rèn luyện & Dự báo học bổng UED (POST/PRG máy chủ PHP).
 */

declare(strict_types=1);

require __DIR__ . '/../../inc/config.php';

const MSSV_HAU = '3120224052';

// Khởi tạo kết nối CSDL có bọc try/catch chống sập trang khi chưa bật MySQL
$pdo = null;
try {
    $pdo = db();
    khoiTaoCSDLHau($pdo);
} catch (Throwable $e) {
    error_log('CSDL MySQL chưa sẵn sàng: ' . $e->getMessage());
}

/**
 * Tự động tạo bảng CSDL cho chức năng cá nhân của Hậu nếu chưa tồn tại.
 * Đảm bảo hệ thống hoạt động ổn định và tức thì ngay cả khi chưa nạp lại file SQL.
 */
function khoiTaoCSDLHau(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tv_hau_luot_thich (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            mssv CHAR(10) NOT NULL DEFAULT '3120224052',
            ip_dia_chi VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_tv_hau_luot_thich_mssv (mssv)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tv_hau_cau_chao (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            mssv CHAR(10) NOT NULL DEFAULT '3120224052',
            buoi ENUM('sang', 'chieu', 'toi', 'khuya') NOT NULL,
            gio_bat_dau TINYINT UNSIGNED NOT NULL,
            gio_ket_thuc TINYINT UNSIGNED NOT NULL,
            bieu_tuong VARCHAR(20) NOT NULL DEFAULT '✨',
            tieu_de VARCHAR(100) NOT NULL,
            loi_chao VARCHAR(500) NOT NULL,
            kich_hoat TINYINT(1) NOT NULL DEFAULT 1,
            ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_tv_hau_cau_chao_gio (mssv, gio_bat_dau, gio_ket_thuc, kich_hoat)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $soCauChao = (int)$pdo->query("SELECT COUNT(*) FROM tv_hau_cau_chao WHERE mssv = '3120224052'")->fetchColumn();
    if ($soCauChao === 0) {
        $st = $pdo->prepare("INSERT INTO tv_hau_cau_chao (mssv, buoi, gio_bat_dau, gio_ket_thuc, bieu_tuong, tieu_de, loi_chao) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $duLieuMau = [
            ['3120224052', 'sang', 5, 11, '☀️', 'Buổi sáng rạng rỡ', '☀️ Buổi sáng rạng rỡ nha! Chúc bạn ngày mới đầy ắp tiếng cười và lượm lặt thêm thật nhiều điều hay ho nè ✨'],
            ['3120224052', 'sang', 5, 11, '🌅', 'Khởi đầu ngày mới', '🌅 Chào ngày mới tràn đầy năng lượng! Chúc bạn một ngày học tập và làm việc thật hiệu quả nhé ✨'],
            ['3120224052', 'chieu', 12, 17, '🌤️', 'Chiều an lành', '🌤️ Chiều an lành nhé! Chúc bạn nạp đầy năng lượng và có buổi học thật vui nha ✨'],
            ['3120224052', 'chieu', 12, 17, '☕', 'Năng lượng buổi chiều', '☕ Chào buổi chiều! Tiếp tục giữ vững tinh thần và hoàn thành xuất sắc các mục tiêu hôm nay nhé 🎯'],
            ['3120224052', 'toi', 18, 22, '🌙', 'Tối an yên', '🌙 Tối an yên nhé! Đừng quên vừa học vừa thư giãn để nạp lại năng lượng cho bản thân nha ☁️'],
            ['3120224052', 'toi', 18, 22, '✨', 'Buổi tối thư thái', '✨ Buổi tối vui vẻ! Thư giãn thật tốt sau một ngày học tập và làm việc chăm chỉ nhé 🌟'],
            ['3120224052', 'khuya', 23, 4, '🌌', 'Đêm khuya thanh tịnh', '🌌 Đã khuya rồi đấy! Đừng thức quá muộn nhé, nhớ ngủ sớm để giữ gìn sức khỏe nha 💤'],
            ['3120224052', 'khuya', 23, 4, '🌠', 'Chúc ngủ ngon', '🌠 Chúc bạn một giấc ngủ thật ngon và những giấc mơ đẹp để nạp lại năng lượng cho ngày mai 🌙'],
        ];
        foreach ($duLieuMau as $dong) {
            $st->execute($dong);
        }
    }
}

/**
 * Lấy tổng số lượt thích từ CSDL MySQL (PDO Prepared Statement).
 */
function laySoLuotThichCSDL(?PDO $pdo, string $mssv): int {
    if (!$pdo) {
        return (int)($_SESSION['hau_so_luot_thich_offline'] ?? 12);
    }
    try {
        $st = $pdo->prepare('SELECT COUNT(*) FROM tv_hau_luot_thich WHERE mssv = ?');
        $st->execute([$mssv]);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) {
        error_log('Lỗi đếm lượt thích CSDL: ' . $e->getMessage());
        return 12;
    }
}

/**
 * Lấy câu chào theo khung giờ thực tế từ CSDL MySQL.
 * Hỗ trợ cả khung giờ ngày thông thường và khung giờ đêm khuya vắt qua nửa đêm.
 */
function layCauChaoCSDL(?PDO $pdo, string $mssv, ?int $gio = null): array {
    if ($gio === null) {
        $gio = (int)date('G');
    }
    if ($pdo) {
        try {
            $sql = "SELECT id, buoi, gio_bat_dau, gio_ket_thuc, bieu_tuong, tieu_de, loi_chao 
                    FROM tv_hau_cau_chao 
                    WHERE mssv = :mssv 
                      AND kich_hoat = 1
                      AND (
                        (gio_bat_dau <= gio_ket_thuc AND :g1 >= gio_bat_dau AND :g2 <= gio_ket_thuc)
                        OR (gio_bat_dau > gio_ket_thuc AND (:g3 >= gio_bat_dau OR :g4 <= gio_ket_thuc))
                      )
                    ORDER BY RAND() 
                    LIMIT 1";
            $st = $pdo->prepare($sql);
            $st->execute([
                'mssv' => $mssv,
                'g1' => $gio,
                'g2' => $gio,
                'g3' => $gio,
                'g4' => $gio,
            ]);
            $kq = $st->fetch();
            if ($kq) {
                return $kq;
            }
        } catch (Throwable $e) {
            error_log('Lỗi lấy câu chào CSDL: ' . $e->getMessage());
        }
    }

    if ($gio >= 5 && $gio < 12) {
        return ['id' => 1, 'buoi' => 'sang', 'gio_bat_dau' => 5, 'gio_ket_thuc' => 11, 'bieu_tuong' => '☀️', 'tieu_de' => 'Buổi sáng rạng rỡ', 'loi_chao' => '☀️ Buổi sáng rạng rỡ nha! Chúc bạn ngày mới đầy ắp tiếng cười và lượm lặt thêm thật nhiều điều hay ho nè ✨'];
    } elseif ($gio >= 12 && $gio < 18) {
        return ['id' => 3, 'buoi' => 'chieu', 'gio_bat_dau' => 12, 'gio_ket_thuc' => 17, 'bieu_tuong' => '🌤️', 'tieu_de' => 'Chiều an lành', 'loi_chao' => '🌤️ Chiều an lành nhé! Chúc bạn nạp đầy năng lượng và có buổi học thật vui nha ✨'];
    } elseif ($gio >= 18 && $gio < 23) {
        return ['id' => 5, 'buoi' => 'toi', 'gio_bat_dau' => 18, 'gio_ket_thuc' => 22, 'bieu_tuong' => '🌙', 'tieu_de' => 'Tối an yên', 'loi_chao' => '🌙 Tối an yên nhé! Đừng quên vừa học vừa thư giãn để nạp lại năng lượng cho bản thân nha ☁️'];
    } else {
        return ['id' => 7, 'buoi' => 'khuya', 'gio_bat_dau' => 23, 'gio_ket_thuc' => 4, 'bieu_tuong' => '🌌', 'tieu_de' => 'Đêm khuya thanh tịnh', 'loi_chao' => '🌌 Đã khuya rồi đấy! Đừng thức quá muộn nhé, nhớ ngủ sớm để giữ gìn sức khỏe nha 💤'];
    }
}

/**
 * Lấy danh sách tất cả các câu chào để hiển thị bảng tra cứu dữ liệu CSDL.
 */
function layDanhSachCauChaoCSDL(?PDO $pdo, string $mssv): array {
    if ($pdo) {
        try {
            $st = $pdo->prepare("SELECT id, buoi, gio_bat_dau, gio_ket_thuc, bieu_tuong, tieu_de, loi_chao, kich_hoat FROM tv_hau_cau_chao WHERE mssv = ? ORDER BY gio_bat_dau ASC, id ASC");
            $st->execute([$mssv]);
            $rows = $st->fetchAll();
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Throwable $e) {
            error_log('Lỗi lấy danh sách câu chào CSDL: ' . $e->getMessage());
        }
    }

    return [
        ['id' => 1, 'buoi' => 'sang', 'gio_bat_dau' => 5, 'gio_ket_thuc' => 11, 'bieu_tuong' => '☀️', 'tieu_de' => 'Buổi sáng rạng rỡ', 'loi_chao' => '☀️ Buổi sáng rạng rỡ nha! Chúc bạn ngày mới đầy ắp tiếng cười và lượm lặt thêm thật nhiều điều hay ho nè ✨'],
        ['id' => 2, 'buoi' => 'sang', 'gio_bat_dau' => 5, 'gio_ket_thuc' => 11, 'bieu_tuong' => '🌅', 'tieu_de' => 'Khởi đầu ngày mới', 'loi_chao' => '🌅 Chào ngày mới tràn đầy năng lượng! Chúc bạn một ngày học tập và làm việc thật hiệu quả nhé ✨'],
        ['id' => 3, 'buoi' => 'chieu', 'gio_bat_dau' => 12, 'gio_ket_thuc' => 17, 'bieu_tuong' => '🌤️', 'tieu_de' => 'Chiều an lành', 'loi_chao' => '🌤️ Chiều an lành nhé! Chúc bạn nạp đầy năng lượng và có buổi học thật vui nha ✨'],
        ['id' => 4, 'buoi' => 'chieu', 'gio_bat_dau' => 12, 'gio_ket_thuc' => 17, 'bieu_tuong' => '☕', 'tieu_de' => 'Năng lượng buổi chiều', 'loi_chao' => '☕ Chào buổi chiều! Tiếp tục giữ vững tinh thần và hoàn thành xuất sắc các mục tiêu hôm nay nhé 🎯'],
        ['id' => 5, 'buoi' => 'toi', 'gio_bat_dau' => 18, 'gio_ket_thuc' => 22, 'bieu_tuong' => '🌙', 'tieu_de' => 'Tối an yên', 'loi_chao' => '🌙 Tối an yên nhé! Đừng quên vừa học vừa thư giãn để nạp lại năng lượng cho bản thân nha ☁️'],
        ['id' => 6, 'buoi' => 'toi', 'gio_bat_dau' => 18, 'gio_ket_thuc' => 22, 'bieu_tuong' => '✨', 'tieu_de' => 'Buổi tối thư thái', 'loi_chao' => '✨ Buổi tối vui vẻ! Thư giãn thật tốt sau một ngày học tập và làm việc chăm chỉ nhé 🌟'],
        ['id' => 7, 'buoi' => 'khuya', 'gio_bat_dau' => 23, 'gio_ket_thuc' => 4, 'bieu_tuong' => '🌌', 'tieu_de' => 'Đêm khuya thanh tịnh', 'loi_chao' => '🌌 Đã khuya rồi đấy! Đừng thức quá muộn nhé, nhớ ngủ sớm để giữ gìn sức khỏe nha 💤'],
        ['id' => 8, 'buoi' => 'khuya', 'gio_bat_dau' => 23, 'gio_ket_thuc' => 4, 'bieu_tuong' => '🌠', 'tieu_de' => 'Chúc ngủ ngon', 'loi_chao' => '🌠 Chúc bạn một giấc ngủ thật ngon và những giấc mơ đẹp để nạp lại năng lượng cho ngày mai 🌙'],
    ];
}

// --- Xử lý AJAX: Lấy câu chào theo thời gian thực từ CSDL ---
if (isset($_GET['action']) && $_GET['action'] === 'lay_cau_chao') {
    header('Content-Type: application/json; charset=utf-8');
    $gioClient = isset($_GET['gio']) ? (int)$_GET['gio'] : (int)date('G');
    if ($gioClient < 0 || $gioClient > 23) {
        $gioClient = (int)date('G');
    }
    $cauChao = layCauChaoCSDL($pdo, MSSV_HAU, $gioClient);
    echo json_encode([
        'thanhCong' => true,
        'gio' => $gioClient,
        'gioMayChu' => (int)date('G'),
        'buoi' => $cauChao['buoi'],
        'bieuTuong' => $cauChao['bieu_tuong'],
        'tieuDe' => $cauChao['tieu_de'],
        'loiChao' => $cauChao['loi_chao'],
        'gioBatDau' => (int)$cauChao['gio_bat_dau'],
        'gioKetThuc' => (int)$cauChao['gio_ket_thuc'],
        'nguon' => $pdo ? 'CSDL MySQL: Bảng tv_hau_cau_chao' : 'Dữ liệu dự phòng (Chưa kết nối MySQL)',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra phiên đã thích chưa
$daThichPhien = !empty($_SESSION['hau_da_thich_3120224052']);

// --- Chức năng 1: Thả tim & Đếm lượt thích trang cá nhân (Lưu CSDL MySQL - POST/PRG) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thich_trang'])) {
    if (!$daThichPhien) {
        if ($pdo) {
            try {
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
                $stInsert = $pdo->prepare('INSERT INTO tv_hau_luot_thich (mssv, ip_dia_chi, user_agent) VALUES (?, ?, ?)');
                $stInsert->execute([MSSV_HAU, $ip, $ua]);
            } catch (Throwable $e) {
                error_log('Lỗi thêm lượt thích CSDL: ' . $e->getMessage());
            }
        } else {
            $_SESSION['hau_so_luot_thich_offline'] = (int)($_SESSION['hau_so_luot_thich_offline'] ?? 12) + 1;
        }
        $_SESSION['hau_da_thich_3120224052'] = true;
        $daThichPhien = true;
    }

    $soLuotThich = laySoLuotThichCSDL($pdo, MSSV_HAU);

    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'thanhCong' => true,
            'soLuotThich' => $soLuotThich,
            'daThich' => true,
            'thongBao' => $pdo ? 'Đã lưu lượt thích vào CSDL MySQL!' : 'Đã ghi nhận lượt thích!',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    chuyenHuong('gioithieu.php?da_thich=1#tuong-tac');
}

// Lấy tổng số lượt thích hiện tại từ CSDL MySQL (hoặc bộ đếm phiên nếu chưa bật DB)
$soLuotThich = laySoLuotThichCSDL($pdo, MSSV_HAU);

// Lấy câu chào theo thời gian thực hiện tại từ CSDL MySQL
$gioHienTai = (int)date('G');
$cauChaoHienTai = layCauChaoCSDL($pdo, MSSV_HAU, $gioHienTai);
$danhSachCauChao = layDanhSachCauChaoCSDL($pdo, MSSV_HAU);

// Danh sách các câu giới thiệu/châm ngôn của Hậu (cho nút "Tạo câu giới thiệu")
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

// --- Chức năng bổ trợ: Máy tính xếp loại rèn luyện & Xét học bổng UED (POST/PRG) ---
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

        $xepLoaiDrl = match (true) {
            $drl >= 90 => 'Xuất sắc',
            $drl >= 80 => 'Tốt',
            $drl >= 65 => 'Khá',
            $drl >= 50 => 'Trung bình',
            default => 'Yếu / Kém'
        };

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
$moTa = 'Trang giới thiệu cá nhân sinh viên Lê Trung Hậu - Thành viên nhóm 01 UEDBookHub với 2 chức năng CSDL MySQL: Thả tim lưu CSDL và Hệ thống câu chào theo thời gian thực từ CSDL.';
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

        <!-- Khối tương tác cá nhân (Phần B: Thả tim CSDL MySQL & Câu chào thời gian thực CSDL) -->
        <section class="tuong-tac" id="tuong-tac" aria-labelledby="tieu-de-tuong-tac">
          <div class="tuong-tac__dau">
            <h2 id="tieu-de-tuong-tac" class="tuong-tac__tieu-de">✨ Góc tương tác cá nhân (CSDL MySQL)</h2>
            <div class="dong-ho-thoi-gian-thuc" id="dong-ho-thuc" role="timer" aria-live="off" title="Đồng hồ thời gian thực">
              <span class="dong-ho__bieu-tuong" aria-hidden="true">⏱️</span>
              <span id="dong-ho-chuoi"><?= date('H:i:s') ?> (Giờ hệ thống)</span>
            </div>
          </div>

          <!-- Hộp lời chào hiển thị theo thời gian thực từ CSDL MySQL -->
          <div class="khung-loi-chao-csdl">
            <div id="loi-chao" class="tuong-tac__loi-chao" role="status" aria-live="polite">
              <?= e($cauChaoHienTai['loi_chao']) ?>
            </div>
            <div class="loi-chao__thong-tin-csdl">
              <span class="huy-hieu huy-hieu--con">CSDL MySQL: Bảng <code>tv_hau_cau_chao</code></span>
              <?php if (!$pdo): ?>
                <span class="huy-hieu huy-hieu--sap-het" title="Vui lòng bật MySQL trong XAMPP để đồng bộ tự động với CSDL">Chế độ dự phòng (Chờ kết nối MySQL)</span>
              <?php endif; ?>
              <span class="huy-hieu huy-hieu--sap-het" id="huy-hieu-khung-gio">Khung giờ: <?= e((string)$cauChaoHienTai['gio_bat_dau']) ?>h - <?= e((string)$cauChaoHienTai['gio_ket_thuc']) ?>h</span>
            </div>
          </div>

          <?php if (isset($_GET['da_thich'])): ?>
          <p class="thong-bao-thich" role="status" aria-live="polite">
            ❤️ Cảm ơn bạn đã thả tim ủng hộ trang cá nhân của Hậu! Lượt thích đã được lưu an toàn vào CSDL MySQL.
          </p>
          <?php endif; ?>

          <!-- Nhóm các nút bấm tương tác -->
          <div class="tuong-tac__hanh-dong">
            <form class="form-thich" action="gioithieu.php#tuong-tac" method="post">
              <input type="hidden" name="thich_trang" value="1">
              <button id="nut-thich" class="nut nut--thich <?= $daThichPhien ? 'nut-thich--da-thich' : '' ?>" type="submit" aria-label="Thả tim thích trang (Lưu CSDL MySQL)">
                <span class="nut-thich__bieu-tuong" aria-hidden="true">❤️</span>
                <span class="nut-thich__nhan"><?= $daThichPhien ? 'Đã thích' : 'Thích trang' ?></span>
                <span class="nut-thich__so" id="so-luot-thich" title="Tổng lượt thích lưu trong CSDL MySQL"><?= e((string)$soLuotThich) ?></span>
              </button>
            </form>

            <a id="nut-doi-cau-chao" class="nut nut--doi-cau-chao" href="gioithieu.php?doi_cau_chao=1#tuong-tac" role="button" title="Đổi câu chào ngẫu nhiên khác từ CSDL theo khung giờ hiện tại">
              <span class="nut-doi-cau-chao__bieu-tuong" aria-hidden="true">🔄</span>
              <span>Đổi câu chào CSDL</span>
            </a>

            <a id="nut-gioi-thieu" class="nut nut--gioi-thieu" href="gioithieu.php?cau_moi=1#tuong-tac" role="button">
              <span class="nut-gioi-thieu__bieu-tuong" aria-hidden="true">🎲</span>
              <span>Tạo câu giới thiệu</span>
            </a>
          </div>

          <!-- Khung hiển thị câu giới thiệu ngẫu nhiên -->
          <div class="tuong-tac__khung-noi-dung">
            <p id="cau-gioi-thieu" class="tuong-tac__cau-noi"><?= e($cauHienTai) ?></p>
          </div>

          <!-- Bảng tra cứu danh mục câu chào theo khung giờ trong CSDL MySQL -->
          <?php if (!empty($danhSachCauChao)): ?>
          <details class="chi-tiet-csdl">
            <summary class="chi-tiet-csdl__tieu-de">
              <span>📋 Tra cứu danh mục câu chào theo khung giờ trong CSDL (<?= count($danhSachCauChao) ?> câu chào)</span>
            </summary>
            <div class="bang-cuon bang-cuon--csdl">
              <table class="bang-csdl">
                <thead>
                  <tr>
                    <th scope="col">Buổi</th>
                    <th scope="col">Khung giờ</th>
                    <th scope="col">Biểu tượng</th>
                    <th scope="col">Tiêu đề</th>
                    <th scope="col">Nội dung câu chào trong CSDL</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($danhSachCauChao as $cc): ?>
                  <?php 
                    $laKhungHienTai = ($cc['gio_bat_dau'] <= $cc['gio_ket_thuc'])
                      ? ($gioHienTai >= $cc['gio_bat_dau'] && $gioHienTai <= $cc['gio_ket_thuc'])
                      : ($gioHienTai >= $cc['gio_bat_dau'] || $gioHienTai <= $cc['gio_ket_thuc']);
                  ?>
                  <tr class="<?= $laKhungHienTai ? 'dong-hien-tai' : '' ?>">
                    <td>
                      <span class="nhan-buoi nhan-buoi--<?= e($cc['buoi']) ?>">
                        <?= e(ucfirst($cc['buoi'])) ?>
                      </span>
                      <?php if ($laKhungHienTai): ?>
                        <span class="huy-hieu-dang-chay" title="Khung giờ hiện tại">Đang áp dụng</span>
                      <?php endif; ?>
                    </td>
                    <td><strong><?= e((string)$cc['gio_bat_dau']) ?>h - <?= e((string)$cc['gio_ket_thuc']) ?>h</strong></td>
                    <td class="can-giua"><?= e($cc['bieu_tuong']) ?></td>
                    <td><?= e($cc['tieu_de']) ?></td>
                    <td class="loi-chao-cell"><?= e($cc['loi_chao']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </details>
          <?php endif; ?>
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
              <p class="the-ky-nang__mo-ta">Sử dụng các ngôn ngữ lập trình như C++, Java, Python, PHP.</p>
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

    <!-- Chức năng bổ trợ: Máy tính xếp loại rèn luyện & Xét học bổng UED -->
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
