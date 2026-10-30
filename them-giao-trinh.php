<?php
/** Thêm giáo trình, dùng kiểm tra chung và upload ảnh an toàn.
 * Thử dữ liệu sai để xem lỗi cạnh ô; thành công dùng PRG về quản trị.
 */
require __DIR__ . '/inc/config.php'; require __DIR__ . '/inc/kiem-tra.php'; yeuCauQuyen('quan_tri');
$kho = khoGiaoTrinh(); $danhMuc = $kho->cacDanhMuc(); $ids = array_map('intval', array_column($danhMuc,'id'));
$f = []; $loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST; [$du,$loi] = kiemTraGiaoTrinh($_POST,$ids); $anh = nhanAnhTaiLen($_FILES,$loi);
    if (!$loi) {
        $dich = null;
        if ($anh !== null) { $du['anh']='uploads/'.$anh['ten']; $dich=__DIR__.'/uploads/'.$anh['ten']; if(!move_uploaded_file($anh['tam'],$dich)){throw new RuntimeException('Không lưu được ảnh.');} }
        try { $id=$kho->them($du); flash('Đã thêm giáo trình #'.$id.'.'); chuyenHuong('quan-tri.php'); }
        catch(PDOException $ex){ if($dich && is_file($dich)){unlink($dich);} if((int)($ex->errorInfo[1]??0)===1062){$loi['ma']='Mã giáo trình đã tồn tại.';} else{throw $ex;} }
    }
}
$tieuDe='Thêm giáo trình';$trang='quan-tri';$hanhDong='them-giao-trinh.php';$nutGui='Thêm giáo trình';require __DIR__.'/inc/header.php';
?><main class="noi-dung-chinh"><h1>Thêm giáo trình</h1><?php require __DIR__.'/inc/form-giao-trinh.php'; ?></main><?php require __DIR__.'/inc/footer.php'; ?>
