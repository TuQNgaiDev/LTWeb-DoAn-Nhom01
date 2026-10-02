/**
 * Tải thời tiết hiện tại tại Đà Nẵng từ REST API Open-Meteo.
 * Chỉ các trường cần thiết được đưa vào DOM bằng textContent/createElement.
 */
import { taiJSON } from './api.js';

const khungThoiTiet = document.querySelector('#thoi-tiet-da-nang');
const noiDungThoiTiet = document.querySelector('#noi-dung-thoi-tiet');

const moTaMaThoiTiet = {
  0: 'Trời quang',
  1: 'Chủ yếu quang',
  2: 'Có mây rải rác',
  3: 'Nhiều mây',
  45: 'Có sương mù',
  48: 'Sương mù đóng băng',
  51: 'Mưa phùn nhẹ',
  53: 'Mưa phùn vừa',
  55: 'Mưa phùn dày',
  61: 'Mưa nhẹ',
  63: 'Mưa vừa',
  65: 'Mưa to',
  80: 'Mưa rào nhẹ',
  81: 'Mưa rào vừa',
  82: 'Mưa rào mạnh',
  95: 'Có dông'
};

function taoChiSo(nhan, giaTri) {
  const muc = document.createElement('div');
  const tieuDe = document.createElement('span');
  const soLieu = document.createElement('strong');
  muc.className = 'thoi-tiet__chi-so';
  tieuDe.textContent = nhan;
  soLieu.textContent = giaTri;
  muc.append(tieuDe, soLieu);
  return muc;
}

function hienLoi() {
  noiDungThoiTiet.replaceChildren();
  const thongBao = document.createElement('p');
  const nutThuLai = document.createElement('button');
  thongBao.className = 'trang-thai-du-lieu trang-thai-du-lieu--loi';
  thongBao.textContent = 'Không tải được thời tiết. Kiểm tra kết nối rồi thử lại.';
  nutThuLai.type = 'button';
  nutThuLai.className = 'nut nut--phu';
  nutThuLai.textContent = 'Thử tải lại';
  nutThuLai.addEventListener('click', taiThoiTiet, { once: true });
  noiDungThoiTiet.append(thongBao, nutThuLai);
}

async function taiThoiTiet() {
  noiDungThoiTiet.className = 'trang-thai-du-lieu trang-thai-du-lieu--dang-tai';
  noiDungThoiTiet.textContent = 'Đang tải thời tiết Đà Nẵng…';

  const thamSo = new URLSearchParams({
    latitude: '16.0544',
    longitude: '108.2022',
    current: 'temperature_2m,apparent_temperature,weather_code,wind_speed_10m',
    timezone: 'Asia/Bangkok'
  });

  try {
    const duLieu = await taiJSON(`https://api.open-meteo.com/v1/forecast?${thamSo}`);
    const hienTai = duLieu.current;
    const donVi = duLieu.current_units;
    const moTa = moTaMaThoiTiet[hienTai.weather_code] || 'Điều kiện thời tiết khác';
    const capNhat = document.createElement('p');
    const cacChiSo = document.createElement('div');

    capNhat.className = 'thoi-tiet__cap-nhat';
    capNhat.textContent = `Cập nhật lúc ${hienTai.time.replace('T', ' ')} (giờ địa phương).`;
    cacChiSo.className = 'thoi-tiet__luoi';
    cacChiSo.append(
      taoChiSo('Nhiệt độ', `${hienTai.temperature_2m} ${donVi.temperature_2m}`),
      taoChiSo('Cảm giác như', `${hienTai.apparent_temperature} ${donVi.apparent_temperature}`),
      taoChiSo('Trạng thái', moTa),
      taoChiSo('Gió', `${hienTai.wind_speed_10m} ${donVi.wind_speed_10m}`)
    );
    noiDungThoiTiet.className = '';
    noiDungThoiTiet.replaceChildren(cacChiSo, capNhat);
  } catch (loi) {
    console.error('Không tải được thời tiết Đà Nẵng.', loi);
    hienLoi();
  }
}

if (khungThoiTiet && noiDungThoiTiet) {
  taiThoiTiet();
}
