import { useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Eye, EyeOff, User, Lock, Loader2, Music } from 'lucide-react';
import { useAuth } from '../stores/auth';
import { useToast } from '../stores/toast';
import { LOGO } from '../lib/assets';
import '../styles/login.css';

const CONFIG = {
  particles: {
    count: 55,
    colors: ['rgba(7,176,242,', 'rgba(7,199,242,', 'rgba(107,217,242,'],
    minSize: 1,
    maxSize: 3.2,
    minSpeed: 0.12,
    maxSpeed: 0.5,
    maxOpacity: 0.6,
  },
  waves: { amplitude: 46, frequency: 0.0045, points: 90 },
};

function rand(min, max) {
  return Math.random() * (max - min) + min;
}

function useLoginScene() {
  const canvasRef = useRef(null);
  const wavesRef = useRef(null);
  const glowRef = useRef(null);
  const blobsRef = useRef([]);

  useEffect(() => {
    const isCoarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
    let rafId = null;

    // Aurora blobs
    const blobs = blobsRef.current.filter(Boolean);
    const base = [
      { x: 0.86, y: 0.1 },
      { x: 0.08, y: 0.92 },
      { x: 0.6, y: 0.48 },
    ];
    const state = blobs.map((el, i) => {
      const b = base[i % base.length];
      return { el, x: b.x, y: b.y, vx: rand(-0.00035, 0.00035), vy: rand(-0.00035, 0.00035), t: 0 };
    });
    const stepAurora = () => {
      state.forEach((s) => {
        s.t += 0.016;
        s.x += s.vx + Math.sin(s.t * 0.4) * 0.0004;
        s.y += s.vy + Math.cos(s.t * 0.33) * 0.0004;
        if (s.x < 0.1 || s.x > 0.9) s.vx *= -1;
        if (s.y < 0.05 || s.y > 0.95) s.vy *= -1;
        s.el.style.transform = `translate(${s.x * 100 - 50}%, ${s.y * 100 - 50}%) scale(${1 + Math.sin(s.t * 0.5) * 0.12})`;
      });
      rafId = requestAnimationFrame(stepAurora);
    };
    if (blobs.length) rafId = requestAnimationFrame(stepAurora);

    const canvas = canvasRef.current;
    const svg = wavesRef.current;
    const glow = glowRef.current;

    let W = 0, H = 0, t = 0, running = true;
    const PR = window.devicePixelRatio || 1;

    // Particles canvas
    let ctx = null;
    let particles = [];
    if (canvas) {
      ctx = canvas.getContext('2d');
      const build = () => {
        let count = CONFIG.particles.count;
        if (W < 500) count = Math.round(count * 0.6);
        particles = [];
        for (let i = 0; i < count; i++) {
          particles.push({
            x: Math.random() * W,
            y: Math.random() * H,
            size: rand(CONFIG.particles.minSize, CONFIG.particles.maxSize),
            vx: rand(-CONFIG.particles.maxSpeed, CONFIG.particles.maxSpeed),
            vy: rand(-CONFIG.particles.maxSpeed, CONFIG.particles.maxSpeed),
            color: CONFIG.particles.colors[i % 3],
            opacity: rand(0.2, CONFIG.particles.maxOpacity),
          });
        }
      };
      const resize = () => {
        const parent = canvas.parentElement;
        W = parent.clientWidth;
        H = parent.clientHeight;
        canvas.width = W * PR;
        canvas.height = H * PR;
        canvas.style.width = W + 'px';
        canvas.style.height = H + 'px';
        ctx.setTransform(PR, 0, 0, PR, 0, 0);
        build();
      };
      const debounceRaf = (fn) => {
        let pending = false;
        return () => {
          if (pending) return;
          pending = true;
          requestAnimationFrame(() => {
            pending = false;
            fn();
          });
        };
      };
      const stepParticles = () => {
        if (!running) return;
        ctx.clearRect(0, 0, W, H);
        particles.forEach((p) => {
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
        rafId = requestAnimationFrame(stepParticles);
      };
      window.addEventListener('resize', debounceRaf(resize));
      resize();
      rafId = requestAnimationFrame(stepParticles);
    }

    // SVG waves
    let path1, path2;
    if (svg) {
      const NS = 'http://www.w3.org/2000/svg';
      path1 = document.createElementNS(NS, 'path');
      path2 = document.createElementNS(NS, 'path');
      path1.setAttribute('fill', 'rgba(7,176,242,0.35)');
      path2.setAttribute('fill', 'rgba(107,217,242,0.18)');
      svg.appendChild(path1);
      svg.appendChild(path2);
      const buildPath = (offset, ampFactor, speed) => {
        let d = `M0 ${H}`;
        const stepX = W / CONFIG.waves.points;
        for (let i = 0; i <= CONFIG.waves.points; i++) {
          const x = i * stepX;
          const y =
            H * 0.45 +
            Math.sin(x * CONFIG.waves.frequency + offset + t * speed) * CONFIG.waves.amplitude * ampFactor +
            Math.sin(x * CONFIG.waves.frequency * 2.4 - offset) * CONFIG.waves.amplitude * 0.35 * ampFactor;
          d += ` L${x.toFixed(1)} ${y.toFixed(1)}`;
        }
        d += ` L${W} ${H} Z`;
        return d;
      };
      const stepWaves = () => {
        t += 0.018;
        path1.setAttribute('d', buildPath(0, 1, 0.02));
        path2.setAttribute('d', buildPath(Math.PI * 0.6, 0.6, 0.03));
        rafId = requestAnimationFrame(stepWaves);
      };
      rafId = requestAnimationFrame(stepWaves);
    }

    // Mouse glow (fine pointers only)
    let tx = window.innerWidth / 2, ty = window.innerHeight / 2;
    let cx = tx, cy = ty, targetOpacity = 0, active = false;
    const onMove = (e) => {
      tx = e.clientX;
      ty = e.clientY;
      if (!active) {
        active = true;
        rafId = requestAnimationFrame(loopGlow);
      }
    };
    const loopGlow = () => {
      cx += (tx - cx) * 0.12;
      cy += (ty - cy) * 0.12;
      const dist = Math.abs(tx - cx) + Math.abs(ty - cy);
      if (dist < 0.5 && !active) targetOpacity = 0;
      else if (active) targetOpacity = 1;
      if (glow) {
        glow.style.opacity = String(targetOpacity);
        glow.style.transform = `translate(${cx - 260}px, ${cy - 260}px)`;
      }
      if (targetOpacity > 0.01 || active) rafId = requestAnimationFrame(loopGlow);
    };
    if (glow && !isCoarse) {
      window.addEventListener('mousemove', onMove, { passive: true });
      rafId = requestAnimationFrame(loopGlow);
    }

    const onVis = () => {
      running = !document.hidden;
      if (running && canvas && ctx) rafId = requestAnimationFrame(stepParticles);
    };
    document.addEventListener('visibilitychange', onVis);

    return () => {
      if (rafId) cancelAnimationFrame(rafId);
      window.removeEventListener('mousemove', onMove);
      document.removeEventListener('visibilitychange', onVis);
      if (svg && path1 && path2) {
        svg.removeChild(path1);
        svg.removeChild(path2);
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return { canvasRef, wavesRef, glowRef, blobsRef };
}

export default function Login() {
  const login = useAuth((s) => s.login);
  const toast = useToast();
  const navigate = useNavigate();
  const { canvasRef, wavesRef, glowRef, blobsRef } = useLoginScene();

  const [cedula, setCedula] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(true);
  const [showPwd, setShowPwd] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    if (!cedula.trim() || !password) {
      toast.warning('Completa todos los campos');
      return;
    }
    setSubmitting(true);
    const result = await login(cedula.trim(), password);
    setSubmitting(false);
    if (result.success) {
      toast.success(`¡Bienvenido, ${result.data.nombre}!`);
      setTimeout(() => navigate(result.data.rol === 'admin' ? '/admin' : '/bailarin'), 800);
    } else {
      toast.error(result.message || 'Error al iniciar sesión');
    }
  }

  return (
    <div className="login-scene">
      <div className="scene-bg">
        <div className="scene-gradient"></div>
        <div className="scene-noise"></div>
        <div className="scene-aurora">
          <div ref={(el) => (blobsRef.current[0] = el)} data-blob="1" className="aurora-blob"></div>
          <div ref={(el) => (blobsRef.current[1] = el)} data-blob="2" className="aurora-blob"></div>
          <div ref={(el) => (blobsRef.current[2] = el)} data-blob="3" className="aurora-blob"></div>
        </div>
        <svg ref={wavesRef} className="scene-waves"></svg>
        <canvas ref={canvasRef} className="scene-particles"></canvas>
        <div ref={glowRef} className="scene-glow"></div>
      </div>

      <main className="login-main">
        <div className="login-card">
          <div className="card-reflection"></div>
          <div className="card-noise"></div>

          <div className="brand">
            <div className="brand-logo">
              <img src={LOGO} alt="QR Tambo" />
            </div>
            <h1 className="brand-title">QR <span>TAMBO</span></h1>
            <p className="brand-subtitle">Tambo Dance Company</p>
            <p className="brand-tagline">Sistema de Asistencia · Clases de Baile</p>
          </div>

          <form className="login-form" onSubmit={handleSubmit}>
            <div className="input-group">
              <label htmlFor="cedula">Cédula</label>
              <div className="input-control">
                <span className="input-icon"><User size={16} /></span>
                <input
                  id="cedula"
                  className="form-input"
                  type="text"
                  autoComplete="username"
                  placeholder="Ingresa tu cédula"
                  value={cedula}
                  onChange={(e) => setCedula(e.target.value)}
                />
                <span className="input-focus"></span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="password">Contraseña</label>
              <div className="input-control">
                <span className="input-icon"><Lock size={16} /></span>
                <input
                  id="password"
                  className="form-input"
                  type={showPwd ? 'text' : 'password'}
                  autoComplete="current-password"
                  placeholder="Ingresa tu contraseña"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
                <button type="button" className="input-toggle" onClick={() => setShowPwd((s) => !s)} aria-label="Mostrar contraseña">
                  {showPwd ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
                <span className="input-focus"></span>
              </div>
            </div>

            <div className="login-options">
              <label className="remember-me">
                <input type="checkbox" checked={remember} onChange={(e) => setRemember(e.target.checked)} />
                Recordarme
              </label>
            </div>

            <button className="login-btn" type="submit" disabled={submitting}>
              <span className="btn-shine"></span>
              <span className="btn-label">
                {submitting ? <Loader2 size={18} className="spin" /> : <Music size={18} />}
                {submitting ? 'Ingresando...' : 'Iniciar Sesión'}
              </span>
            </button>
          </form>

          <div className="login-footer">
            ¿No tienes cuenta? <Link to="/register">Regístrate aquí</Link>
          </div>
        </div>
      </main>
    </div>
  );
}
