/**
 * Tệp: canhan.js - Xử lý tương tác trên trang cá nhân
 * Bhling Trường (MSSV: 3120124040)
 * Chức năng:
 *  1. Chuyển đổi giao diện Sáng / Tối và lưu trạng thái vào localStorage.
 *  2. Sao chép địa chỉ Email vào bộ nhớ tạm (Clipboard API) kèm thông báo.
 * Cách thử: Nhấp nút 'Đổi giao diện' (hoặc Tab + Enter). Nhấp nút 'Sao chép Email'.
 */

// ========================================================
// 1. TƯƠNG TÁC 1: ĐỔI GIAO DIỆN SÁNG / TỐI (THEME TOGGLE)
// ========================================================
const nutDoiGiaoDien = document.querySelector('#nut-doi-giao-dien');
const KHOA_THEME = 'theme_3120124040_truong';

// Khởi tạo: Đọc tùy chọn từ localStorage khi vừa mở trang
const themeDaLuu = localStorage.getItem(KHOA_THEME);
if (themeDaLuu === 'toi') {
  document.body.classList.add('che-do-toi');
  if (nutDoiGiaoDien) {
    nutDoiGiaoDien.textContent = '☀️ Chế độ sáng';
    nutDoiGiaoDien.setAttribute('aria-pressed', 'true');
  }
}

// Bắt sự kiện click trên nút đổi theme
if (nutDoiGiaoDien) {
  nutDoiGiaoDien.addEventListener('click', () => {
    const laToi = document.body.classList.toggle('che-do-toi');
    nutDoiGiaoDien.textContent = laToi ? '☀️ Chế độ sáng' : '🌓 Chế độ tối';
    nutDoiGiaoDien.setAttribute('aria-pressed', String(laToi));
    localStorage.setItem(KHOA_THEME, laToi ? 'toi' : 'sang');
  });
}

// ========================================================
// 2. TƯƠNG TÁC 2: SAO CHÉP EMAIL VÀO CLIPBOARD
// ========================================================
const nutChepEmail = document.querySelector('#nut-chep-email');
const emailVanBan = document.querySelector('#email-ca-nhan');
const thongBaoChep = document.querySelector('#thong-bao-chep');

if (nutChepEmail && emailVanBan && thongBaoChep) {
  nutChepEmail.addEventListener('click', async () => {
    const emailCanChep = emailVanBan.textContent.trim();
    try {
      // Dùng Clipboard API theo đúng gợi ý của thầy
      await navigator.clipboard.writeText(emailCanChep);
      
      // Hiển thị phản hồi an toàn bằng textContent (tránh XSS)
      thongBaoChep.textContent = '✓ Đã sao chép vào bộ nhớ tạm!';
      thongBaoChep.classList.add('hien');

      // Tự động ẩn thông báo sau 2.5 giây
      setTimeout(() => {
        thongBaoChep.classList.remove('hien');
        thongBaoChep.textContent = '';
      }, 2500);
    } catch (loi) {
      console.error('Không thể sao chép email:', loi);
      thongBaoChep.textContent = 'Sao chép thất bại!';
      thongBaoChep.classList.add('hien');
    }
  });
}