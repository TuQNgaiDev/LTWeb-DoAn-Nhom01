/**
 * Nâng cấp thanh điều hướng thành menu thu gọn trên điện thoại.
 * Đồng thời hiển thị số giáo trình yêu thích đã lưu trên mọi trang.
 */
import { docYeuThich } from './yeu-thich.js';

document.documentElement.classList.add('js');

const thanhDieuHuong = document.querySelector('.thanh-dieu-huong');
const danhSachMenu = thanhDieuHuong?.querySelector('.thanh-dieu-huong__danh-sach');

if (thanhDieuHuong && danhSachMenu) {
  if (!danhSachMenu.id) {
    danhSachMenu.id = 'menu-chinh';
  }

  const nutMenu = document.createElement('button');
  nutMenu.type = 'button';
  nutMenu.className = 'nut-menu';
  nutMenu.setAttribute('aria-controls', danhSachMenu.id);
  nutMenu.setAttribute('aria-expanded', 'false');
  nutMenu.textContent = '☰ Menu';
  thanhDieuHuong.insertBefore(nutMenu, danhSachMenu);

  const dongMenu = (traTieuDiem = false) => {
    danhSachMenu.classList.remove('thanh-dieu-huong__danh-sach--mo');
    nutMenu.setAttribute('aria-expanded', 'false');
    if (traTieuDiem) {
      nutMenu.focus();
    }
  };

  nutMenu.addEventListener('click', () => {
    const daMo = danhSachMenu.classList.toggle('thanh-dieu-huong__danh-sach--mo');
    nutMenu.setAttribute('aria-expanded', String(daMo));
  });

  document.addEventListener('keydown', (suKien) => {
    if (suKien.key === 'Escape' && nutMenu.getAttribute('aria-expanded') === 'true') {
      dongMenu(true);
    }
  });

  window.addEventListener('resize', () => {
    if (window.matchMedia('(min-width: 768px)').matches) {
      dongMenu(false);
    }
  });

  const lienKetDanhSach = danhSachMenu.querySelector('a[href$="danh-sach.html"]');
  const mucYeuThich = document.createElement('li');
  const lienKetYeuThich = document.createElement('a');
  const soYeuThich = document.createElement('span');

  mucYeuThich.className = 'thanh-dieu-huong__muc thanh-dieu-huong__muc--yeu-thich';
  lienKetYeuThich.className = 'thanh-dieu-huong__lien-ket';
  lienKetYeuThich.href = `${lienKetDanhSach?.getAttribute('href') || 'danh-sach.html'}#danh-sach-dong`;
  lienKetYeuThich.append('Yêu thích ');
  soYeuThich.className = 'dem-yeu-thich';
  soYeuThich.setAttribute('data-so-yeu-thich', '');
  soYeuThich.setAttribute('aria-label', 'Số giáo trình yêu thích');
  lienKetYeuThich.append(soYeuThich);
  mucYeuThich.append(lienKetYeuThich);
  danhSachMenu.append(mucYeuThich);

  const capNhatSoDem = () => {
    soYeuThich.textContent = String(docYeuThich().length);
  };

  capNhatSoDem();
  window.addEventListener('yeu-thich:thay-doi', capNhatSoDem);
  window.addEventListener('storage', capNhatSoDem);
}
