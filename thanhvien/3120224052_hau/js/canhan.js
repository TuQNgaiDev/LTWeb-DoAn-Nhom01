/**
 * Các tương tác cho trang cá nhân Lê Trung Hậu (MSSV: 3120224052):
 * 1. Hiển thị lời chào theo thời gian (kết hợp đồng bộ với máy chủ PHP).
 * 2. Đếm lượt thích phía máy chủ (hỗ trợ AJAX mượt mà và form POST/PRG khi tắt JS).
 * 3. Tạo câu giới thiệu ngẫu nhiên (chuyển động mượt mà khi bật JS).
 */

// 1. HIỂN THỊ LỜI CHÀO THEO THỜI GIAN
const loiChao = document.querySelector('#loi-chao');

function hienThiLoiChao() {
    if (!loiChao) return;

    const gio = new Date().getHours();

    if (gio >= 5 && gio < 12) {
        loiChao.textContent = '☀️ Buổi sáng rạng rỡ nha! Chúc bạn ngày mới đầy ắp tiếng cười và lượm lặt thêm thật nhiều điều hay ho nè ✨';
    } else if (gio >= 12 && gio < 18) {
        loiChao.textContent = '🌤️ Chiều an lành nhé! Chúc bạn nạp đầy năng lượng và có buổi học thật vui nha ✨';
    } else {
        loiChao.textContent = '🌙 Tối an yên nhé! Đừng quên vừa học vừa thư giãn để nạp lại năng lượng cho bản thân nha ☁️';
    }
}

hienThiLoiChao();

// 2. ĐẾM LƯỢT THÍCH MÁY CHỦ (PROGRESSIVE ENHANCEMENT)
const formThich = document.querySelector('.form-thich');
const nutThich = document.querySelector('#nut-thich');
const soLuotThich = document.querySelector('#so-luot-thich');
const nhanThich = document.querySelector('.nut-thich__nhan');

if (formThich && nutThich && soLuotThich) {
    formThich.addEventListener('submit', async (e) => {
        // Tận dụng AJAX để tăng mượt không tải lại trang khi có JavaScript
        e.preventDefault();

        // Hiệu ứng nhịp tim
        nutThich.classList.add('nut-thich--nhay');
        setTimeout(() => nutThich.classList.remove('nut-thich--nhay'), 450);

        try {
            const formData = new URLSearchParams();
            formData.append('thich_trang', '1');
            formData.append('ajax', '1');

            const res = await fetch('gioithieu.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString(),
            });

            if (res.ok) {
                const data = await res.json();
                if (data && data.thanhCong) {
                    soLuotThich.textContent = data.soLuotThich;
                    if (nhanThich) {
                        nhanThich.textContent = 'Đã thích';
                    }
                    nutThich.classList.add('nut-thich--da-thich');
                }
            } else {
                formThich.submit();
            }
        } catch {
            formThich.submit();
        }
    });
}

// 3. TẠO CÂU GIỚI THIỆU NGẪU NHIÊN
const nutGioiThieu = document.querySelector('#nut-gioi-thieu');
const cauGioiThieu = document.querySelector('#cau-gioi-thieu');

const danhSachGioiThieu = [
    '💻 Tôi yêu thích lập trình và phát triển website.',
    '🎯 Tôi đang cố gắng nâng cao kỹ năng lập trình mỗi ngày.',
    '🌱 Tôi thích khám phá, sáng tạo và thử nghiệm những điều mới.',
    '📚 Tôi luôn tìm kiếm kiến thức mới để hoàn thiện bản thân.',
    '✈️ Tôi thích đi du lịch và khám phá thế giới.',
    '🎧 Tôi thích nghe nhạc để thư giãn và tìm cảm hứng.',
    '🌊 Tôi thích đi dạo ngắm biển và tận hưởng gió trời.',
];

let viTriHienTai = -1;

if (nutGioiThieu && cauGioiThieu) {
    nutGioiThieu.addEventListener('click', (e) => {
        e.preventDefault();

        let viTriMoi = viTriHienTai;
        while (danhSachGioiThieu.length > 1 && viTriMoi === viTriHienTai) {
            viTriMoi = Math.floor(Math.random() * danhSachGioiThieu.length);
        }
        viTriHienTai = viTriMoi;

        // Hiệu ứng mờ dần và hiện câu mới
        cauGioiThieu.style.opacity = '0';
        setTimeout(() => {
            cauGioiThieu.textContent = danhSachGioiThieu[viTriHienTai];
            cauGioiThieu.style.opacity = '1';
        }, 150);
    });
}
