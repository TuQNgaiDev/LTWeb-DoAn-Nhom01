/** Tìm tức thời sau 350 ms; biểu mẫu GET vẫn hoạt động khi tắt JavaScript. */
const form = document.querySelector('[data-form-tim-kiem]');
const oTuKhoa = form?.querySelector('[name="q"]');
const vung = document.querySelector('[data-ket-qua-tim-kiem]');
let henGio;
let boDieuKhien;

function taoThe(s) {
  const bai = document.createElement('article'); bai.className = 'the';
  const than = document.createElement('div'); than.className = 'the__than';
  const h3 = document.createElement('h3'); h3.className = 'the__tieu-de';
  const a = document.createElement('a'); a.href = `chi-tiet.php?id=${encodeURIComponent(s.id)}`; a.textContent = s.ten;
  const dm = document.createElement('p'); dm.textContent = s.danh_muc;
  const tg = document.createElement('p'); tg.textContent = s.tac_gia;
  h3.append(a); than.append(h3, dm, tg); bai.append(than); return bai;
}

async function tim(q) {
  boDieuKhien?.abort(); boDieuKhien = new AbortController();
  try {
    const url = new URL('api/giao-trinh.php', document.baseURI); url.searchParams.set('q', q);
    const phanHoi = await fetch(url, { signal: boDieuKhien.signal, headers: { Accept: 'application/json' } });
    const json = await phanHoi.json(); if (!phanHoi.ok) throw new Error(json.loi || 'Không tìm được dữ liệu.');
    const tieuDe = document.createElement('h2'); tieuDe.textContent = 'Kết quả tìm tức thời';
    const dem = document.createElement('p'); dem.textContent = `Hiển thị ${json.du_lieu.length} kết quả đầu tiên.`;
    const luoi = document.createElement('div'); luoi.className = 'luoi-the luoi-the--ba-cot';
    json.du_lieu.forEach((s) => luoi.append(taoThe(s))); vung.replaceChildren(tieuDe, dem, luoi);
  } catch (loi) { if (loi.name !== 'AbortError') console.error(loi); }
}

oTuKhoa?.addEventListener('input', () => { clearTimeout(henGio); henGio = setTimeout(() => tim(oTuKhoa.value.trim()), 350); });
