/** Thêm vào giỏ bằng fetch; form POST nguyên bản là phương án khi tắt JavaScript. */
const formGio = document.querySelector('[data-them-gio]');
formGio?.addEventListener('submit', async (suKien) => {
  suKien.preventDefault();
  const nut = formGio.querySelector('button[type="submit"]'); nut.disabled = true;
  try {
    const duLieu = new FormData(formGio);
    const phanHoi = await fetch(new URL('api/gio-hang.php', document.baseURI), {
      method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ id: Number(duLieu.get('id')), sl: Number(duLieu.get('so_luong')) })
    });
    const json = await phanHoi.json(); if (!phanHoi.ok) throw new Error(json.loi || 'Không thể thêm vào giỏ.');
    document.querySelectorAll('[data-so-mon]').forEach((vung) => { vung.textContent = String(json.so_mon); });
    nut.textContent = 'Đã thêm vào giỏ';
  } catch (loi) { window.alert(loi.message); } finally { nut.disabled = false; }
});
