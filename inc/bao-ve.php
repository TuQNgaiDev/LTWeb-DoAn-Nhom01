<?php
/** Chặn truy cập quản trị trước khi gửi HTML. */
if (!isset($_SESSION['user']) || !is_string($_SESSION['user'])) { chuyenHuong('dang-nhap.php'); }
