/**
 * Cung cấp hàm dùng chung để tải JSON bằng Fetch API.
 * Mọi phản hồi đều được kiểm tra trạng thái HTTP trước khi đọc dữ liệu.
 */
export async function taiJSON(url, tuyChon = {}) {
  if ((tuyChon.method || 'GET').toUpperCase() !== 'GET') {
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    tuyChon.headers = { ...(tuyChon.headers || {}), 'X-CSRF-Token': token };
  }
  const phanHoi = await fetch(url, tuyChon);

  if (!phanHoi.ok) {
    throw new Error(`HTTP ${phanHoi.status} khi tải ${url}`);
  }

  return phanHoi.json();
}
