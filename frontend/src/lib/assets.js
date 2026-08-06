// Resolves an asset under the Vite public folder with the correct base.
const BASE = import.meta.env.BASE_URL || '/';

export function asset(path) {
  return `${BASE}${String(path).replace(/^\/+/, '')}`;
}

export const LOGO = asset('assets/logo1-white-removebg-preview.png');
export const LOGO_DARK = asset('assets/logo1.png');
