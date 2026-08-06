export const MONTH_NAMES = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

export const DAY_NAMES = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
export const DAY_NAMES_SHORT = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

export function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(`${dateStr}T00:00:00`);
  return `${date.getDate()} de ${MONTH_NAMES[date.getMonth()]}, ${date.getFullYear()}`;
}

export function formatDateFull(dateStr) {
  if (!dateStr) return '';
  const date = new Date(`${dateStr}T00:00:00`);
  return `${DAY_NAMES[date.getDay()]}, ${date.getDate()} de ${MONTH_NAMES[date.getMonth()]} ${date.getFullYear()}`;
}

export function formatTime(timeStr) {
  if (!timeStr) return '';
  const parts = String(timeStr).split(':');
  let hours = parseInt(parts[0], 10);
  const minutes = parts[1];
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12;
  return `${hours}:${minutes} ${ampm}`;
}

const ISO_NAMES = { 1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 5: 'Viernes', 6: 'Sábado', 7: 'Domingo' };
const ISO_SHORT = { 1: 'Lun', 2: 'Mar', 3: 'Mié', 4: 'Jue', 5: 'Vie', 6: 'Sáb', 7: 'Dom' };

export function getDayName(num) {
  return ISO_NAMES[num] || 'Desconocido';
}

export function getDayShort(num) {
  return ISO_SHORT[num] || '?';
}

export function getCurrentDayNumber() {
  const jsDay = new Date().getDay();
  return jsDay === 0 ? 7 : jsDay;
}

export function getTodayStr() {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

export function generateToken() {
  return Date.now().toString(36) + '-' + Math.random().toString(36).substr(2, 6);
}
