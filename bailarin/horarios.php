<?php
require_once __DIR__ . '/../config/init.php';
if (!isLoggedIn() || $_SESSION['rol'] !== 'bailarin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Horarios';
$basePath = '../';
include '../includes/header.php';
?>
<div class="page-wrapper">
    <main class="bailarin-main" style="max-width:500px;margin:0 auto;padding:20px 16px;padding-top:16px;">
        <!-- Day Slider -->
        <div class="glass-card" style="padding:12px;margin-bottom:20px;">
            <div class="day-slider" id="daySlider">
                <div class="day-slider-indicator" id="dayIndicator"></div>
                <button class="day-slider-btn active" data-day="1" onclick="switchDay(this, 0)">Lun</button>
                <button class="day-slider-btn" data-day="2" onclick="switchDay(this, 1)">Mar</button>
                <button class="day-slider-btn" data-day="3" onclick="switchDay(this, 2)">Mié</button>
                <button class="day-slider-btn" data-day="4" onclick="switchDay(this, 3)">Jue</button>
                <button class="day-slider-btn" data-day="5" onclick="switchDay(this, 4)">Vie</button>
                <button class="day-slider-btn" data-day="6" onclick="switchDay(this, 5)">Sáb</button>
            </div>
        </div>

        <!-- Schedule Timeline -->
        <div class="timeline-container" id="scheduleContainer">
            <div style="text-align:center;padding:40px;">
                <div class="spinner" style="margin:0 auto;"></div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
console.log('[HORARIOS] Página cargada');
let currentDayIdx = (new Date().getDay() || 7) - 1; // 0=Mon...5=Sat
if (currentDayIdx > 5) currentDayIdx = 0;

function switchDay(el, idx) {
    const indicator = document.getElementById('dayIndicator');
    const slider = document.getElementById('daySlider');
    const step = slider.offsetWidth / 6;
    indicator.style.transform = `translateX(${idx * step}px)`;

    document.querySelectorAll('.day-slider-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    currentDayIdx = idx;
    loadDay(parseInt(el.dataset.day));
}

async function loadDay(day) {
    const container = document.getElementById('scheduleContainer');
    container.innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner" style="margin:0 auto;"></div></div>';

    const result = await api('../api/horarios/list.php');
    if (!result.success || !result.data.length) {
        container.innerHTML = '<div class="empty-state" style="padding:40px 16px;"><span class="empty-icon"><i class="fas fa-calendar-alt"></i></span><p>No hay horarios disponibles</p></div>';
        return;
    }

    const daySchedules = result.data.filter(h => h.dia_semana == day);
    if (!daySchedules.length) {
        container.innerHTML = '<div class="empty-state" style="padding:40px 16px;"><span class="empty-icon"><i class="fas fa-calendar-alt"></i></span><p>No hay clases para este día</p></div>';
        return;
    }

    daySchedules.sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));

    container.innerHTML = daySchedules.map((h, i) => `
        <div class="timeline-card" style="animation-delay:${i * 0.08}s;">
            <div class="timeline-time">
                ${formatTime(h.hora_inicio)}
                <div class="timeline-dot"></div>
            </div>
            <div class="timeline-body">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <div>
                        <h3>${h.clase_nombre}</h3>
                        <div class="meta">
                            <span><i class="far fa-clock"></i> ${formatTime(h.hora_inicio)} - ${formatTime(h.hora_fin)}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Recalcular indicador al redimensionar
let bailarinResizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(bailarinResizeTimer);
    bailarinResizeTimer = setTimeout(function() {
        const slider = document.getElementById('daySlider');
        if (!slider) return;
        const step = slider.offsetWidth / 6;
        const active = slider.querySelector('.day-slider-btn.active');
        if (active) {
            const idx = Array.from(slider.querySelectorAll('.day-slider-btn')).indexOf(active);
            document.getElementById('dayIndicator').style.transform = `translateX(${idx * step}px)`;
        }
    }, 150);
});

// Init
const initialBtn = document.querySelectorAll('.day-slider-btn')[currentDayIdx];
if (initialBtn) {
    const slider = document.getElementById('daySlider');
    const step = slider.offsetWidth / 6;
    document.getElementById('dayIndicator').style.transform = `translateX(${currentDayIdx * step}px)`;
    initialBtn.classList.add('active');
    loadDay(currentDayIdx + 1);
}
</script>
