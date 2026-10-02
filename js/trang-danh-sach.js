/**
 * Tải danh sách giáo trình từ JSON, sau đó tìm kiếm, lọc và sắp xếp tức thời.
 * Các nút yêu thích dùng ủy quyền sự kiện trên vùng danh sách chung.
 */
import { taiJSON } from './api.js';
import {
  capNhatNutYeuThich,
  daoTrangThaiYeuThich
} from './yeu-thich.js';

const DUONG_DAN_DU_LIEU = new URL('../data/giao-trinh.json', import.meta.url);
const boLoc = document.querySelector('#bo-loc-giao-trinh');
const oTimKiem = document.querySelector('#tim-kiem-giao-trinh');
const chonKhoa = document.querySelector('#loc-khoa');
const chonSapXep = document.querySelector('#sap-xep-giao-trinh');
const danhSach = document.querySelector('#danh-sach-dong');
const trangThai = document.querySelector('#trang-thai-danh-sach');
let tatCaGiaoTrinh = [];

function chuanHoa(chuoi) {
  return chuoi
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
    .toLocaleLowerCase('vi')
    .trim();
}

function dinhDangTien(soTien) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(soTien);
}

function hienTrangThai(noiDung, loai = '') {
  trangThai.replaceChildren();
  trangThai.className = `trang-thai-du-lieu${loai ? ` trang-thai-du-lieu--${loai}` : ''}`;
  const thongBao = document.createElement('span');
  thongBao.textContent = noiDung;
  trangThai.append(thongBao);
}

function taoDongThongTin(nhan, giaTri) {
  const dong = document.createElement('span');
  const nhanDam = document.createElement('strong');
  nhanDam.textContent = `${nhan}: `;
  dong.append(nhanDam, giaTri);
  return dong;
}

function taoTheGiaoTrinh(giaoTrinh) {
  const baiViet = document.createElement('article');
  const khungAnh = document.createElement('div');
  const anh = document.createElement('img');
  const than = document.createElement('div');
  const tieuDe = document.createElement('h3');
  const lienKetTieuDe = document.createElement('a');
  const moTa = document.createElement('p');
  const thongTin = document.createElement('div');
  const chan = document.createElement('div');
  const nhomGia = document.createElement('div');
  const gia = document.createElement('strong');
  const tonKho = document.createElement('span');
  const nhomNut = document.createElement('div');
  const lienKetChiTiet = document.createElement('a');
  const nutYeuThich = document.createElement('button');

  baiViet.className = 'the';
  khungAnh.className = 'the__anh-khung';
  anh.className = 'the__anh';
  anh.src = giaoTrinh.anh;
  anh.alt = `Bìa ${giaoTrinh.ten}`;
  anh.width = 300;
  anh.height = 420;
  anh.loading = 'lazy';
  khungAnh.append(anh);

  than.className = 'the__than';
  tieuDe.className = 'the__tieu-de';
  lienKetTieuDe.href = `chi-tiet.html?id=${giaoTrinh.id}`;
  lienKetTieuDe.textContent = giaoTrinh.ten;
  tieuDe.append(lienKetTieuDe);

  moTa.className = 'the__mo-ta';
  moTa.textContent = giaoTrinh.moTa;
  thongTin.className = 'the__thong-tin';
  thongTin.append(
    taoDongThongTin('Mã', giaoTrinh.ma),
    taoDongThongTin('Tác giả', giaoTrinh.tacGia),
    taoDongThongTin('Khoa', giaoTrinh.khoa)
  );

  chan.className = 'the__chan the__chan--doc';
  nhomGia.className = 'the__gia-va-kho';
  gia.className = 'gia-giao-trinh';
  gia.textContent = dinhDangTien(giaoTrinh.gia);
  tonKho.className = giaoTrinh.soLuong > 0
    ? 'huy-hieu huy-hieu--con'
    : 'huy-hieu huy-hieu--het';
  tonKho.textContent = giaoTrinh.soLuong > 0
    ? `Còn ${giaoTrinh.soLuong} bản`
    : 'Tạm hết';
  nhomGia.append(gia, tonKho);

  nhomNut.className = 'nhom-nut nhom-nut--the';
  lienKetChiTiet.className = 'nut nut--chinh nut--nho';
  lienKetChiTiet.href = `chi-tiet.html?id=${giaoTrinh.id}`;
  lienKetChiTiet.textContent = 'Chi tiết';
  nutYeuThich.type = 'button';
  nutYeuThich.className = 'nut nut--phu nut--nho';
  nutYeuThich.dataset.hanhDong = 'yeu-thich';
  nutYeuThich.dataset.id = String(giaoTrinh.id);
  nhomNut.append(lienKetChiTiet, nutYeuThich);
  chan.append(nhomGia, nhomNut);
  than.append(tieuDe, moTa, thongTin, chan);
  baiViet.append(khungAnh, than);
  return baiViet;
}

function locVaSapXep() {
  const tuKhoa = chuanHoa(oTimKiem.value);
  const khoa = chonKhoa.value;
  const kieuSapXep = chonSapXep.value;
  const ketQua = tatCaGiaoTrinh.filter((giaoTrinh) => {
    const vanBan = chuanHoa([
      giaoTrinh.ten,
      giaoTrinh.ma,
      giaoTrinh.tacGia,
      giaoTrinh.khoa
    ].join(' '));
    const dungTuKhoa = vanBan.includes(tuKhoa);
    const dungKhoa = khoa === 'tat-ca' || giaoTrinh.khoa === khoa;
    return dungTuKhoa && dungKhoa;
  });

  ketQua.sort((mucA, mucB) => {
    if (kieuSapXep === 'gia-tang') {
      return mucA.gia - mucB.gia;
    }
    if (kieuSapXep === 'gia-giam') {
      return mucB.gia - mucA.gia;
    }
    if (kieuSapXep === 'ten-z-a') {
      return mucB.ten.localeCompare(mucA.ten, 'vi');
    }
    return mucA.ten.localeCompare(mucB.ten, 'vi');
  });

  danhSach.replaceChildren(...ketQua.map(taoTheGiaoTrinh));

  if (ketQua.length === 0) {
    hienTrangThai('Không tìm thấy giáo trình phù hợp. Hãy đổi từ khóa hoặc bộ lọc.', 'rong');
  } else {
    hienTrangThai(`Tìm thấy ${ketQua.length} giáo trình.`);
  }

  capNhatNutYeuThich(danhSach);
}

async function taiDanhSach() {
  hienTrangThai('Đang tải danh sách giáo trình…', 'dang-tai');
  boLoc?.setAttribute('aria-busy', 'true');

  try {
    const duLieu = await taiJSON(DUONG_DAN_DU_LIEU);
    tatCaGiaoTrinh = Array.isArray(duLieu) ? duLieu : [];
    locVaSapXep();
  } catch (loi) {
    console.error('Không tải được dữ liệu giáo trình.', loi);
    hienTrangThai('Không tải được dữ liệu. Kiểm tra kết nối rồi thử lại.', 'loi');
    const nutThuLai = document.createElement('button');
    nutThuLai.type = 'button';
    nutThuLai.className = 'nut nut--phu nut--nho';
    nutThuLai.textContent = 'Thử tải lại';
    nutThuLai.addEventListener('click', taiDanhSach, { once: true });
    trangThai.append(nutThuLai);
  } finally {
    boLoc?.removeAttribute('aria-busy');
  }
}

boLoc?.addEventListener('input', locVaSapXep);
boLoc?.addEventListener('change', locVaSapXep);
danhSach?.addEventListener('click', (suKien) => {
  const nut = suKien.target.closest('[data-hanh-dong="yeu-thich"]');
  if (!nut || !danhSach.contains(nut)) {
    return;
  }

  daoTrangThaiYeuThich(Number(nut.dataset.id));
  capNhatNutYeuThich(danhSach);
});

if (boLoc && oTimKiem && chonKhoa && chonSapXep && danhSach && trangThai) {
  taiDanhSach();
}
