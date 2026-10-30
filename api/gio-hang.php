<?php
/** GET xem giỏ; POST JSON {id, sl} thêm giáo trình vào giỏ session.
 * Kiểm tra phương thức, JSON, kiểu, ID và tồn kho với mã HTTP phù hợp.
 */
require __DIR__ . '/../inc/config.php'; require __DIR__ . '/../inc/api.php'; dangKyLoiApi();
$phuongThuc=$_SERVER['REQUEST_METHOD'];
if(!in_array($phuongThuc,['GET','POST'],true)){header('Allow: GET, POST');traJson(['loi'=>'Phương thức không được hỗ trợ.'],405);}
$kho=khoGiaoTrinh();$gio=new App\Services\GioHang();
if($phuongThuc==='GET'){
    $muc=array_map(fn(array $x):array=>['giao_trinh'=>$x['giaoTrinh'],'so_luong'=>$x['soLuong'],'thanh_tien'=>$x['thanhTien']],$gio->chiTiet($kho));
    traJson(['du_lieu'=>$muc,'so_mon'=>$gio->soMon(),'tong_tien'=>$gio->tongTien($kho)]);
}
$than=file_get_contents('php://input');
try{$du=json_decode($than===false?'':$than,true,512,JSON_THROW_ON_ERROR);}catch(JsonException){traJson(['loi'=>'Thân yêu cầu phải là JSON hợp lệ.'],400);}
if(!is_array($du)||array_is_list($du)){traJson(['loi'=>'Thân JSON phải là một đối tượng.'],400);}
$id=soNguyen($du['id']??null);$sl=soNguyen($du['sl']??null,1,99);
if($id===null||$sl===null){traJson(['loi'=>'ID và số lượng phải là số nguyên dương hợp lệ.'],422);}
if($kho->timTheoId($id)===null){traJson(['loi'=>'Không tìm thấy giáo trình.'],404);}
try{$gio->them($id,$sl,$kho);}catch(InvalidArgumentException $ex){traJson(['loi'=>$ex->getMessage()],422);}
traJson(['thong_bao'=>'Đã thêm vào giỏ.','so_mon'=>$gio->soMon()]);
