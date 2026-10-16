<?php
/** Hiển thị danh sách thẻ từ các đối tượng giáo trình. */
?>
<div class="luoi-the luoi-the--ba-cot">
<?php foreach ($danhSachThe as $s): ?>
<article class="the">
  <img class="the__anh" src="<?= e($s->anh) ?>" alt="Bìa <?= e($s->ten) ?>" width="240" height="300" loading="lazy">
  <div class="the__than">
    <h3 class="the__tieu-de"><a href="chi-tiet.php?id=<?= e($s->id) ?>"><?= e($s->ten) ?></a></h3>
    <p><?= e($s->khoa) ?></p><p><?= e($s->tacGia) ?></p>
    <p><strong><?= e(vnd($s->gia)) ?></strong> · Còn <?= e($s->soLuong) ?> bản</p>
    <div class="nhom-nut"><a class="nut nut--phu" href="chi-tiet.php?id=<?= e($s->id) ?>">Xem chi tiết</a><button hidden class="nut nut--phu" type="button" data-hanh-dong="yeu-thich" data-id="<?= e($s->id) ?>">Thêm yêu thích</button></div>
  </div>
</article>
<?php endforeach; ?>
</div>
