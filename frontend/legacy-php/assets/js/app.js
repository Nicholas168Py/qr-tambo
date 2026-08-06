/**
 * QR Tambo - Shared JavaScript Utilities
 */

// ============================================
// API Helper
// ============================================
async function api(endpoint, method = 'GET', data = null) {
    const apiBase = (window.API_BASE || '').replace(/\/+$/, '');
    const url = apiBase ? `${apiBase}/${endpoint.replace(/^\/+/, '')}` : endpoint;
    const logData = data ? JSON.stringify(data).substring(0, 200) : '';
    const separator = url.includes('?') ? '&' : '?';
    const cacheBuster = `_t=${Date.now()}`;
    const finalUrl = method === 'GET' ? `${url}${separator}${cacheBuster}` : url;
    console.log(`[API] -> ${method} ${finalUrl}`, logData);
    const options = {
        method: method,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    };

    if (data && method !== 'GET') {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(finalUrl, options);
        console.log(`[API] <- ${response.status} ${finalUrl}`);
        const text = await response.text();
        if (!text) {
            console.error(`[API] Respuesta vacía de ${finalUrl}`);
            return { success: false, message: 'Respuesta vacía del servidor', code: response.status };
        }
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error(`[API] JSON inválido desde ${finalUrl}:`, text.substring(0, 500));
            return { success: false, message: 'Error del servidor', code: response.status, raw: text.substring(0, 200) };
        }
        console.log(`[API] OK ${finalUrl}`, JSON.stringify(result).substring(0, 300));
        return result;
    } catch (error) {
        console.error(`[API] FETCH ERROR ${method} ${finalUrl}:`, error.message || error);
        return { success: false, message: 'Error de conexión con el servidor: ' + (error.message || '') };
    }
}

// ============================================
// Toast Notifications
// ============================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: '<i class="fas fa-circle-check"></i>',
        error: '<i class="fas fa-circle-xmark"></i>',
        warning: '<i class="fas fa-triangle-exclamation"></i>',
        info: '<i class="fas fa-circle-info"></i>'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${icons[type] || ''}</span><span>${message}</span>`;

    toast.addEventListener('click', () => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    });

    container.appendChild(toast);

    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 300);
        }
    }, 4000);
}

// ============================================
// Loading Overlay
// ============================================
function showLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.style.display = 'flex';
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.style.display = 'none';
}

// ============================================
// Date & Time Helpers
// ============================================
const MONTH_NAMES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const DAY_NAMES = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const DAY_NAMES_SHORT = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

function formatDate(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const day = date.getDate();
    const month = MONTH_NAMES[date.getMonth()];
    const year = date.getFullYear();
    return `${day} de ${month}, ${year}`;
}

function formatDateFull(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const dayName = DAY_NAMES[date.getDay()];
    const day = date.getDate();
    const month = MONTH_NAMES[date.getMonth()];
    const year = date.getFullYear();
    return `${dayName}, ${day} de ${month} ${year}`;
}

function formatTime(timeStr) {
    if (!timeStr) return '';
    const parts = timeStr.split(':');
    let hours = parseInt(parts[0]);
    const minutes = parts[1];
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    return `${hours}:${minutes} ${ampm}`;
}

function getDayName(num) {
    // 1=Lunes, ..., 7=Domingo (ISO format)
    const names = { 1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 5: 'Viernes', 6: 'Sábado', 7: 'Domingo' };
    return names[num] || 'Desconocido';
}

function getDayShort(num) {
    const names = { 1: 'Lun', 2: 'Mar', 3: 'Mié', 4: 'Jue', 5: 'Vie', 6: 'Sáb', 7: 'Dom' };
    return names[num] || '?';
}

function getCurrentDayNumber() {
    // Returns 1=Monday ... 7=Sunday (ISO format)
    const jsDay = new Date().getDay(); // 0=Sunday
    return jsDay === 0 ? 7 : jsDay;
}

function getTodayStr() {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// ============================================
// Logout
// ============================================
async function logout() {
    showLoading();
    // Determine basePath based on current URL depth
    let basePath = '';
    if (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/bailarin/')) {
        basePath = '../';
    }
    await api('auth/logout', 'POST');
    window.location.href = basePath + 'index.php';
}

// ============================================
// Generate UUID-like token
// ============================================
function generateToken() {
    return Date.now().toString(36) + '-' + Math.random().toString(36).substr(2, 6);
}

// ============================================
// Modal Helpers
// ============================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('active');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
}

// Close modal when clicking overlay
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// ============================================
// Sidebar Helpers
// ============================================
function closeSidebarIfOpen() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
}

// ============================================
// Debug Console visible en pantalla
// ============================================
(function() {
    const panel = document.getElementById('debug-panel');
    const logs = document.getElementById('debug-logs');
    if (!panel || !logs) return;

    const originalLog = console.log;
    const originalWarn = console.warn;
    const originalError = console.error;

    function addLog(level, args) {
        const text = Array.from(args).map(a => typeof a === 'string' ? a : JSON.stringify(a)).join(' ');
        const time = new Date().toLocaleTimeString();
        const prefix = level === 'error' ? '🔴' : level === 'warn' ? '🟡' : '⚪';
        const line = document.createElement('div');
        line.textContent = `${time} ${prefix} ${text}`;
        line.style.color = level === 'error' ? '#f66' : level === 'warn' ? '#fc0' : '#0f0';
        logs.appendChild(line);
        panel.scrollTop = panel.scrollHeight;

        // Auto-mostrar en errores
        if (level === 'error') {
            panel.style.display = 'block';
        }
    }

    console.log = function() { originalLog.apply(console, arguments); addLog('log', arguments); };
    console.warn = function() { originalWarn.apply(console, arguments); addLog('warn', arguments); };
    console.error = function() { originalError.apply(console, arguments); addLog('error', arguments); };

    // Tocar 5 veces rápido muestra el panel
    let tapCount = 0;
    let tapTimer = null;
    document.addEventListener('click', () => {
        tapCount++;
        if (tapTimer) clearTimeout(tapTimer);
        tapTimer = setTimeout(() => { tapCount = 0; }, 1000);
        if (tapCount >= 5) {
            tapCount = 0;
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    });

    console.log('[DEBUG] Panel de depuración listo — tocá 5 veces para mostrar');
})();
