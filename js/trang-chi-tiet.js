/**
 * Đọc id trên URL và tải đúng một giáo trình từ tệp JSON.
 * Trang cập nhật tiêu đề, nội dung chi tiết và trạng thái yêu thích an toàn.
 */
import { taiJSON } from './api.js';
import {
  capNhatNutYeuThich,
  daoTrangThaiYeuThich
} from './yeu-thich.js';

const DUONG_DAN_DU_LIEU = new URL('../data/giao-trinh.json', import.meta.url);
const khungChiTiet = document.querySelector('#chi-tiet');
const noiDungDuPhong = document.querySelector('#chi-tiet-du-phong');
const giaTien = new Intl.NumberFormat('vi-VN', {
  style: 'currency',
  currency: 'VND'
});

function taoMucThongTin(nhan, noiDung) {
  const muc = document.createElement('div');
  const tieuDe = document.createElement('dt');
  const giaTri = document.createElement('dd');
  tieuDe.textContent = nhan;
  giaTri.textContent = noiDung;
  muc.append(tieuDe, giaTri);
  return muc;
}

function hienLoi(noiDung, choThuLai = false) {
  khungChiTiet.replaceChildren();
  khungChiTiet.className = 'trang-thai-du-lieu trang-thai-du-lieu--loi';
  const thongBao = document.createElement('p');
  thongBao.textContent = noiDung;
  khungChiTiet.append(thongBao);

  if (choThuLai) {
    const nut = document.createElement('button');
    nut.type = 'button';
    nut.className = 'nut nut--phu';
    nut.textContent = 'Thử tải lại';
    nut.addEventListener('click', taiChiTiet, { once: true });
    khungChiTiet.append(nut);
  }
}

function hienGiaoTrinh(giaoTrinh) {
  const khuVuc = document.createElement('section');
  const khungAnh = document.createElement('div');
  const hinh = document.createElement('figure');
  const anh = document.createElement('img');
  const chuThich = document.createElement('figcaption');
  const tongQuan = document.createElement('div');
  const nhomHuyHieu = document.createElement('div');
  const huyHieuKhoa = document.createElement('span');
  const huyHieuKho = document.createElement('span');
  const tieuDe = document.createElement('h1');
  const moTa = document.createElement('p');
  const thongTin = document.createElement('dl');
  const khungGia = document.createElement('div');
  const gia = document.createElement('strong');
  const giaGoc = document.createElement('del');
  const nhomNut = document.createElement('div');
  const lienHe = document.createElement('a');
  const nutYeuThich = document.createElement('button');

  khuVuc.className = 'khung-sach-chi-tiet';
  khungAnh.className = 'khung-sach-chi-tiet__anh';
  anh.src = giaoTrinh.anh;
  anh.alt = `Bìa ${giaoTrinh.ten}`;
  anh.width = 260;
  anh.height = 368;
  chuThich.textContent = `${giaoTrinh.ma} – ${giaoTrinh.nhaXuatBan}`;
  hinh.append(anh, chuThich);
  khungAnh.append(hinh);

  tongQuan.className = 'khung-sach-chi-tiet__tong-quan';
  nhomHuyHieu.className = 'nhom-nut';
  huyHieuKhoa.className = 'huy-hieu huy-hieu--sap-het';
  huyHieuKhoa.textContent = giaoTrinh.khoa;
  huyHieuKho.className = giaoTrinh.soLuong > 0
    ? 'huy-hieu huy-hieu--con'
    : 'huy-hieu huy-hieu--het';
  huyHieuKho.textContent = giaoTrinh.soLuong > 0
    ? `Còn ${giaoTrinh.soLuong} bản`
    : 'Tạm hết';
  nhomHuyHieu.append(huyHieuKhoa, huyHieuKho);

  tieuDe.className = 'khung-sach-chi-tiet__tieu-de';
  tieuDe.textContent = giaoTrinh.ten;
  moTa.className = 'doan-van-dai';
  moTa.textContent = giaoTrinh.moTa;
  thongTin.className = 'chi-tiet-dong__thong-tin';
  thongTin.append(
    taoMucThongTin('Mã học liệu', giaoTrinh.ma),
    taoMucThongTin('Tác giả', giaoTrinh.tacGia),
    taoMucThongTin('Nhà xuất bản', `${giaoTrinh.nhaXuatBan}, ${giaoTrinh.namXuatBan}`),
    taoMucThongTin('Số trang', `${giaoTrinh.soTrang} trang`),
    taoMucThongTin('Khoa chuyên môn', giaoTrinh.khoa)
  );

  khungGia.className = 'khung-sach-chi-tiet__gia chi-tiet-dong__gia';
  gia.className = 'khung-sach-chi-tiet__gia-so';
  gia.textContent = giaTien.format(giaoTrinh.gia);
  giaGoc.className = 'khung-sach-chi-tiet__gia-goc';
  giaGoc.textContent = giaTien.format(giaoTrinh.giaGoc);
  khungGia.append(gia, giaGoc);

  nhomNut.className = 'nhom-nut khoang-cach-tren-sm';
  lienHe.className = 'nut nut--chinh';
  lienHe.href = `lien-he.html?giao-trinh=${giaoTrinh.id}`;
  lienHe.textContent = 'Đăng ký mượn / mua';
  nutYeuThich.type = 'button';
  nutYeuThich.className = 'nut nut--phu';
  nutYeuThich.dataset.hanhDong = 'yeu-thich';
  nutYeuThich.dataset.id = String(giaoTrinh.id);
  nhomNut.append(lienHe, nutYeuThich);

  tongQuan.append(nhomHuyHieu, tieuDe, moTa, thongTin, khungGia, nhomNut);
  khuVuc.append(khungAnh, tongQuan);
  khungChiTiet.className = '';
  khungChiTiet.replaceChildren(khuVuc);
  capNhatNutYeuThich(khungChiTiet);
}

async function taiChiTiet() {
  khungChiTiet.className = 'trang-thai-du-lieu trang-thai-du-lieu--dang-tai';
  khungChiTiet.textContent = 'Đang tải thông tin giáo trình…';

  const thamSoId = new URLSearchParams(window.location.search).get('id');
  const id = thamSoId === null || thamSoId === '' ? 1 : Number(thamSoId);

  try {
    const danhSach = await taiJSON(DUONG_DAN_DU_LIEU);
    const giaoTrinh = danhSach.find((muc) => muc.id === id);

    if (!giaoTrinh) {
      noiDungDuPhong?.setAttribute('hidden', '');
      document.title = 'Không tìm thấy giáo trình | UEDBookHub';
      hienLoi('Không tìm thấy giáo trình theo mã trên đường dẫn.');
      return;
    }

    noiDungDuPhong?.setAttribute('hidden', '');
    document.title = `${giaoTrinh.ten} | UEDBookHub`;
    const duongDanHienTai = document.querySelector('.duong-dan__hien-tai');
    if (duongDanHienTai) {
      duongDanHienTai.textContent = giaoTrinh.ten;
    }
    hienGiaoTrinh(giaoTrinh);
  } catch (loi) {
    console.error('Không tải được chi tiết giáo trình.', loi);
    hienLoi('Không tải được dữ liệu. Kiểm tra kết nối rồi thử lại.', true);
  }
}

khungChiTiet?.addEventListener('click', (suKien) => {
  const nut = suKien.target.closest('[data-hanh-dong="yeu-thich"]');
  if (!nut || !khungChiTiet.contains(nut)) {
    return;
  }

  daoTrangThaiYeuThich(Number(nut.dataset.id));
  capNhatNutYeuThich(khungChiTiet);
});

if (khungChiTiet) {
  taiChiTiet();
}
