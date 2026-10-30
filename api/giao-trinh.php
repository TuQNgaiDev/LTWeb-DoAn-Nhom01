<?php
/** GET ?q= tìm giáo trình; GET ?id= trả một giáo trình công khai.
 * Thử id sai/không có để nhận JSON 400/404, không có HTML hoặc Warning.
 */
require __DIR__ . '/../inc/config.php'; require __DIR__ . '/../inc/api.php'; dangKyLoiApi();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { header('Allow: GET'); traJson(['loi'=>'Phương thức không được hỗ trợ.'],405); }
$kho=khoGiaoTrinh();
if(array_key_exists('id',$_GET)){
    $id=soNguyen($_GET['id']); if($id===null){traJson(['loi'=>'ID không hợp lệ.'],400);}
    $s=$kho->timTheoId($id); if($s===null){traJson(['loi'=>'Không tìm thấy giáo trình.'],404);}
    traJson(['du_lieu'=>$s]);
}
$q=chuoi($_GET,'q'); if(mb_strlen($q)>150){traJson(['loi'=>'Từ khóa không quá 150 ký tự.'],422);}
$ds=$kho->trang(['q'=>$q,'dm'=>null,'giaToiDa'=>null],'moi',10,0);
traJson(['du_lieu'=>$ds]);
