<?php
/** Chân trang và JavaScript tăng cường; chức năng chính không cần JS.
 * Trang con có thể truyền danh sách $jsThem từ thư mục gốc.
 */
$jsThem ??= [];
?>
<footer class="chan-trang">
  <p>&copy; 2026 UEDBookHub · Nhóm 01</p>
  <p>Trần Văn Tú · Lê Trung Hậu · Nguyễn Tuấn Nhật Thanh · Bhling Trường</p>
</footer>
<script type="module" src="<?= e($goc ?? '') ?>js/main.js"></script>
<script type="module" src="<?= e($goc ?? '') ?>js/php.js"></script>
<?php if (($trang ?? '') === 'index'): ?><script type="module" src="<?= e($goc ?? '') ?>js/trang-chu.js"></script><?php endif; ?>
<?php foreach ($jsThem as $js): ?><script type="module" src="<?= e(($goc ?? '') . $js) ?>"></script><?php endforeach; ?>
</body>
</html>
