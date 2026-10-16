<?php
/** Trang Giới thiệu nhóm dùng khung chung và dữ liệu từ máy chủ. */
require __DIR__ . '/inc/config.php';
$tieuDe = 'Giới thiệu nhóm';
$trang = 'gioi-thieu';
require __DIR__ . '/inc/header.php';
?>
<main class="noi-dung-chinh">
    <h1>Giới thiệu về UEDBookHub &amp; Nhóm Phát triển</h1>

    <section>
      <h2>Mục đích xây dựng website</h2>
      <p class="doan-van-dai">
        Website <strong>UEDBookHub</strong> được nghiên cứu và xây dựng trong khuôn khổ học phần <em>Thiết kế và Lập trình Web</em> tại Khoa Toán – Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng (năm học 2026 – 2027).
      </p>
      <p class="doan-van-dai">
        Mục tiêu trọng tâm của dự án là số hóa quy trình quản lý và tiếp cận học liệu cho sinh viên toàn trường: hỗ trợ tra cứu trực tuyến tình trạng sách thực tế trong kho thư viện, đơn giản hóa việc đăng ký mượn tài liệu, đồng thời tạo kênh tiếp cận chính thống giúp sinh viên đặt mua giáo trình xuất bản chính thức từ Nhà xuất bản Đại học Đà Nẵng với mức giá trợ giá học đường tốt nhất.
      </p>
      <p class="doan-van-dai">
        Giao diện website được thiết kế theo tư duy <strong>Mobile-First</strong>, đảm bảo trải nghiệm người dùng mượt mà trên cả thiết bị di động, máy tính bảng và màn hình máy tính để bàn, tuân thủ các tiêu chuẩn nghiêm ngặt về tính ngữ nghĩa HTML5, khả năng tiếp cận (Accessibility - WCAG 2.1 AA) và tối ưu hiệu năng Lighthouse.
      </p>
    </section>

    <section>
      <h2>Sinh viên thực hiện đề tài</h2>
      <div class="danh-sach-thanh-vien">
        <article class="the-thanh-vien">
          <h3 class="the-thanh-vien__ten">Trần Văn Tú</h3>
          <div class="the-thanh-vien__vai-tro">Trưởng nhóm – Quản trị kiến trúc hệ thống &amp; Thiết kế giao diện CSS</div>
          <div class="the-thanh-vien__lien-ket">
            <a href="thanhvien/3120224169_tu/gioithieu.php" class="nut nut--phu nut--day-du">Xem trang cá nhân Tú</a>
          </div>
        </article>

        <article class="the-thanh-vien">
          <h3 class="the-thanh-vien__ten">Bhling Trường</h3>
          <div class="the-thanh-vien__vai-tro">Thành viên – Biên tập học liệu &amp; Giao diện trang cá nhân</div>
          <div class="the-thanh-vien__lien-ket">
            <a href="thanhvien/3120124040_truong/gioithieu.php" class="nut nut--phu nut--day-du">Xem trang cá nhân Trường</a>
          </div>
        </article>

        <article class="the-thanh-vien">
          <h3 class="the-thanh-vien__ten">Lê Trung Hậu</h3>
          <div class="the-thanh-vien__vai-tro">Thành viên – Thu thập dữ liệu giáo trình &amp; Giao diện trang cá nhân</div>
          <div class="the-thanh-vien__lien-ket">
            <a href="thanhvien/3120224052_hau/gioithieu.php" class="nut nut--phu nut--day-du">Xem trang cá nhân Hậu</a>
          </div>
        </article>

        <article class="the-thanh-vien">
          <h3 class="the-thanh-vien__ten">Nguyễn Tuấn Nhật Thanh</h3>
          <div class="the-thanh-vien__vai-tro">Thành viên – Kiểm chuẩn W3C/Lighthouse &amp; Giao diện trang cá nhân</div>
          <div class="the-thanh-vien__lien-ket">
            <a href="thanhvien/3120224132_thanh/gioithieu.php" class="nut nut--phu nut--day-du">Xem trang cá nhân Thanh</a>
          </div>
        </article>
      </div>
    </section>
  </main>
<?php require __DIR__ . '/inc/footer.php'; ?>
