/**
 * Đọc, ghi và thay đổi danh sách giáo trình yêu thích trong localStorage.
 * Module cũng phát sự kiện để số đếm trên đầu trang được cập nhật tức thời.
 */
const KHOA_LUU_TRU = 'uedBookHubYeuThich';

export function docYeuThich() {
  try {
    const duLieu = JSON.parse(localStorage.getItem(KHOA_LUU_TRU) || '[]');
    return Array.isArray(duLieu)
      ? duLieu.filter((id) => Number.isInteger(id) && id > 0)
      : [];
  } catch (loi) {
    console.warn('Không đọc được danh sách yêu thích đã lưu.', loi);
    return [];
  }
}

function ghiYeuThich(danhSach) {
  try {
    localStorage.setItem(KHOA_LUU_TRU, JSON.stringify(danhSach));
    window.dispatchEvent(new CustomEvent('yeu-thich:thay-doi', {
      detail: { danhSach }
    }));
  } catch (loi) {
    console.warn('Không thể lưu danh sách yêu thích trên trình duyệt.', loi);
  }
}

export function laYeuThich(id) {
  return docYeuThich().includes(id);
}

export function daoTrangThaiYeuThich(id) {
  const danhSach = docYeuThich();
  const danhSachMoi = danhSach.includes(id)
    ? danhSach.filter((maMuc) => maMuc !== id)
    : [...danhSach, id];

  ghiYeuThich(danhSachMoi);
  return danhSachMoi.includes(id);
}

export function capNhatNutYeuThich(vung = document) {
  const danhSach = docYeuThich();

  vung.querySelectorAll('[data-hanh-dong="yeu-thich"]').forEach((nut) => {
    const id = Number(nut.dataset.id);
    const dangYeuThich = danhSach.includes(id);
    nut.classList.toggle('nut--da-yeu-thich', dangYeuThich);
    nut.setAttribute('aria-pressed', String(dangYeuThich));
    nut.textContent = dangYeuThich ? 'Bỏ yêu thích' : 'Thêm yêu thích';
  });
}
