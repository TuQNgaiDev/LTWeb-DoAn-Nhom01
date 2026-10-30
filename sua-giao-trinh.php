<?php
/** Sửa giáo trình với dữ liệu cũ điền sẵn và kiểm tra chung.
 * Chỉ quản trị được truy cập; ID không tồn tại trả 404.
 */
require __DIR__ . '/inc/config.php'; require __DIR__ . '/inc/kiem-tra.php'; yeuCauQuyen('quan_tri');
$kho=khoGiaoTrinh();$id=soNguyen($_GET['id']??null);$s=$id!==null?$kho->timTheoId($id):null;
if($s===null){require __DIR__.'/404.php';exit;}
$danhMuc=$kho->cacDanhMuc();$ids=array_map('intval',array_column($danhMuc,'id'));
$cu=['danh_muc_id'=>$s->danhMucId,'ma'=>$s->ma,'ten'=>$s->ten,'tac_gia'=>$s->tacGia,'nam_xuat_ban'=>$s->namXuatBan,'nha_xuat_ban'=>$s->nhaXuatBan,'gia'=>$s->gia,'gia_goc'=>$s->giaGoc,'ton_kho'=>$s->soLuong,'so_trang'=>$s->soTrang,'mo_ta'=>$s->moTa];
$f=$cu;$loi=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $f=$_POST;[$du,$loi]=kiemTraGiaoTrinh($_POST,$ids,$s->anh);$anh=nhanAnhTaiLen($_FILES,$loi);
    if(!$loi){$dich=null;if($anh!==null){$du['anh']='uploads/'.$anh['ten'];$dich=__DIR__.'/uploads/'.$anh['ten'];if(!move_uploaded_file($anh['tam'],$dich)){throw new RuntimeException('Không lưu được ảnh.');}}
        try{$kho->sua($id,$du);flash('Đã cập nhật giáo trình #'.$id.'.');chuyenHuong('quan-tri.php');}catch(PDOException $ex){if($dich&&is_file($dich)){unlink($dich);}if((int)($ex->errorInfo[1]??0)===1062){$loi['ma']='Mã giáo trình đã tồn tại.';}else{throw $ex;}}
    }
}
$tieuDe='Sửa giáo trình';$trang='quan-tri';$hanhDong='sua-giao-trinh.php?id='.$id;$nutGui='Lưu thay đổi';require __DIR__.'/inc/header.php';
?><main class="noi-dung-chinh"><h1>Sửa giáo trình #<?= e($id) ?></h1><?php require __DIR__.'/inc/form-giao-trinh.php'; ?></main><?php require __DIR__.'/inc/footer.php'; ?>
