<?php
/** Xóa giáo trình chỉ bằng POST; bắt lỗi khóa ngoại khi đã có trong đơn.
 * GET nhận 405 và không thay đổi dữ liệu.
 */
require __DIR__ . '/inc/config.php'; yeuCauQuyen('quan_tri');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');echo 'Chỉ chấp nhận POST.';exit;}
$id=soNguyen($_POST['id']??null);
if($id===null){flash('ID giáo trình không hợp lệ.');chuyenHuong('quan-tri.php');}
try{khoGiaoTrinh()->xoa($id);flash('Đã xóa giáo trình #'.$id.'.');}
catch(PDOException $ex){if((int)($ex->errorInfo[1]??0)===1451){flash('Không thể xóa giáo trình đã có trong đơn hàng hoặc liên hệ.');}else{throw $ex;}}
chuyenHuong('quan-tri.php');
