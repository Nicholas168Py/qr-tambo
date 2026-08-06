import axios from 'axios';

const API_BASE = (import.meta.env.VITE_API_BASE || '/backend/api').replace(/\/+$/, '');

const http = axios.create({
  baseURL: API_BASE,
  withCredentials: true,
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
});

/**
 * Unified API helper. Mirrors the legacy api() contract:
 * never throws on HTTP errors; returns the parsed JSON body or a
 * normalized { success:false, message } object.
 */
export async function api(endpoint, method = 'GET', data = null) {
  const url = `/${endpoint.replace(/^\/+/, '')}`;
  try {
    const response = await http.request({
      url,
      method,
      data: data && method !== 'GET' ? data : undefined,
    });
    return response.data;
  } catch (error) {
    if (error.response) {
      const body = error.response.data;
      if (body && typeof body === 'object' && 'success' in body) return body;
      return { success: false, message: 'Error del servidor', code: error.response.status };
    }
    return { success: false, message: `Error de conexión con el servidor: ${error.message || ''}` };
  }
}

export default http;
