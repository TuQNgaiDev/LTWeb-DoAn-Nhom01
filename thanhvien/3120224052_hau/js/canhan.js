/** 
 * Ba tương tác cho trang cá nhân Lê Trung Hậu: 
 * 1. Hiển thị lời chào theo thời gian. 
 * 2. Đếm lượt thích (có ghi nhớ qua localStorage). 
 * 3. Tạo câu giới thiệu ngẫu nhiên (không trùng câu liền kề). 
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

// 2. ĐẾM LƯỢT THÍCH
const nutThich = document.querySelector('#nut-thich'); 
const soLuotThich = document.querySelector('#so-luot-thich'); 
const KHOA_LUOT_THICH = 'luotThich_hau_3120224052';

let luotThich = Number(localStorage.getItem(KHOA_LUOT_THICH)) || 0;

if (nutThich && soLuotThich) { 
    soLuotThich.textContent = luotThich;

    nutThich.addEventListener('click', () => { 
        luotThich += 1; 
        soLuotThich.textContent = luotThich; 
        
        // Hiệu ứng nhịp tim
        nutThich.classList.add('nut-thich--nhay');
        setTimeout(() => nutThich.classList.remove('nut-thich--nhay'), 450);

        try {
            localStorage.setItem(KHOA_LUOT_THICH, luotThich);
        } catch (e) {
            console.warn('Không thể lưu lượt thích:', e);
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
    nutGioiThieu.addEventListener('click', () => { 
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