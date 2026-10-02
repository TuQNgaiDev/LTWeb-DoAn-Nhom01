/**
 * Thêm hai tương tác cho trang cá nhân Trần Văn Tú.
 * Nút giao diện ghi nhớ chế độ sáng/tối; nhóm nút kỹ năng lọc danh sách tức thời.
 * Thử bằng Tab rồi Enter, sau đó tải lại trang để kiểm tra lựa chọn giao diện.
 */
const KHOA_GIAO_DIEN = 'giaoDienTrangTu';
const nutGiaoDien = document.querySelector('#doi-giao-dien');
const boLocKyNang = document.querySelector('#bo-loc-ky-nang');
const danhSachKyNang = document.querySelector('#danh-sach-ky-nang');
const ketQuaLoc = document.querySelector('#ket-qua-loc-ky-nang');

function docGiaoDien() {
  try {
    return localStorage.getItem(KHOA_GIAO_DIEN) === 'toi' ? 'toi' : 'sang';
  } catch (loi) {
    console.warn('Không đọc được lựa chọn giao diện.', loi);
    return 'sang';
  }
}

function apDungGiaoDien(giaoDien) {
  const laToi = giaoDien === 'toi';
  document.documentElement.dataset.giaoDien = giaoDien;
  nutGiaoDien?.setAttribute('aria-pressed', String(laToi));
  if (nutGiaoDien) {
    nutGiaoDien.textContent = laToi ? '☀️ Dùng giao diện sáng' : '🌙 Dùng giao diện tối';
  }
}

if (nutGiaoDien) {
  apDungGiaoDien(docGiaoDien());
  nutGiaoDien.addEventListener('click', () => {
    const giaoDienMoi = document.documentElement.dataset.giaoDien === 'toi' ? 'sang' : 'toi';
    apDungGiaoDien(giaoDienMoi);
    try {
      localStorage.setItem(KHOA_GIAO_DIEN, giaoDienMoi);
    } catch (loi) {
      console.warn('Không lưu được lựa chọn giao diện.', loi);
    }
  });
}

boLocKyNang?.addEventListener('click', (suKien) => {
  const nutLoc = suKien.target.closest('[data-loc-ky-nang]');
  if (!nutLoc || !boLocKyNang.contains(nutLoc)) {
    return;
  }

  const nhom = nutLoc.dataset.locKyNang;
  const cacKyNang = [...danhSachKyNang.querySelectorAll('[data-nhom-ky-nang]')];
  let soLuongHienThi = 0;

  cacKyNang.forEach((kyNang) => {
    const hienThi = nhom === 'tat-ca' || kyNang.dataset.nhomKyNang === nhom;
    kyNang.classList.toggle('the-ky-nang--an', !hienThi);
    if (hienThi) {
      soLuongHienThi += 1;
    }
  });

  boLocKyNang.querySelectorAll('[data-loc-ky-nang]').forEach((nut) => {
    const dangChon = nut === nutLoc;
    nut.classList.toggle('nut--chinh', dangChon);
    nut.classList.toggle('nut--phu', !dangChon);
    nut.setAttribute('aria-pressed', String(dangChon));
  });
  ketQuaLoc.textContent = `Đang hiển thị ${soLuongHienThi} kỹ năng.`;
});
