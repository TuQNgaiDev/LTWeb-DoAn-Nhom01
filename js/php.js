/** Tăng cường yêu thích và báo lỗi client; không chặn POST hợp lệ tới PHP. */
import { capNhatNutYeuThich, daoTrangThaiYeuThich } from './yeu-thich.js';
document.querySelectorAll('[data-hanh-dong="yeu-thich"]').forEach((nut) => { nut.hidden = false; });
capNhatNutYeuThich();
document.addEventListener('click', (event) => {
  const nut = event.target.closest('[data-hanh-dong="yeu-thich"]');
  if (!nut) return;
  daoTrangThaiYeuThich(Number(nut.dataset.id));
  capNhatNutYeuThich();
});
window.addEventListener('storage', () => capNhatNutYeuThich());
const form = document.querySelector('#bieu-mau-lien-he');
if (form) {
  form.addEventListener('focusout', (event) => {
    const field = event.target;
    if (!field.name || !field.validity) return;
    field.setCustomValidity('');
    if (field.name === 'email_sv' && field.value && !field.value.toLowerCase().endsWith('@ued.udn.vn')) field.setCustomValidity('Vui lòng dùng email @ued.udn.vn.');
    const error = document.getElementById(`loi-${field.name}`);
    if (error) error.textContent = field.validity.valid ? '' : field.validationMessage;
    field.setAttribute('aria-invalid', String(!field.validity.valid));
  });
  form.addEventListener('input', (event) => { event.target.setCustomValidity?.(''); });
}
