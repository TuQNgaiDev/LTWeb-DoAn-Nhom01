<?php
/** Đăng ký học liệu: kiểm tra ở máy chủ, upload ảnh, lưu JSONL và PRG. */
require __DIR__ . '/inc/config.php';
$kho = khoGiaoTrinh();
$du = array_fill_keys(['ho_ten', 'ma_sinh_vien', 'email_sv', 'so_dien_thoai', 'lop_hoc_phan', 'hinh_thuc', 'chon_giao_trinh', 'so_luong', 'ngay_hen', 'ghi_chu', 'cam_ket'], '');
$du['hinh_thuc'] = 'muon'; $du['so_luong'] = '1';
$hinhThucChon = chuoi($_GET, 'hinh-thuc');
if (in_array($hinhThucChon, ['mua', 'muon'], true)) { $du['hinh_thuc'] = $hinhThucChon; }
$idChon = soNguyen($_GET['giao-trinh'] ?? null);
if ($idChon !== null && $kho->timTheoId($idChon) !== null) { $du['chon_giao_trinh'] = (string) $idChon; }
$loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$du, $loi, $anh] = (new App\Services\KiemTraLienHe())->kiemTra($_POST, $_FILES, $kho);
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) { $loi['anh'] = 'Dữ liệu tải lên vượt giới hạn máy chủ; ảnh không được lớn hơn 2 MB.'; }
    if (!$loi) {
        $dich = $anh !== null ? __DIR__ . '/uploads/' . $anh['ten'] : null;
        if ($anh !== null && !move_uploaded_file($anh['tam'], $dich)) { throw new RuntimeException('Không lưu được ảnh tải lên.'); }
        $gio = new App\Services\GioHang();
        $donMua = [];
        if ($du['hinh_thuc'] === 'mua') {
            foreach ($gio->cacMuc() as $id => $sl) {
                $sach = $kho->timTheoId((int) $id);
                if ($sach !== null) { $donMua[] = ['id' => $sach->id, 'ten' => $sach->ten, 'soLuong' => $sl, 'donGia' => $sach->gia]; }
            }
        }
        try {
            (new App\Data\KhoLienHe(__DIR__ . '/storage/lien-he.jsonl'))->them([
                'thoiGian' => date('Y-m-d H:i:s'), ...$du, 'anh' => $anh['ten'] ?? null, 'gioHang' => $donMua,
            ]);
        } catch (Throwable $ex) { if ($dich !== null && is_file($dich)) { unlink($dich); } throw $ex; }
        flash('Đã gửi phiếu đăng ký thành công. Cảm ơn bạn!');
        chuyenHuong('lien-he.php');
    }
}
$tb = flash(); $tieuDe = 'Đăng ký và liên hệ'; $trang = 'lien-he';
$truongNhap = [
    'ho_ten' => ['Họ và tên đầy đủ', 'text', 'required minlength="3" maxlength="70"'],
    'ma_sinh_vien' => ['Mã số sinh viên', 'text', 'required pattern="[0-9]{9,10}" maxlength="10"'],
    'email_sv' => ['Email sinh viên @ued.udn.vn', 'email', 'required maxlength="254"'],
    'so_dien_thoai' => ['Số điện thoại', 'tel', 'required pattern="0[35789][0-9]{8}" maxlength="10"'],
    'lop_hoc_phan' => ['Lớp sinh hoạt hoặc khoa chuyên ngành', 'text', 'maxlength="100"'],
    'so_luong' => ['Số lượng bản yêu cầu từ 1 đến 5', 'number', 'required min="1" max="5"'],
    'ngay_hen' => ['Ngày hẹn nhận sách', 'date', 'required min="2026-09-01" max="2027-01-31"'],
];
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh"><h1>Đăng ký và liên hệ</h1>
<p>Gửi yêu cầu mượn hoặc đặt mua giáo trình. Khi chọn đặt mua, phiếu lưu kèm các mục hiện có trong giỏ hàng để quản trị viên tiếp nhận.</p>
<?php if ($tb): ?><p class="thong-bao-bieu-mau--thanh-cong" role="status"><?= e($tb) ?></p><?php endif; ?>
<?php if ($loi): ?><p class="bieu-mau__loi" role="alert">Chưa gửi phiếu. Vui lòng kiểm tra các trường báo lỗi bên dưới.</p><?php endif; ?>
<form id="bieu-mau-lien-he" class="bieu-mau" action="lien-he.php" method="post" enctype="multipart/form-data">
<fieldset class="bieu-mau__khung"><legend>Thông tin đăng ký</legend>
<?php foreach ($truongNhap as $key => [$nhan, $type, $attrs]): ?>
<div class="bieu-mau__nhom"><label class="bieu-mau__nhan" for="<?= e($key) ?>"><?= e($nhan) ?><?= $key !== 'lop_hoc_phan' ? ' (bắt buộc)' : '' ?></label>
<input class="bieu-mau__o-nhap" id="<?= e($key) ?>" name="<?= e($key) ?>" type="<?= e($type) ?>" value="<?= e($du[$key]) ?>" <?= $attrs ?> aria-describedby="loi-<?= e($key) ?>" aria-invalid="<?= isset($loi[$key]) ? 'true' : 'false' ?>">
<p class="bieu-mau__loi" id="loi-<?= e($key) ?>"><?= e($loi[$key] ?? '') ?></p></div>
<?php endforeach; ?>
<div class="bieu-mau__nhom"><label for="hinh_thuc">Hình thức yêu cầu (bắt buộc)</label><select class="bieu-mau__o-nhap" id="hinh_thuc" name="hinh_thuc" required aria-describedby="loi-hinh_thuc"><option value="">Chọn hình thức yêu cầu</option><?php foreach (['muon' => 'Đăng ký mượn', 'mua' => 'Đặt mua giáo trình'] as $v => $nhan): ?><option value="<?= e($v) ?>" <?= $du['hinh_thuc'] === $v ? 'selected' : '' ?>><?= e($nhan) ?></option><?php endforeach; ?></select><p class="bieu-mau__loi" id="loi-hinh_thuc"><?= e($loi['hinh_thuc'] ?? '') ?></p></div>
<div class="bieu-mau__nhom"><label for="chon_giao_trinh">Giáo trình cần liên hệ (bắt buộc)</label><select class="bieu-mau__o-nhap" id="chon_giao_trinh" name="chon_giao_trinh" required aria-describedby="loi-chon_giao_trinh"><option value="">Chọn giáo trình</option><?php foreach ($kho->tatCa() as $s): ?><option value="<?= e($s->id) ?>" <?= $du['chon_giao_trinh'] === (string) $s->id ? 'selected' : '' ?>><?= e($s->ten) ?></option><?php endforeach; ?></select><p class="bieu-mau__loi" id="loi-chon_giao_trinh"><?= e($loi['chon_giao_trinh'] ?? '') ?></p></div>
<div class="bieu-mau__nhom"><label for="ghi_chu">Ghi chú hoặc yêu cầu bổ sung</label><textarea class="bieu-mau__o-nhap" id="ghi_chu" name="ghi_chu" rows="4" maxlength="2000" aria-describedby="loi-ghi_chu"><?= e($du['ghi_chu']) ?></textarea><p class="bieu-mau__loi" id="loi-ghi_chu"><?= e($loi['ghi_chu'] ?? '') ?></p></div>
<div class="bieu-mau__nhom"><label for="anh">Ảnh đính kèm không bắt buộc</label><input id="anh" name="anh" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="huong-dan-anh loi-anh"><p id="huong-dan-anh">JPG, PNG hoặc WebP, tối đa 2 MB. Nếu biểu mẫu có lỗi, cần chọn lại ảnh.</p><p class="bieu-mau__loi" id="loi-anh"><?= e($loi['anh'] ?? '') ?></p></div>
<div class="bieu-mau__nhom"><label for="cam_ket"><input id="cam_ket" name="cam_ket" type="checkbox" value="1" required <?= $du['cam_ket'] === '1' ? 'checked' : '' ?> aria-describedby="loi-cam_ket"> Tôi xác nhận thông tin và cam kết thực hiện quy định nhận, mượn hoặc mua sách (bắt buộc).</label><p class="bieu-mau__loi" id="loi-cam_ket"><?= e($loi['cam_ket'] ?? '') ?></p></div>
</fieldset><div class="nhom-nut"><button class="nut nut--chinh" type="submit">Gửi phiếu đăng ký</button><a class="nut nut--phu" href="lien-he.php">Nhập lại từ đầu</a></div>
</form></main>
<?php require __DIR__ . '/inc/footer.php'; ?>
