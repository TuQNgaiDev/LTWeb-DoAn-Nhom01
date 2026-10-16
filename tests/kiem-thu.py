#!/usr/bin/env python3
"""Kiểm thử HTTP 12 ca của Bảng 3. Chỉ chạy trên bản sao tạm, không làm bẩn dữ liệu dự án."""
import base64, http.cookiejar, json, os, re, shutil, socket, subprocess, tempfile, time, urllib.request, urllib.parse, urllib.error
from pathlib import Path
ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT/'tests/ket-qua'; OUT.mkdir(parents=True,exist_ok=True)
class Client:
 def __init__(self, base):
  self.base=base; self.jar=http.cookiejar.CookieJar(); self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
 def req(self,path,data=None,headers=None):
  if isinstance(data,dict): data=urllib.parse.urlencode(data).encode()
  try: r=self.opener.open(urllib.request.Request(self.base+path,data=data,headers=headers or {}))
  except urllib.error.HTTPError as e: r=e
  return r.status,r.read().decode('utf-8',errors='replace'),dict(r.headers)
 def sid(self): return next((c.value for c in self.jar if c.name=='PHPSESSID'),'')
def multipart(fields,name,content):
 b='UEDBoundary2026'; data=b''
 for k,v in fields.items(): data+=f'--{b}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode()
 data+=f'--{b}\r\nContent-Disposition: form-data; name="anh"; filename="{name}"\r\nContent-Type: image/jpeg\r\n\r\n'.encode()+content+f'\r\n--{b}--\r\n'.encode()
 return data,{'Content-Type':f'multipart/form-data; boundary={b}'}
valid={'ho_ten':'Sinh viên kiểm thử','ma_sinh_vien':'0000000000','email_sv':'kiemthu@ued.udn.vn','so_dien_thoai':'0900000000','lop_hoc_phan':'Lớp thử nghiệm','hinh_thuc':'mua','chon_giao_trinh':'1','so_luong':'1','ngay_hen':'2026-10-20','ghi_chu':'Dữ liệu minh họa, không phải đăng ký thật.','cam_ket':'1'}
results=[]; extras={}
def check(n,detail,ok):
 results.append({'tt':n,'thucTe':detail,'dat':bool(ok)})
 print(f'{n}: {"PASS" if ok else "FAIL"} - {detail}')
with tempfile.TemporaryDirectory(prefix='uedbookhub-test-') as tmp:
 dst=Path(tmp)/'site'
 shutil.copytree(ROOT,dst,ignore=shutil.ignore_patterns('.git','output','bao-cao','.DS_Store','kiemtra','ket-qua','lien-he.jsonl','php-error.log'))
 # Keep generated data in the disposable copy.
 for f in (dst/'uploads').glob('*'):
  if f.is_file() and f.name not in ['.gitkeep','.htaccess']: f.unlink()
 log=open(Path(tmp)/'server.log','w')
 p=subprocess.Popen(['php','-d','opcache.enable=0','-d','upload_max_filesize=4M','-d','post_max_size=8M','-S','127.0.0.1:8011'],cwd=dst,stdout=log,stderr=log)
 try:
  time.sleep(.5); c=Client('http://127.0.0.1:8011/')
  count=lambda: len((dst/'storage/lien-he.jsonl').read_text().splitlines()) if (dst/'storage/lien-he.jsonl').exists() else 0
  status,h,_=c.req('lien-he.php',{})
  check(1,'POST rỗng: lỗi từng trường bắt buộc, không lưu.',count()==0 and all(x in h for x in ['Họ tên cần','Email không hợp lệ','Bạn cần xác nhận']))
  status,h,_=c.req('lien-he.php',{**valid,'email_sv':'lan@ued','so_dien_thoai':'abc'})
  check(2,'Sai email và SĐT: đúng hai thông báo, giữ họ tên và lớp.',count()==0 and 'Email không hợp lệ' in h and 'Số điện thoại gồm' in h and h.count('aria-invalid="true"')==2 and valid['ho_ten'] in h and valid['lop_hoc_phan'] in h)
  before=set((dst/'uploads').iterdir()); data,headers=multipart(valid,'gia-mao.jpg',b'<?php phpinfo();')
  _,h,_=c.req('lien-he.php',data,headers)
  check(3,'Tệp PHP giả JPG bị từ chối; uploads không có tệp mới.', 'Chỉ nhận ảnh JPG' in h and before==set((dst/'uploads').iterdir()) and count()==0)
  png=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7yQAAAAASUVORK5CYII=')
  data,headers=multipart(valid,'lon.png',png+b'0'*(2*1024*1024+1)); _,h,_=c.req('lien-he.php',data,headers)
  check(4,'Ảnh trên 2 MB bị chặn; giữ dữ liệu, không Warning.', 'Ảnh không được lớn hơn 2 MB' in h and valid['ho_ten'] in h and 'Warning' not in h and count()==0)
  # Các chuỗi lỗi phải được từ chối ngay tại biểu mẫu, kể cả trường không bắt buộc.
  malformed=[]
  for field in valid:
   for kind,value in [('utf8',b'\xff\xfe'),('nul','du\x00lieu'),('array',None)]:
    data={**valid}
    if kind=='array': data.pop(field); data[field+'[]']='gia tri'
    else: data[field]=value
    status,h,_=c.req('lien-he.php',data)
    assert status==200 and 'Chưa gửi phiếu' in h and count()==0, (field,kind,status)
    assert not re.search(r'(Warning|Fatal error|Deprecated|Notice):',h), (field,kind)
    malformed.append(field+':'+kind)
  for date in ['\x002026-10-20','2026-10-20\x00','2026-02-30','20/10/2026']:
   status,h,_=c.req('lien-he.php',{**valid,'ngay_hen':date})
   assert status==200 and 'Ngày hẹn hợp lệ' in h and count()==0, repr(date)
  data,headers=multipart({**valid,'ghi_chu':'ghi\x00chu'},'anh-hop-le.png',png)
  status,h,_=c.req('lien-he.php',data,headers)
  assert status==200 and 'Dữ liệu không hợp lệ' in h and count()==0 and before==set((dst/'uploads').iterdir())
  extras['malformedContactFields']={'passed':len(malformed),'cases':malformed,'invalidDates':4,'invalidContactDoesNotSaveImage':True}
  print('Bổ sung: PASS - 33 ca UTF-8/NUL/mảng, 4 ngày sai và không lưu ảnh khi phiếu lỗi.')
  data,headers=multipart(valid,'hop-le.png',png); _,h,_=c.req('lien-he.php',data,headers); _,h2,_=c.req('lien-he.php')
  check(5,'Gửi hợp lệ có ảnh: một dòng JSONL; GET lại không lưu trùng, flash chỉ một lần.',count()==1 and 'Đã gửi phiếu đăng ký thành công' in h and 'Đã gửi phiếu đăng ký thành công' not in h2 and len(set((dst/'uploads').iterdir())-before)==1)
  xss='"><script>alert(1)</script>'; _,search,_=c.req('danh-sach.php?q='+urllib.parse.quote(xss)); c.req('lien-he.php',{**valid,'ghi_chu':xss}); _,login,_=c.req('dang-nhap.php',{'ten_dang_nhap':'admin','mat_khau':'UEDDemo2026!'}); _,admin,_=c.req('quan-tri.php')
  check(6,'Tìm kiếm và nội dung quản trị escape chuỗi XSS.', '&lt;script&gt;alert(1)&lt;/script&gt;' in search and xss not in search and '&lt;script&gt;alert(1)&lt;/script&gt;' in admin and xss not in admin)
  bad=[c.req('chi-tiet.php'+q) for q in ['?id=abc','?id=999999','']]
  check(7,'ID chữ, không tồn tại và thiếu ID đều HTTP 404.',all(s==404 and '404 · Không tìm thấy' in h and 'Warning' not in h for s,h,_ in bad))
  b=Client(c.base); invalid=[]
  for id_,sl in [('1','0'),('1','-3'),('1','1000'),('999999','1')]:
   _,h,_=b.req('gio-hang.php',{'hanh_dong':'them','id':id_,'so_luong':sl}); invalid.append('role="alert"' in h and 'Giỏ hàng đang trống' in h)
  check(8,'SL 0, -3, 1000 và ID lạ: báo lỗi, giỏ giữ trống.',all(invalid))
  b.req('gio-hang.php',{'hanh_dong':'them','id':'1','so_luong':'2'}); b.req('gio-hang.php',{'hanh_dong':'them','id':'2','so_luong':'1'}); _,h,_=b.req('gio-hang.php',{'hanh_dong':'cap-nhat','id':'1','so_luong':'3'}); mid='233.000 đ' in h and 'Giỏ hàng (4)' in h
  b.req('gio-hang.php',{'hanh_dong':'xoa','id':'2'}); _,h,_=b.req('gio-hang.php'); _,home,_=b.req('index.php')
  check(9,'Hai mục: tổng 233.000đ sau cập nhật; xóa mục 2 còn 165.000đ và header 3.',mid and '165.000 đ' in h and 'Giỏ hàng (3)' in h and 'Giỏ hàng (3)' in home)
  # Liên kết đặt mua phải mở đúng lựa chọn mua mà không cần JavaScript.
  assert 'href="lien-he.php?hinh-thuc=mua"' in h
  _,h,_=b.req('lien-he.php?hinh-thuc=mua')
  assert '<option value="mua" selected>' in h
  _,h,_=b.req('lien-he.php?hinh-thuc=khong-hop-le')
  assert '<option value="muon" selected>' in h
  _,h,_=b.req('lien-he.php?hinh-thuc[]=mua')
  assert '<option value="muon" selected>' in h and 'Warning:' not in h
  n=count(); _,h,_=b.req('lien-he.php?hinh-thuc=mua',{**valid,'email_sv':'sai'})
  assert '<option value="mua" selected>' in h and count()==n
  ghiChu='Đặt mua giáo trình tiếng Việt 📚'
  _,h,_=b.req('lien-he.php?hinh-thuc=mua',{**valid,'ghi_chu':ghiChu})
  saved=json.loads((dst/'storage/lien-he.jsonl').read_text().splitlines()[-1])
  assert count()==n+1 and saved['hinh_thuc']=='mua' and saved['ghi_chu']==ghiChu
  assert saved['gioHang']==[{'id':1,'ten':'Giáo trình Giáo dục học','soLuong':3,'donGia':55000}]
  extras['cartPurchaseContact']={'defaultPurchase':True,'invalidQueryIgnored':True,'selectionKeptAfterError':True,'snapshotSavedFromSession':True,'validUnicodePreserved':True}
  print('Bổ sung: PASS - từ giỏ mở đúng mua, giữ lựa chọn khi lỗi và lưu đúng giỏ/tiếng Việt.')
  d=Client(c.base); _,h,_=d.req('quan-tri.php'); protected='Đăng nhập quản trị' in h; sid=d.sid(); _,h,_=d.req('dang-nhap.php',{'ten_dang_nhap':'admin','mat_khau':'sai'}); wrong='Sai tên đăng nhập hoặc mật khẩu' in h; _,h,_=d.req('dang-nhap.php',{'ten_dang_nhap':'admin','mat_khau':'UEDDemo2026!'}); rotated=sid!=d.sid(); d.req('dang-xuat.php'); _,out,_=d.req('quan-tri.php')
  check(10,'Chặn khách, báo lỗi chung; đăng nhập đổi SID; đăng xuất không vào lại quản trị.',protected and wrong and rotated and '<h1>Quản trị liên hệ</h1>' in h and 'Đăng nhập quản trị' in out)
  _,h,_=d.req('index.php',headers={'Cookie':'da_xem=abc,-1,999999'})
  check(11,'Cookie abc,-1,999999 bị bỏ qua, trang chủ 200 không Warning.', 'Bạn chưa xem giáo trình nào' in h and 'Warning' not in h)
  normalLog=dst/'logs/php-error.log'
  assert not normalLog.exists() or not re.search(r'PHP (Warning|Notice|Deprecated|Fatal)|ValueError|JsonException',normalLog.read_text())
  cfg=dst/'inc/config.php'; s=cfg.read_text(); cfg.write_text(s.replace("const MOI_TRUONG = 'dev'", "const MOI_TRUONG = 'prod'")); src=dst/'data/giao-trinh.json'; src.rename(src.with_suffix('.bak')); time.sleep(2.1)
  status,h,_=d.req('danh-sach.php'); (OUT/'prod-response.html').write_text(h); err=(dst/'logs/php-error.log').read_text()
  check(12,'Prod thiếu JSON: HTTP 500, không lộ đường dẫn; chi tiết ghi vào log.', status==500 and '500 · Hệ thống tạm gián đoạn' in h and str(dst) not in h and 'Không đọc được tệp dữ liệu' in err)
  cfg.write_text(s); src.with_suffix('.bak').rename(src)
  # Additional malformed arrays and invalid selectors.
  for path,data in [('chi-tiet.php?id[]=1',None),('index.php',None),('danh-sach.php?dm=khong-co&sx=sai&q[]=a',None),('lien-he.php',{'ho_ten[]':'a','email_sv[]':'x'}),('gio-hang.php',{'hanh_dong':'them','id[]':'1','so_luong[]':'2'}),('dang-nhap.php',{'ten_dang_nhap[]':'admin','mat_khau[]':'x'})]:
   _,h,_=d.req(path,data); assert not re.search(r'(Warning|Fatal error|Deprecated|Notice):',h),path
  extras['arrayInputs']='PASS: GET/POST dạng mảng không gây lỗi PHP.'
  b.req('gio-hang.php',{'hanh_dong':'xoa-het'}); _,h,_=b.req('gio-hang.php'); assert 'Giỏ hàng đang trống' in h
  for id_ in [1,2,3,4,5,3]: c.req(f'chi-tiet.php?id={id_}')
  viewed=next(k.value for k in c.jar if k.name=='da_xem'); assert viewed=='3%2C5%2C4%2C2',viewed
  extras['recentCookie']='PASS: 3,5,4,2 sau khi xem 1,2,3,4,5,3; tối đa bốn, không lặp.'
  extras['clearCart']='PASS: xóa hết giỏ.'
  # Experiments C2/C3 on disposable code only, restored immediately.
  sf=dst/'danh-sach.php'; original=sf.read_text(); sf.write_text(original.replace('value="<?= e($q) ?>"','value="<?= $q ?>"')); time.sleep(2.1)
  _,unsafe,_=c.req('danh-sach.php?q='+urllib.parse.quote(xss)); sf.write_text(original)
  extras['C2']={'unsafeScriptInHTML':xss in unsafe,'restored':sf.read_text()==original}
  lf=dst/'lien-he.php'; orig=lf.read_text(); lf.write_text(orig.replace("chuyenHuong('lien-he.php');",'// bỏ chuyển hướng trong bản thử nghiệm'))
  n=count(); c.req('lien-he.php',valid); c.req('lien-he.php',valid)
  extras['C3a']={'withoutPRGLinesAddedForTwoPOSTs':count()-n,'method':'Hai POST cùng nội dung, mô phỏng gửi lại khi tải lại trang.'}; lf.write_text(orig)
  cf=dst/'chi-tiet.php'; orig=cf.read_text(); cf.write_text('\n'+orig)
  # output_buffering is 0 on this server by explicit separate process.
  with open(Path(tmp)/'buffer-server.log','w') as bl:
   bp=subprocess.Popen(['php','-d','output_buffering=0','-S','127.0.0.1:8012'],cwd=dst,stdout=bl,stderr=bl)
   try:
    time.sleep(.4); _,bh,bheaders=Client('http://127.0.0.1:8012/').req('chi-tiet.php?id=1')
    extras['C3b']={'headersAlreadySent':'headers already sent' in bh,'daXemSet':'da_xem=' in str(bheaders)}
   finally: bp.terminate(); bp.wait()
  cf.write_text(orig)
  # Capture expected logs separately; intentional experiments cause warnings only in disposable copy.
  (OUT/'log-thu-nghiem.txt').write_text((dst/'logs/php-error.log').read_text())
 finally: p.terminate(); p.wait(); log.close()
(OUT/'ket-qua-http.json').write_text(json.dumps({'results':results,'extras':extras},ensure_ascii=False,indent=2))
assert all(x['dat'] for x in results), 'Có ca chưa đạt'
