/**
 * Tệp: canhan.js - Xử lý tương tác trên trang cá nhân
 * Bhling Trường (MSSV: 3120124040)
 * Chức năng:
 *  1. Đồng hồ đếm ngược thời gian thực đến ngày thi cuối kỳ (Date, setInterval, textContent).
 *  2. Sao chép địa chỉ Email vào bộ nhớ tạm (Clipboard API) kèm thông báo.
 * Cách thử: 
 *  - Quan sát bộ đếm thời gian lùi từng giây ở khối đếm ngược ngày thi.
 *  - Nhấp nút 'Sao chép Email' (hoặc Tab + Enter) để copy và xem thông báo xác nhận.
 */

//ĐỒNG HỒ ĐẾM NGƯỢC THI CUỐI KỲ
const phanTuDemNguoc = document.querySelector('#dong-ho-dem-nguoc');

if (phanTuDemNguoc) {
  // Mốc thi giả định cuối kỳ: 8:00 sáng ngày 25/12/2026
  const thoiDiemThi = new Date('2026-12-25T08:00:00').getTime();

  function capNhatDemNguoc() {
    const bayGio = new Date().getTime();
    const khoangCach = thoiDiemThi - bayGio;

    if (khoangCach <= 0) {
      phanTuDemNguoc.textContent = 'Đã đến giờ thi cuối kỳ!';
      return;
    }

    const ngay = Math.floor(khoangCach / (1000 * 60 * 60 * 24));
    const gio = Math.floor((khoangCach % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const phut = Math.floor((khoangCach % (1000 * 60 * 60)) / (1000 * 60));
    const giay = Math.floor((khoangCach % (1000 * 60)) / 1000);

    phanTuDemNguoc.textContent = `${ngay} ngày ${gio} giờ ${phut} phút ${giay} giây`;
  }

  // Chạy ngay lần đầu để tránh độ trễ 1 giây rồi lặp mỗi 1000ms
  capNhatDemNguoc();
  setInterval(capNhatDemNguoc, 1000);
}

//SAO CHÉP EMAIL VÀO CLIPBOARD (GIỮ NGUYÊN)
const nutChepEmail = document.querySelector('#nut-chep-email');
const emailVanBan = document.querySelector('#email-ca-nhan');
const thongBaoChep = document.querySelector('#thong-bao-chep');

if (nutChepEmail && emailVanBan && thongBaoChep) {
  nutChepEmail.addEventListener('click', async () => {
    const emailCanChep = emailVanBan.textContent.trim();
    try {
      // Dùng Clipboard API theo đúng gợi ý của đề bài
      await navigator.clipboard.writeText(emailCanChep);
      
      // Hiển thị phản hồi an toàn bằng textContent
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