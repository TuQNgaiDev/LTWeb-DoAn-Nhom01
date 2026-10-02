/**
 * Kiểm tra từng trường của biểu mẫu khi rời ô và khi gửi.
 * Dữ liệu hợp lệ được POST tới API giả lập mà không tải lại trang.
 */
import { taiJSON } from './api.js';

const bieuMau = document.querySelector('#bieu-mau-lien-he');
const thongBao = document.querySelector('#ket-qua-lien-he');
const nutGui = bieuMau?.querySelector('button[type="submit"]');
const cacTruong = bieuMau
  ? [...bieuMau.querySelectorAll('input, select, textarea')]
      .filter((truong) => truong.type !== 'hidden' && truong.type !== 'radio')
  : [];

function noiDungLoi(truong) {
  const giaTri = typeof truong.value === 'string' ? truong.value.trim() : '';
  truong.setCustomValidity('');

  if (truong.required && giaTri === '' && truong.type !== 'checkbox') {
    truong.setCustomValidity('Vui lòng điền hoặc chọn thông tin này.');
  }

  if (truong.id === 'ho-ten' && giaTri !== '' && giaTri.length < 3) {
    truong.setCustomValidity('Họ và tên cần có ít nhất 3 ký tự.');
  }

  if (truong.id === 'email-sv' && giaTri !== '' && !giaTri.toLowerCase().endsWith('@ued.udn.vn')) {
    truong.setCustomValidity('Vui lòng dùng email sinh viên có tên miền @ued.udn.vn.');
  }

  if (truong.validity.valid) {
    return '';
  }
  if (truong.validity.valueMissing) {
    return 'Vui lòng điền hoặc chọn thông tin này.';
  }
  if (truong.validity.typeMismatch) {
    return 'Định dạng thông tin chưa hợp lệ.';
  }
  if (truong.validity.patternMismatch) {
    return truong.title || 'Thông tin chưa đúng định dạng yêu cầu.';
  }
  if (truong.validity.rangeUnderflow || truong.validity.rangeOverflow) {
    return `Giá trị cần nằm trong khoảng từ ${truong.min} đến ${truong.max}.`;
  }
  return truong.validationMessage || 'Thông tin chưa hợp lệ.';
}

function hienLoiTruong(truong) {
  const loi = noiDungLoi(truong);
  const vungLoi = document.querySelector(`#loi-${truong.id}`);
  truong.classList.toggle('bieu-mau__o-nhap--loi', loi !== '');
  truong.setAttribute('aria-invalid', String(loi !== ''));
  if (vungLoi) {
    vungLoi.textContent = loi;
  }
  return loi === '';
}

function taoVungBaoLoi() {
  cacTruong.forEach((truong) => {
    if (!truong.id || document.querySelector(`#loi-${truong.id}`)) {
      return;
    }
    const vungLoi = document.createElement('p');
    vungLoi.id = `loi-${truong.id}`;
    vungLoi.className = 'bieu-mau__loi';
    vungLoi.setAttribute('aria-live', 'polite');
    truong.setAttribute('aria-describedby', vungLoi.id);
    truong.closest('.bieu-mau__nhom')?.append(vungLoi);
  });
}

function ganGiaoTrinhTuUrl() {
  const id = new URLSearchParams(window.location.search).get('giao-trinh');
  const oChon = document.querySelector('#chon-giao-trinh');
  if (id && oChon?.querySelector(`option[value="${id}"]`)) {
    oChon.value = id;
  }
}

function hienKetQua(noiDung, loai = '') {
  thongBao.className = `thong-bao-bieu-mau${loai ? ` thong-bao-bieu-mau--${loai}` : ''}`;
  thongBao.textContent = noiDung;
}

if (bieuMau && thongBao && nutGui) {
  taoVungBaoLoi();
  ganGiaoTrinhTuUrl();

  bieuMau.addEventListener('focusout', (suKien) => {
    const truong = suKien.target;
    if (cacTruong.includes(truong)) {
      hienLoiTruong(truong);
    }
  });

  bieuMau.addEventListener('input', (suKien) => {
    const truong = suKien.target;
    if (cacTruong.includes(truong) && truong.getAttribute('aria-invalid') === 'true') {
      hienLoiTruong(truong);
    }
  });

  bieuMau.addEventListener('reset', () => {
    window.setTimeout(() => {
      cacTruong.forEach((truong) => {
        truong.setCustomValidity('');
        truong.classList.remove('bieu-mau__o-nhap--loi');
        truong.setAttribute('aria-invalid', 'false');
        const vungLoi = document.querySelector(`#loi-${truong.id}`);
        if (vungLoi) {
          vungLoi.textContent = '';
        }
      });
    });
  });

  bieuMau.addEventListener('submit', async (suKien) => {
    suKien.preventDefault();
    const hopLe = cacTruong.map(hienLoiTruong).every(Boolean);

    if (!hopLe) {
      hienKetQua('Biểu mẫu còn thông tin chưa hợp lệ. Vui lòng kiểm tra các lỗi bên dưới.', 'loi');
      bieuMau.querySelector('[aria-invalid="true"]')?.focus();
      return;
    }

    nutGui.disabled = true;
    nutGui.textContent = 'Đang gửi…';
    hienKetQua('Đang gửi phiếu đăng ký…', 'dang-tai');

    try {
      const duLieuGui = Object.fromEntries(new FormData(bieuMau).entries());
      const ketQua = await taiJSON(bieuMau.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=UTF-8' },
        body: JSON.stringify(duLieuGui)
      });
      hienKetQua(`Gửi phiếu thành công. Mã tiếp nhận giả lập: ${ketQua.id}.`, 'thanh-cong');
      bieuMau.reset();
    } catch (loi) {
      console.error('Không gửi được phiếu đăng ký.', loi);
      hienKetQua('Không gửi được phiếu. Kiểm tra kết nối và bấm “Gửi phiếu đăng ký” để thử lại.', 'loi');
    } finally {
      nutGui.disabled = false;
      nutGui.textContent = 'Gửi phiếu đăng ký';
    }
  });
}
