/**
 * Các tương tác cho trang cá nhân Lê Trung Hậu (MSSV: 3120224052):
 * 1. Đồng hồ thời gian thực và tự động cập nhật câu chào từ CSDL MySQL (bảng tv_hau_cau_chao).
 * 2. Đổi câu chào ngẫu nhiên trong khung giờ từ CSDL (hỗ trợ AJAX và PHP fallback).
 * 3. Thả tim & Đếm lượt thích thời gian thực lưu vào CSDL MySQL (bảng tv_hau_luot_thich).
 * 4. Tạo câu giới thiệu ngẫu nhiên.
 */

// 1. ĐỒNG HỒ THỜI GIAN THỰC & CẬP NHẬT CÂU CHÀO TỪ CSDL
const dongHoChuoi = document.querySelector('#dong-ho-chuoi');
const loiChao = document.querySelector('#loi-chao');
const huyHieuKhungGio = document.querySelector('#huy-hieu-khung-gio');
let gioDaGhiNhan = new Date().getHours();

function layTenBuoi(gio) {
    if (gio >= 5 && gio < 12) return 'Buổi sáng';
    if (gio >= 12 && gio < 18) return 'Buổi chiều';
    if (gio >= 18 && gio < 23) return 'Buổi tối';
    return 'Đêm khuya';
}

function capNhatDongHoThoiGianThuc() {
    const bayGio = new Date();
    const gio = bayGio.getHours();
    const phut = String(bayGio.getMinutes()).padStart(2, '0');
    const giay = String(bayGio.getSeconds()).padStart(2, '0');
    const gioDinhDang = String(gio).padStart(2, '0');

    if (dongHoChuoi) {
        dongHoChuoi.textContent = `${gioDinhDang}:${phut}:${giay} - ${layTenBuoi(gio)} (Thời gian thực)`;
    }

    // Nếu bước sang khung giờ mới, tự động đồng bộ lại câu chào từ CSDL
    if (gio !== gioDaGhiNhan) {
        gioDaGhiNhan = gio;
        taiCauChaoTuCSDL(gio);
    }
}

// Chạy đồng hồ mỗi giây
setInterval(capNhatDongHoThoiGianThuc, 1000);
capNhatDongHoThoiGianThuc();

// 2. TẢI VÀ ĐỔI CÂU CHÀO TỪ CSDL MYSQL (AJAX)
async function taiCauChaoTuCSDL(gioChiDinh) {
    if (!loiChao) return;

    try {
        const gio = typeof gioChiDinh === 'number' ? gioChiDinh : new Date().getHours();
        const res = await fetch(`gioithieu.php?action=lay_cau_chao&gio=${gio}`);
        if (!res.ok) return;

        const data = await res.json();
        if (data && data.thanhCong && data.loiChao) {
            loiChao.style.opacity = '0';
            loiChao.style.transform = 'translateY(4px)';

            setTimeout(() => {
                loiChao.textContent = data.loiChao;
                loiChao.style.opacity = '1';
                loiChao.style.transform = 'translateY(0)';

                if (huyHieuKhungGio && data.gioBatDau !== undefined && data.gioKetThuc !== undefined) {
                    huyHieuKhungGio.textContent = `Khung giờ: ${data.gioBatDau}h - ${data.gioKetThuc}h`;
                }

                // Cập nhật dòng đang áp dụng trong bảng tra cứu CSDL (nếu có)
                capNhatDongBangCSDL(gio);
            }, 180);
        }
    } catch (err) {
        console.warn('Không thể tải câu chào thời gian thực từ CSDL:', err);
    }
}

function capNhatDongBangCSDL(gio) {
    const cacDong = document.querySelectorAll('.bang-csdl tbody tr');
    cacDong.forEach((tr) => {
        tr.classList.remove('dong-hien-tai');
        const huyHieu = tr.querySelector('.huy-hieu-dang-chay');
        if (huyHieu) huyHieu.remove();
    });

    // Tìm dòng tương ứng với giờ hiện tại
    cacDong.forEach((tr) => {
        const tdKhung = tr.querySelector('td:nth-child(2)');
        if (!tdKhung) return;
        const text = tdKhung.textContent || '';
        const match = text.match(/(\d+)h\s*-\s*(\d+)h/);
        if (match) {
            const start = parseInt(match[1], 10);
            const end = parseInt(match[2], 10);
            const laKhung = (start <= end)
                ? (gio >= start && gio <= end)
                : (gio >= start || gio <= end);
            if (laKhung) {
                tr.classList.add('dong-hien-tai');
                const tdDau = tr.querySelector('td:first-child');
                if (tdDau && !tdDau.querySelector('.huy-hieu-dang-chay')) {
                    const tag = document.createElement('span');
                    tag.className = 'huy-hieu-dang-chay';
                    tag.textContent = 'Đang áp dụng';
                    tag.title = 'Khung giờ hiện tại';
                    tdDau.appendChild(tag);
                }
            }
        }
    });
}

// Nút Đổi câu chào CSDL
const nutDoiCauChao = document.querySelector('#nut-doi-cau-chao');
if (nutDoiCauChao) {
    nutDoiCauChao.addEventListener('click', (e) => {
        e.preventDefault();
        const icon = nutDoiCauChao.querySelector('.nut-doi-cau-chao__bieu-tuong');
        if (icon) {
            icon.classList.add('xoay-vong');
            setTimeout(() => icon.classList.remove('xoay-vong'), 500);
        }
        taiCauChaoTuCSDL(new Date().getHours());
    });
}

// 3. ĐẾM LƯỢT THÍCH MÁY CHỦ MYSQL (PROGRESSIVE ENHANCEMENT)
const formThich = document.querySelector('.form-thich');
const nutThich = document.querySelector('#nut-thich');
const soLuotThich = document.querySelector('#so-luot-thich');
const nhanThich = document.querySelector('.nut-thich__nhan');

if (formThich && nutThich && soLuotThich) {
    formThich.addEventListener('submit', async (e) => {
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

                    // Hiện thông báo cảm ơn mượt mà
                    hienThongBaoCamOn('❤️ Cảm ơn bạn đã thả tim! Lượt thích đã được lưu an toàn vào CSDL MySQL.');
                }
            } else {
                formThich.submit();
            }
        } catch {
            formThich.submit();
        }
    });
}

function hienThongBaoCamOn(tinNhan) {
    let tb = document.querySelector('.thong-bao-thich');
    if (!tb) {
        tb = document.createElement('p');
        tb.className = 'thong-bao-thich';
        tb.setAttribute('role', 'status');
        tb.setAttribute('aria-live', 'polite');
        const khungLoiChao = document.querySelector('.khung-loi-chao-csdl');
        if (khungLoiChao && khungLoiChao.parentNode) {
            khungLoiChao.parentNode.insertBefore(tb, khungLoiChao.nextSibling);
        }
    }
    tb.textContent = tinNhan;
    tb.style.display = 'block';
}

// 4. TẠO CÂU GIỚI THIỆU NGẪU NHIÊN
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
