<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Panel de Administración';
$basePath = '../';
include '../includes/header.php';
?>
<div class="admin-layout">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h2><i class="fas fa-music"></i> QR Tambo</h2>
            <small>Panel de Administración</small>
        </div>
        <nav class="sidebar-nav">
            <button class="nav-link active" data-section="resumen" onclick="switchSection('resumen', this)">
                <span class="nav-icon"><i class="fas fa-chart-bar"></i></span> Resumen
            </button>
            <button class="nav-link" data-section="clases" onclick="switchSection('clases', this)">
                <span class="nav-icon"><i class="fas fa-music"></i></span> Clases
            </button>
            <button class="nav-link" data-section="horarios" onclick="switchSection('horarios', this)">
                <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span> Horarios
            </button>
            <button class="nav-link" data-section="asistencia" onclick="switchSection('asistencia', this)">
                <span class="nav-icon"><i class="fas fa-check-circle"></i></span> Asistencia
            </button>
            <button class="nav-link" data-section="reportes" onclick="switchSection('reportes', this)">
                <span class="nav-icon"><i class="fas fa-chart-line"></i></span> Reportes
            </button>
            <button class="nav-link" data-section="bailarines" onclick="switchSection('bailarines', this)">
                <span class="nav-icon"><i class="fas fa-users"></i></span> Bailarines
            </button>
            <a href="qr_dia.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-qrcode"></i></span> QR del Día
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">A</div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['nombre']) ?></div>
                    <div class="user-role"><span class="badge badge-admin">Admin</span></div>
                </div>
            </div>
            <button class="btn btn-secondary btn-block btn-sm" onclick="logout()">Cerrar Sesión</button>
        </div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <h1 id="sectionTitle">Resumen</h1>
            <p id="sectionDesc">Vista general del sistema</p>
        </div>

        <!-- Resumen Section -->
        <div class="section active" id="section-resumen">
            <div class="stats-grid" id="resumenStats">
                <div class="glass-card stat-card"><div class="stat-icon"><i class="fas fa-music"></i></div><div class="stat-value" id="totalClases">-</div><div class="stat-label">Clases</div></div>
                <div class="glass-card stat-card"><div class="stat-icon"><i class="fas fa-calendar-alt"></i></div><div class="stat-value" id="totalHorarios">-</div><div class="stat-label">Horarios</div></div>
                <div class="glass-card stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-value" id="totalBailarines">-</div><div class="stat-label">Bailarines</div></div>
                <div class="glass-card stat-card"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-value" id="totalAsistencia">-</div><div class="stat-label">Asistencias este mes</div></div>
            </div>
            <div class="glass-card" style="padding:24px;">
                <h3 style="margin-bottom:16px;"><i class="fas fa-bolt"></i> Acceso Rápido</h3>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="qr_dia.php" class="btn btn-primary"><i class="fas fa-qrcode"></i> Generar QR del Día</a>
                    <button class="btn btn-secondary" onclick="switchSection('clases', document.querySelector('[data-section=clases]'))"><i class="fas fa-music"></i> Gestionar Clases</button>
                    <button class="btn btn-secondary" onclick="switchSection('horarios', document.querySelector('[data-section=horarios]'))"><i class="fas fa-calendar-alt"></i> Configurar Horarios</button>
                </div>
            </div>
        </div>

        <!-- Clases Section -->
        <div class="section" id="section-clases">
            <div class="glass-card add-form">
                <div class="form-group"><input type="text" id="claseNombre" class="form-input" placeholder="Nombre de la clase" required></div>
                <div class="form-group"><input type="text" id="claseDesc" class="form-input" placeholder="Descripción (opcional)"></div>
                <button class="btn btn-primary" onclick="createClase()">+ Agregar</button>
            </div>
            <div id="clasesList"><div class="empty-state"><span class="empty-icon"><i class="fas fa-music"></i></span>Cargando clases...</div></div>
        </div>

        <!-- Horarios Section -->
        <div class="section" id="section-horarios">
            <div class="glass-card add-form" id="horarioForm">
                <div class="form-group">
                    <select id="horarioClase" class="form-input"><option value="">Seleccionar clase...</option></select>
                </div>
                <div class="form-group">
                    <select id="horarioDia" class="form-input">
                        <option value="1">Lunes</option><option value="2">Martes</option>
                        <option value="3">Miércoles</option><option value="4">Jueves</option>
                        <option value="5">Viernes</option><option value="6">Sábado</option>
                    </select>
                </div>
                <div class="form-group"><input type="time" id="horarioInicio" class="form-input" required></div>
                <div class="form-group"><input type="time" id="horarioFin" class="form-input" required></div>
                <button class="btn btn-primary" onclick="createHorario()">+ Agregar</button>
            </div>
            <div id="horariosList">
                <div class="empty-state"><span class="empty-icon"><i class="fas fa-calendar-alt"></i></span>Cargando horario semanal...</div>
            </div>
        </div>

        <!-- Asistencia Section -->
        <div class="section" id="section-asistencia">
            <div class="glass-card filter-bar">
                <div class="form-group">
                    <input type="date" id="filtroFecha" class="form-input">
                </div>
                <div class="form-group">
                    <input type="text" id="filtroCedula" class="form-input" placeholder="Filtrar por cédula...">
                </div>
                <div class="form-group">
                    <input type="text" id="filtroClase" class="form-input" placeholder="Filtrar por clase...">
                </div>
                <button class="btn btn-primary" onclick="loadAsistencia()"><i class="fas fa-search"></i> Filtrar</button>
                <button class="btn btn-secondary" onclick="resetFiltros()">Limpiar</button>
            </div>
            <div id="asistenciaList"><div class="empty-state"><span class="empty-icon"><i class="fas fa-check-circle"></i></span>Cargando asistencias...</div></div>
        </div>

        <!-- Reportes Section -->
        <div class="section" id="section-reportes">
            <div class="glass-card filter-bar">
                <div class="form-group">
                    <select id="reporteMes" class="form-input">
                        <option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option>
                        <option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option>
                        <option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option>
                        <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="number" id="reporteAnio" class="form-input" min="2024" max="2030">
                </div>
                <button class="btn btn-primary" onclick="loadReporte()"><i class="fas fa-chart-line"></i> Ver Reporte</button>
            </div>
            <div id="reporteContent"></div>
        </div>

        <!-- Bailarines Section -->
        <div class="section" id="section-bailarines">
            <div class="glass-card" style="padding:24px;margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <h3><i class="fas fa-users"></i> Bailarines Registrados</h3>
                    <span id="totalBailarinesCount" style="font-size:0.9rem;color:var(--text-secondary);padding:6px 14px;background:rgba(255,255,255,0.06);border-radius:50px;"></span>
                </div>
            </div>
            <div id="bailarinesList"><div class="empty-state"><span class="empty-icon"><i class="fas fa-users"></i></span>Cargando bailarines...</div></div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
console.log('[ADMIN] Dashboard cargado');

// Sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function switchSection(sectionId, btn) {
    console.log('[ADMIN] switchSection:', sectionId);
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('active'));
    document.getElementById('section-' + sectionId).classList.add('active');
    if (btn) btn.classList.add('active');

    const titles = { resumen: ['Resumen', 'Vista general del sistema'], clases: ['Clases', 'Gestiona los tipos de baile'], horarios: ['Horarios', 'Configura el horario semanal'], asistencia: ['Asistencia', 'Registros de asistencia'], reportes: ['Reportes', 'Reportes mensuales de asistencia'], bailarines: ['Bailarines', 'Administra los bailarines registrados'] };
    document.getElementById('sectionTitle').textContent = titles[sectionId][0];
    document.getElementById('sectionDesc').textContent = titles[sectionId][1];

    if (sectionId === 'resumen') loadResumen();
    if (sectionId === 'clases') loadClases();
    if (sectionId === 'horarios') { loadClasesSelect(); loadHorarios(); }
    if (sectionId === 'asistencia') loadAsistencia();
    if (sectionId === 'reportes') loadReporte();
    if (sectionId === 'bailarines') loadBailarines();

    if (window.innerWidth <= 768) toggleSidebar();
}

// Resumen
async function loadResumen() {
    console.log('[ADMIN] loadResumen()');
    const [c, h, u, a] = await Promise.all([
        api('../api/clases/list.php'),
        api('../api/horarios/list.php'),
        api('../api/usuarios/list.php'),
        api('../api/asistencia/list.php?mes=' + (new Date().getMonth() + 1) + '&anio=' + new Date().getFullYear())
    ]);
    document.getElementById('totalClases').textContent = c.success ? c.data.length : '-';
    document.getElementById('totalHorarios').textContent = h.success ? h.data.length : '-';
    document.getElementById('totalBailarines').textContent = u.success ? u.total : '-';
    document.getElementById('totalAsistencia').textContent = a.success ? a.data.length : '-';
}

// Clases CRUD
async function loadClases() {
    console.log('[ADMIN] loadClases()');
    const result = await api('../api/clases/list.php');
    const container = document.getElementById('clasesList');
    if (!result.success || result.data.length === 0) {
        container.innerHTML = '<div class="empty-state"><span class="empty-icon"><i class="fas fa-music"></i></span><p>No hay clases registradas</p></div>';
        return;
    }
    container.innerHTML = '<div class="cards-grid">' + result.data.map(c => `
        <div class="glass-card item-card">
            <h3>${c.nombre}</h3>
            <p>${c.descripcion || 'Sin descripción'}</p>
            <div class="card-actions">
                <button class="btn btn-danger btn-sm" onclick="deleteClase(${c.id}, '${c.nombre}')">Eliminar</button>
            </div>
        </div>`).join('') + '</div>';
}

async function createClase() {
    const nombre = document.getElementById('claseNombre').value.trim();
    const desc = document.getElementById('claseDesc').value.trim();
    if (!nombre) return showToast('Ingresa un nombre para la clase', 'warning');
    console.log('[ADMIN] createClase:', nombre);
    const result = await api('../api/clases/create.php', 'POST', { nombre, descripcion: desc });
    if (result.success) {
        showToast(result.message, 'success');
        document.getElementById('claseNombre').value = '';
        document.getElementById('claseDesc').value = '';
        loadClases();
    } else {
        showToast(result.message, 'error');
    }
}

async function deleteClase(id, nombre) {
    if (!confirm(`¿Eliminar la clase "${nombre}"? También se eliminarán sus horarios.`)) return;
    console.log('[ADMIN] deleteClase:', id, nombre);
    const result = await api('../api/clases/delete.php', 'POST', { id });
    if (result.success) { showToast(result.message, 'success'); loadClases(); }
    else showToast(result.message, 'error');
}

// Horarios
const DAYS_LABELS = { 1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 5: 'Viernes', 6: 'Sábado' };
const DAYS_ORDER = [1, 2, 3, 4, 5, 6];

async function loadHorarios() {
    console.log('[ADMIN] loadHorarios()');
    const result = await api('../api/horarios/list.php');
    const container = document.getElementById('horariosList');
    console.log('[ADMIN] Horarios desde API:', JSON.stringify(result.data).substring(0, 500));

    if (!result.success || result.data.length === 0) {
        container.innerHTML = '<div class="empty-state"><span class="empty-icon"><i class="fas fa-calendar-alt"></i></span><p>No hay horarios configurados. Agregá uno arriba.</p></div>';
        return;
    }

    const horarios = result.data;

    const timeSlots = [...new Set(horarios.map(h => h.hora_inicio))].sort();

    const grid = {};
    horarios.forEach(h => {
        const key = h.hora_inicio;
        if (!grid[key]) grid[key] = {};
        grid[key][h.dia_semana] = h;
    });

    let html = '<div class="schedule-grid-wrapper"><table class="schedule-grid">';
    html += '<thead><tr><th>Hora</th>';
    DAYS_ORDER.forEach(d => { html += `<th>${DAYS_LABELS[d]}</th>`; });
    html += '</tr></thead><tbody>';

    timeSlots.forEach(hora => {
        html += `<tr><td style="text-align:right;padding-right:10px;color:var(--text-muted);font-size:0.78rem;white-space:nowrap;">${formatTime(hora)}</td>`;
        DAYS_ORDER.forEach((dia, colIdx) => {
            const h = grid[hora] && grid[hora][dia];
            if (h) {
                const name = h.clase_nombre.replace(/'/g, "\\'");
                html += `<td class="slot-class" data-dia="${h.dia_semana}" data-col="${colIdx + 1}" title="${h.clase_nombre}">
                    <span class="class-name">${h.clase_nombre}</span>
                    <button class="btn-sm-ghost" onclick="deleteHorario(${h.id}, '${name}')" title="Eliminar"><i class="fas fa-trash" style="color: red; background: none;"></i></button>
                </td>`;
            } else {
                html += '<td class="slot-empty"><i class="fas fa-minus"></i></td>';
            }
        });
        html += '</tr>';
    });

    html += '</tbody></table></div>';
    console.log('[ADMIN] Grid HTML generado');
    container.innerHTML = html;

    // Debug: logear las columnas del header
    const headerCells = container.querySelectorAll('thead th');
    console.log('[ADMIN] Columnas del grid:', Array.from(headerCells).map(th => th.textContent).join(' | '));
}

async function loadClasesSelect() {
    console.log('[ADMIN] loadClasesSelect()');
    const result = await api('../api/clases/list.php');
    const select = document.getElementById('horarioClase');
    if (result.success) {
        select.innerHTML = '<option value="">Seleccionar clase...</option>' +
            result.data.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
    }
}

async function createHorario() {
    const clase_id = parseInt(document.getElementById('horarioClase').value);
    const dia_semana = parseInt(document.getElementById('horarioDia').value);
    const hora_inicio = document.getElementById('horarioInicio').value;
    const hora_fin = document.getElementById('horarioFin').value;
    if (!clase_id || !hora_inicio || !hora_fin) return showToast('Completa todos los campos', 'warning');
    console.log('[ADMIN] createHorario:', { clase_id, dia_semana, hora_inicio, hora_fin });
    const result = await api('../api/horarios/create.php', 'POST', { clase_id, dia_semana, hora_inicio, hora_fin });
    if (result.success) {
        showToast(result.message, 'success');
        document.getElementById('horarioInicio').value = '';
        document.getElementById('horarioFin').value = '';
        loadHorarios();
    } else showToast(result.message, 'error');
}

async function deleteHorario(id, nombre) {
    if (!confirm(`¿Eliminar "${nombre}" de este horario?`)) return;
    console.log('[ADMIN] deleteHorario:', id, nombre);
    const result = await api('../api/horarios/delete.php', 'POST', { id });
    if (result.success) { showToast(result.message, 'success'); loadHorarios(); }
    else showToast(result.message, 'error');
}

// Asistencia
async function loadAsistencia() {
    console.log('[ADMIN] loadAsistencia()');
    const params = new URLSearchParams();
    const fecha = document.getElementById('filtroFecha').value;
    const cedula = document.getElementById('filtroCedula').value.trim();
    const clase = document.getElementById('filtroClase').value.trim();
    if (fecha) { params.set('mes', new Date(fecha).getMonth() + 1); params.set('anio', new Date(fecha).getFullYear()); }
    if (cedula) params.set('cedula', cedula);
    if (clase) params.set('clase', clase);

    const result = await api(`../api/asistencia/list.php?${params.toString()}`);
    const container = document.getElementById('asistenciaList');

    if (!result.success || result.data.length === 0) {
        container.innerHTML = '<div class="empty-state"><span class="empty-icon"><i class="fas fa-check-circle"></i></span><p>No se encontraron registros de asistencia</p></div>';
        return;
    }
    container.innerHTML = `<div class="table-container"><table>
        <thead><tr><th>#</th><th>Cédula</th><th>Nombre</th><th>Clase</th><th>Fecha</th><th>Hora</th></tr></thead>
        <tbody>${result.data.map((r, i) => `<tr>
            <td>${i + 1}</td><td>${r.cedula}</td><td>${r.nombre}</td>
            <td>${r.clase}</td><td>${formatDate(r.fecha)}</td><td>${formatTime(r.hora_registro)}</td>
        </tr>`).join('')}</tbody>
    </table></div>`;
}

function resetFiltros() {
    console.log('[ADMIN] resetFiltros()');
    document.getElementById('filtroFecha').value = '';
    document.getElementById('filtroCedula').value = '';
    document.getElementById('filtroClase').value = '';
    loadAsistencia();
}

// Reportes
async function loadReporte() {
    console.log('[ADMIN] loadReporte()');
    const mes = document.getElementById('reporteMes').value;
    let anio = document.getElementById('reporteAnio').value;
    if (!anio) anio = new Date().getFullYear();

    const result = await api(`../api/asistencia/reporte_mensual.php?mes=${mes}&anio=${anio}`);
    const container = document.getElementById('reporteContent');
    if (!result.success) {
        container.innerHTML = '<div class="empty-state"><span class="empty-icon"><i class="fas fa-chart-line"></i></span><p>Error al cargar reporte</p></div>';
        return;
    }
    const d = result.data;
    container.innerHTML = `
        <div class="stats-grid">
            <div class="glass-card stat-card"><div class="stat-icon"><i class="fas fa-clipboard-list"></i></div><div class="stat-value">${d.total_registros}</div><div class="stat-label">Registros Totales</div></div>
            <div class="glass-card stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-value">${d.bailarines_unicos}</div><div class="stat-label">Bailarines Únicos</div></div>
        </div>
        ${d.por_clase.length ? `<div class="glass-card" style="padding:24px;margin-bottom:20px;">
            <h3 style="margin-bottom:16px;">Asistencia por Clase</h3>
            <div class="table-container"><table>
                <thead><tr><th>Clase</th><th>Total</th></tr></thead>
                <tbody>${d.por_clase.map(c => `<tr><td>${c.clase}</td><td><strong>${c.total}</strong></td></tr>`).join('')}</tbody>
            </table></div>
        </div>` : ''}
        ${d.por_bailarin.length ? `<div class="glass-card" style="padding:24px;margin-bottom:20px;">
            <h3 style="margin-bottom:16px;">Asistencia por Bailarín</h3>
            <div class="table-container"><table>
                <thead><tr><th>Cédula</th><th>Nombre</th><th>Total</th></tr></thead>
                <tbody>${d.por_bailarin.map(b => `<tr><td>${b.cedula}</td><td>${b.nombre}</td><td><strong>${b.total}</strong></td></tr>`).join('')}</tbody>
            </table></div>
        </div>` : ''}
        ${d.por_dia.length ? `<div class="glass-card" style="padding:24px;">
            <h3 style="margin-bottom:16px;">Asistencia por Día</h3>
            <div class="table-container"><table>
                <thead><tr><th>Fecha</th><th>Total</th></tr></thead>
                <tbody>${d.por_dia.map(dia => `<tr><td>${formatDate(dia.fecha)}</td><td><strong>${dia.total}</strong></td></tr>`).join('')}</tbody>
            </table></div>
        </div>` : ''}`;
}

// Bailarines
async function loadBailarines() {
    console.log('[ADMIN] loadBailarines()');
    const result = await api('../api/usuarios/list.php');
    const container = document.getElementById('bailarinesList');
    const totalEl = document.getElementById('totalBailarinesCount');

    if (!result.success || result.data.length === 0) {
        container.innerHTML = '<div class="empty-state"><span class="empty-icon"><i class="fas fa-users"></i></span><p>No hay bailarines registrados</p></div>';
        if (totalEl) totalEl.textContent = '0 bailarines';
        return;
    }

    if (totalEl) totalEl.textContent = `${result.total} bailarín${result.total !== 1 ? 'es' : ''}`;

    container.innerHTML = `<div class="table-container"><table>
        <thead><tr><th>#</th><th>Cédula</th><th>Nombre</th><th>Registrado</th><th>Acción</th></tr></thead>
        <tbody>${result.data.map((u, i) => {
            const date = u.created_at ? formatDate(u.created_at.split(' ')[0]) : '-';
            return `<tr>
                <td>${i + 1}</td>
                <td>${u.cedula}</td>
                <td><strong>${u.nombre}</strong></td>
                <td>${date}</td>
                <td><button class="btn btn-danger btn-sm" onclick="deleteBailarin(${u.id}, '${u.nombre.replace(/'/g, "\\'")}')">Eliminar</button></td>
            </tr>`;
        }).join('')}</tbody>
    </table></div>`;
}

async function deleteBailarin(id, nombre) {
    if (!confirm(`¿Eliminar la cuenta de "${nombre}"?\n\nSe eliminará permanentemente.`)) return;
    console.log('[ADMIN] deleteBailarin:', id, nombre);
    const result = await api('../api/usuarios/delete.php', 'POST', { id });
    if (result.success) {
        showToast(result.message, 'success');
        loadBailarines();
    } else {
        showToast(result.message, 'error');
    }
}

// Init
console.log('[ADMIN] Inicializando dashboard...');
loadResumen();

document.getElementById('reporteMes').value = new Date().getMonth() + 1;
document.getElementById('reporteAnio').value = new Date().getFullYear();
</script>
