/**
 * QR Tambo — Login Premium
 * Escena: aurora, ondas SVG, partículas y mouse glow.
 * Lógica de login: conserva exactamente las peticiones a api/auth/login.php.
 */
(function () {
    'use strict';

    /* ============================================================
       CONFIG
       ============================================================ */
    var CONFIG = {
        particles: {
            count: 55,
            colors: ['rgba(7,176,242,', 'rgba(7,199,242,', 'rgba(107,217,242,'],
            minSize: 1,
            maxSize: 3.2,
            minSpeed: 0.12,
            maxSpeed: 0.5,
            maxOpacity: 0.6
        },
        waves: {
            amplitude: 46,
            frequency: 0.0045,
            points: 90
        }
    };

    var isCoarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
    var rafId = null;

    /* ============================================================
       UTILS
       ============================================================ */
    function rand(min, max) {
        return Math.random() * (max - min) + min;
    }

    function debounceRaf(fn) {
        var pending = false;
        return function () {
            if (pending) return;
            pending = true;
            requestAnimationFrame(function () {
                pending = false;
                fn.apply(this, arguments);
            });
        };
    }

    /* ============================================================
       AURORA (desplazamiento lento y orgánico)
       ============================================================ */
    function initAurora() {
        var blobs = Array.prototype.slice.call(document.querySelectorAll('.aurora-blob'));
        if (!blobs.length) return;

        var base = [
            { x: 0.86, y: 0.1 },
            { x: 0.08, y: 0.92 },
            { x: 0.6, y: 0.48 }
        ];

        var state = blobs.map(function (blob, i) {
            var b = base[i % base.length];
            return {
                el: blob,
                x: b.x,
                y: b.y,
                vx: rand(-0.00035, 0.00035),
                vy: rand(-0.00035, 0.00035),
                t: 0
            };
        });

        function step() {
            state.forEach(function (s) {
                s.t += 0.016;
                s.x += s.vx + Math.sin(s.t * 0.4) * 0.0004;
                s.y += s.vy + Math.cos(s.t * 0.33) * 0.0004;
                if (s.x < 0.1 || s.x > 0.9) s.vx *= -1;
                if (s.y < 0.05 || s.y > 0.95) s.vy *= -1;
                s.el.style.transform =
                    'translate(' + (s.x * 100 - 50) + '%, ' + (s.y * 100 - 50) + '%) scale(' + (1 + Math.sin(s.t * 0.5) * 0.12) + ')';
            });
            rafId = requestAnimationFrame(step);
        }

        rafId = requestAnimationFrame(step);
    }

    /* ============================================================
       PARTICLES (canvas)
       ============================================================ */
    function initParticles() {
        var canvas = document.getElementById('particlesCanvas');
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        var W = 0, H = 0, particles = [];
        var PR = window.devicePixelRatio || 1;
        var running = true;

        function resize() {
            var parent = canvas.parentElement;
            W = parent.clientWidth;
            H = parent.clientHeight;
            canvas.width = W * PR;
            canvas.height = H * PR;
            canvas.style.width = W + 'px';
            canvas.style.height = H + 'px';
            ctx.setTransform(PR, 0, 0, PR, 0, 0);
            build();
        }

        function build() {
            var count = CONFIG.particles.count;
            if (W < 500) count = Math.round(count * 0.6);
            particles = [];
            for (var i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * W,
                    y: Math.random() * H,
                    size: rand(CONFIG.particles.minSize, CONFIG.particles.maxSize),
                    vx: rand(-CONFIG.particles.maxSpeed, CONFIG.particles.maxSpeed),
                    vy: rand(-CONFIG.particles.maxSpeed, CONFIG.particles.maxSpeed),
                    color: CONFIG.particles.colors[i % 3],
                    opacity: rand(0.2, CONFIG.particles.maxOpacity)
                });
            }
        }

        function step() {
            if (!running) return;
            ctx.clearRect(0, 0, W, H);
            particles.forEach(function (p) {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < -10) p.x = W + 10;
                if (p.x > W + 10) p.x = -10;
                if (p.y < -10) p.y = H + 10;
                if (p.y > H + 10) p.y = -10;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fillStyle = p.color + p.opacity + ')';
                ctx.fill();
            });
            rafId = requestAnimationFrame(step);
        }

        var onResize = debounceRaf(resize);
        window.addEventListener('resize', onResize);
        resize();
        rafId = requestAnimationFrame(step);

        // Pausar cuando no se ve (ahorro)
        document.addEventListener('visibilitychange', function () {
            running = !document.hidden;
            if (running) rafId = requestAnimationFrame(step);
        });
    }

    /* ============================================================
       WAVES (SVG matemático)
       ============================================================ */
    function initWaves() {
        var svg = document.getElementById('wavesLayer');
        if (!svg) return;

        var NS = 'http://www.w3.org/2000/svg';
        var W = 0, H = 0;
        var path1 = document.createElementNS(NS, 'path');
        var path2 = document.createElementNS(NS, 'path');
        var t = 0;

        path1.setAttribute('fill', 'rgba(7,176,242,0.35)');
        path2.setAttribute('fill', 'rgba(107,217,242,0.18)');
        svg.appendChild(path1);
        svg.appendChild(path2);

        function buildPath(offset, ampFactor, speed) {
            var d = 'M0 ' + H;
            var stepX = W / CONFIG.waves.points;
            for (var i = 0; i <= CONFIG.waves.points; i++) {
                var x = i * stepX;
                var y = H * 0.45
                    + Math.sin(x * CONFIG.waves.frequency + offset + t * speed) * CONFIG.waves.amplitude * ampFactor
                    + Math.sin(x * CONFIG.waves.frequency * 2.4 - offset) * CONFIG.waves.amplitude * 0.35 * ampFactor;
                d += ' L' + x.toFixed(1) + ' ' + y.toFixed(1);
            }
            d += ' L' + W + ' ' + H + ' Z';
            return d;
        }

        function resize() {
            var parent = svg.parentElement;
            W = parent.clientWidth;
            H = parent.clientHeight;
            svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
            svg.setAttribute('preserveAspectRatio', 'none');
            svg.style.width = W + 'px';
            svg.style.height = H + 'px';
        }

        function step() {
            t += 0.018;
            path1.setAttribute('d', buildPath(0, 1, 0.02));
            path2.setAttribute('d', buildPath(Math.PI * 0.6, 0.6, 0.03));
            rafId = requestAnimationFrame(step);
        }

        window.addEventListener('resize', debounceRaf(resize));
        resize();
        rafId = requestAnimationFrame(step);
    }

    /* ============================================================
       MOUSE GLOW (solo punteros finos)
       ============================================================ */
    function initMouseGlow() {
        if (isCoarse) return;
        var glow = document.getElementById('mouseGlow');
        if (!glow) return;

        var tx = window.innerWidth / 2;
        var ty = window.innerHeight / 2;
        var cx = tx, cy = ty;
        var targetOpacity = 0;
        var active = false;

        function onMove(e) {
            tx = e.clientX;
            ty = e.clientY;
            if (!active) {
                active = true;
                rafId = requestAnimationFrame(loop);
            }
        }

        function onLeave() {
            active = false;
            targetOpacity = 0;
            rafId = requestAnimationFrame(loop);
        }

        function loop() {
            cx += (tx - cx) * 0.12;
            cy += (ty - cy) * 0.12;
            var dist = Math.abs(tx - cx) + Math.abs(ty - cy);
            if (dist < 0.5 && !active) targetOpacity = 0;
            else if (active) targetOpacity = 1;

            glow.style.opacity = String(targetOpacity);
            glow.style.transform =
                'translate(' + (cx - 260) + 'px, ' + (cy - 260) + 'px)';

            if (targetOpacity > 0.01 || active) {
                rafId = requestAnimationFrame(loop);
            }
        }

        window.addEventListener('mousemove', onMove, { passive: true });
        document.addEventListener('mouseleave', onLeave);
        // Arrancar siempre un ciclo suave de entrada
        rafId = requestAnimationFrame(loop);
    }

    /* ============================================================
       BUTTON: ripple + label
       ============================================================ */
    function initButton() {
        var btn = document.getElementById('loginBtn');
        if (!btn) return;

        btn.addEventListener('pointerdown', function (e) {
            var rect = btn.getBoundingClientRect();
            var d = Math.max(rect.width, rect.height);
            var span = document.createElement('span');
            span.className = 'ripple';
            var x = e.clientX - rect.left - d / 2;
            var y = e.clientY - rect.top - d / 2;
            span.style.width = d + 'px';
            span.style.height = d + 'px';
            span.style.left = x + 'px';
            span.style.top = y + 'px';
            btn.appendChild(span);
            span.addEventListener('animationend', function () {
                if (span.parentNode) span.parentNode.removeChild(span);
            });
        });
    }

    /* ============================================================
       PASSWORD TOGGLE
       ============================================================ */
    function initPasswordToggle() {
        var toggle = document.getElementById('passwordToggle');
        var input = document.getElementById('password');
        if (!toggle || !input) return;

        toggle.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            toggle.innerHTML = show
                ? '<i class="fas fa-eye-slash"></i>'
                : '<i class="fas fa-eye"></i>';
        });
    }

    /* ============================================================
       LOGIN (petición preservada tal cual)
       ============================================================ */
    function initLoginForm() {
        var form = document.getElementById('loginForm');
        if (!form) return;

        var btn = document.getElementById('loginBtn');
        var btnLabel = btn ? btn.querySelector('.btn-label') : null;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            var cedula = document.getElementById('cedula').value.trim();
            var password = document.getElementById('password').value;
            var rememberMe = document.getElementById('rememberMe');

            if (!cedula || !password) {
                showToast('Completa todos los campos', 'warning');
                return;
            }

            if (btnLabel) {
                btnLabel.innerHTML = 'Ingresando <i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;
            }

            var result = await api('api/auth/login.php', 'POST', {
                cedula: cedula,
                password: password,
                recordarme: rememberMe ? rememberMe.checked : true
            });

            if (result.success) {
                showToast('¡Bienvenido, ' + result.data.nombre + '!', 'success');
                setTimeout(function () {
                    if (result.data.rol === 'admin') {
                        window.location.href = 'admin/dashboard.php';
                    } else {
                        window.location.href = 'bailarin/escanear.php';
                    }
                }, 800);
            } else {
                showToast(result.message || 'Error al iniciar sesión', 'error');
                if (btnLabel) {
                    btnLabel.textContent = 'Iniciar Sesión';
                    btn.disabled = false;
                }
            }
        });
    }

    /* ============================================================
       APP
       ============================================================ */
    function app() {
        initAurora();
        initParticles();
        initWaves();
        initMouseGlow();
        initButton();
        initPasswordToggle();
        initLoginForm();

        window.addEventListener('pagehide', function () {
            if (rafId) cancelAnimationFrame(rafId);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', app);
    } else {
        app();
    }
})();
