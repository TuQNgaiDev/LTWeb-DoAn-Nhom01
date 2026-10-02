// File canhan.js xử lý các tương tác cho trang cá nhân.
// Chức năng: Accordion, lọc kỹ năng và đếm ngược ngày thi.
// Chức năng: phóng to ảnh, tải thông tin và sao chép email.
// Cách thử: nhấn các nút và tương tác trực tiếp trên trang cá nhân.





// ==========================================
// CHỨC NĂNG 3: ACCORDION
// ==========================================

const accordionButtons =
    document.querySelectorAll(".accordion-button");


accordionButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        // Kiểm tra trạng thái hiện tại
        const isExpanded =
            button.getAttribute("aria-expanded") === "true";


        // Đổi trạng thái mở / đóng
        button.setAttribute(
            "aria-expanded",
            String(!isExpanded)
        );


        // Lấy ID của phần nội dung cần đóng / mở
        const contentId =
            button.getAttribute("aria-controls");

        const content =
            document.getElementById(contentId);


        // Thu gọn hoặc mở rộng nội dung
        content.classList.toggle(
            "is-hidden",
            isExpanded
        );

    });

});


// ==========================================
// CHỨC NĂNG BỔ SUNG: LỌC KỸ NĂNG
// ==========================================

const filterButtons =
    document.querySelectorAll(".filter-button");

const skillItems =
    document.querySelectorAll(".skills li");


filterButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        const selectedGroup =
            button.dataset.group;


        // Xóa trạng thái active của các nút
        filterButtons.forEach(function (item) {

            item.classList.remove("active");

        });


        // Đánh dấu nút đang được chọn
        button.classList.add("active");


        // Hiển thị / ẩn kỹ năng
        skillItems.forEach(function (skill) {

            if (
                selectedGroup === "all" ||
                skill.dataset.group === selectedGroup
            ) {

                skill.style.display = "";

            } else {

                skill.style.display = "none";

            }

        });

    });

});



// Đồng hồ đếm ngược đến ngày thi cuối kỳ
const examDate = new Date("2026-12-01T07:00:00").getTime();

const countdown = document.getElementById("countdown");
const daysElement = document.getElementById("days");
const hoursElement = document.getElementById("hours");
const minutesElement = document.getElementById("minutes");
const secondsElement = document.getElementById("seconds");
const countdownMessage = document.getElementById("countdownMessage");

function updateCountdown() {
    const now = new Date().getTime();
    const distance = examDate - now;

    if (distance <= 0) {
        daysElement.textContent = "0";
        hoursElement.textContent = "0";
        minutesElement.textContent = "0";
        secondsElement.textContent = "0";
        countdownMessage.textContent = "Đã đến ngày thi cuối kỳ!";
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor(
        (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
    );
    const minutes = Math.floor(
        (distance % (1000 * 60 * 60)) / (1000 * 60)
    );
    const seconds = Math.floor(
        (distance % (1000 * 60)) / 1000
    );

    daysElement.textContent = days;
    hoursElement.textContent = hours;
    minutesElement.textContent = minutes;
    secondsElement.textContent = seconds;
}

updateCountdown();
setInterval(updateCountdown, 1000);

// ==========================================
// CHỨC NĂNG: PHÓNG TO / THU NHỎ ẢNH
// ==========================================

const profileImage = document.getElementById("profileImage");

if (profileImage) {

    // Click chuột vào ảnh
    profileImage.addEventListener("click", function () {
        profileImage.classList.toggle("large-image");
    });

    // Nhấn Enter hoặc Space bằng bàn phím
    profileImage.addEventListener("keydown", function (event) {

        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            profileImage.classList.toggle("large-image");
        }

    });
}

// ================================
// TẢI THÔNG TIN CÁ NHÂN
// ================================

const downloadInfoBtn = document.getElementById("downloadInfoBtn");

if (downloadInfoBtn) {
    downloadInfoBtn.addEventListener("click", function () {

        const info = `
THÔNG TIN CÁ NHÂN

Họ và tên: Nguyễn Tuấn Nhật Thanh
MSSV: 3120224132
Ngành: Công nghệ thông tin

KỸ NĂNG
- HTML
- CSS
- JavaScript

SỞ THÍCH
- Lập trình
- Thiết kế website
- Tìm hiểu công nghệ

Website nhóm: UEDBookHub
        `.trim();

        const file = new Blob([info], {
            type: "text/plain;charset=utf-8"
        });

        const url = URL.createObjectURL(file);

        const link = document.createElement("a");
        link.href = url;
        link.download = "thong-tin-ca-nhan.txt";

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        URL.revokeObjectURL(url);
    });
}

// ==========================================
// CHỨC NĂNG: SAO CHÉP EMAIL
// ==========================================

const copyEmailBtn = document.getElementById("copyEmailBtn");
const email = document.getElementById("email");
const copyMessage = document.getElementById("copyMessage");

if (copyEmailBtn && email && copyMessage) {

    copyEmailBtn.addEventListener("click", function () {

        navigator.clipboard.writeText(email.textContent.trim())
            .then(function () {
                copyMessage.textContent =
                    "Đã sao chép email vào bộ nhớ tạm!";
            })
            .catch(function () {
                copyMessage.textContent =
                    "Không thể sao chép email.";
            });

    });
}