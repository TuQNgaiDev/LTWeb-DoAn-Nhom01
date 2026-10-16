/**
 * Tăng cường trải nghiệm giao diện Sáng / Tối cho trang cá nhân Trần Văn Tú.
 * Chức năng chính được xử lý hoàn toàn phía máy chủ PHP qua Cookie (hoạt động tốt khi tắt JS).
 * Script này hỗ trợ chuyển đổi tức thì trên giao diện và đồng bộ Cookie 'giao_dien_tu' với máy chủ.
 * Thử nghiệm: Thao tác bằng chuột hoặc bàn phím (Tab + Enter), tải lại trang để kiểm tra trạng thái lưu.
 */
const nutGiaoDien = document.querySelector('#doi-giao-dien');
const formGiaoDien = document.querySelector('.form-doi-giao-dien');

function docCookie(ten) {
  const cacCookie = document.cookie.split(';');
  for (const cookie of cacCookie) {
    const [k, v] = cookie.trim().split('=');
    if (k === ten) {
      return decodeURIComponent(v);
    }
  }
  return null;
}

function ghiCookie(ten, giaTri, ngayHetHan = 30) {
  const d = new Date();
  d.setTime(d.getTime() + ngayHetHan * 24 * 60 * 60 * 1000);
  document.cookie = `${ten}=${encodeURIComponent(giaTri)};expires=${d.toUTCString()};path=/;SameSite=Lax`;
}

if (nutGiaoDien && formGiaoDien) {
  nutGiaoDien.addEventListener('click', (e) => {
    // Tăng cường: đổi giao diện tức thì không cần đợi tải lại trang
    e.preventDefault();
    const html = document.documentElement;
    const hienTai = html.getAttribute('data-giao-dien') || 'sang';
    const moi = hienTai === 'toi' ? 'sang' : 'toi';

    html.setAttribute('data-giao-dien', moi);
    nutGiaoDien.setAttribute('aria-pressed', String(moi === 'toi'));
    nutGiaoDien.textContent = moi === 'toi' ? '☀️ Dùng giao diện sáng' : '🌙 Dùng giao diện tối';

    ghiCookie('giao_dien_tu', moi);
  });
}
