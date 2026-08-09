import { useEffect, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import QRCode from 'qrcode';
import { CalendarDays, ArrowRight, CheckCircle2, Star, Loader2, QrCode } from 'lucide-react';
import { api } from '../../api/client';
import { useToast } from '../../stores/toast';
import { formatDate, formatTime, generateToken, getCurrentDayNumber, getDayName } from '../../lib/format';
import { useAuth } from '../../stores/auth';

const DAY_NAMES = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const DAY_SHORT = ['', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

function getQrSize() {
  const vw = window.innerWidth;
  if (vw <= 360) return 150;
  if (vw <= 480) return 180;
  return 220;
}

function buildQrUrl(encoded) {
  const base = new URL(`${import.meta.env.BASE_URL}registro-qr`, window.location.origin);
  base.searchParams.set('token', encoded);
  return base.toString();
}

export default function QrDia() {
  const toast = useToast();
  const { user } = useAuth();
  const [day, setDay] = useState(getCurrentDayNumber());
  const [index, setIndex] = useState(0);
  const [finished, setFinished] = useState(false);
  const [qrDataUrl, setQrDataUrl] = useState('');

  const { data, isLoading } = useQuery({
    queryKey: ['clases-por-dia', day],
    queryFn: () => api(`horarios/clases-por-dia?dia=${day}`),
  });

  const clases = data?.data || [];
  const dayData = {
    dia: data?.dia ?? day,
    dia_nombre: data?.dia_nombre ?? DAY_NAMES[day],
    fecha: data?.fecha,
    total: data?.total ?? clases.length,
  };

  const current = clases[index];

  // Generate QR whenever current class changes
  useEffect(() => {
    if (current && dayData.fecha) {
      const raw = {
        c: current.clase_nombre,
        f: dayData.fecha,
        h: current.hora_inicio,
        t: generateToken(),
        i: current.id,
      };
      const encoded = btoa(JSON.stringify(raw));
      const url = buildQrUrl(encoded);
      QRCode.toDataURL(url, { width: getQrSize(), margin: 1, color: { dark: '#06080D', light: '#ffffff' } })
        .then((d) => setQrDataUrl(d));
    }
  }, [current, dayData.fecha]);

  function handleDaySelect(d) {
    setDay(d);
    setIndex(0);
    setFinished(false);
  }

  function nextClass() {
    if (index < clases.length - 1) {
      setIndex((i) => i + 1);
    }
  }

  function finishDay() {
    setFinished(true);
    toast.success('¡Día finalizado! Todas las clases han sido generadas.');
  }

  if (isLoading) {
    return <div className="empty-state"><Loader2 size={40} className="spin" /></div>;
  }

  return (
    <>
      <div className="main-header">
        <h1>QR del Día</h1>
        <p id="dayStatus">
          {clases.length > 0 && !finished
            ? `Clase ${index + 1} de ${clases.length} - ${dayData.dia_nombre} ${dayData.fecha}`
            : `${dayData.dia_nombre} ${dayData.fecha}`}
        </p>
      </div>

      <div className="day-selector">
        {[1, 2, 3, 4, 5, 6, 7].map((d) => (
          <button
            key={d}
            className={`day-pill ${d === day ? 'active' : ''}`}
            onClick={() => handleDaySelect(d)}
          >
            {DAY_NAMES[d]}
          </button>
        ))}
      </div>

      {clases.length === 0 ? (
        <div className="qr-empty" style={{ padding: '80px 20px' }}>
          <div className="empty-icon"><CalendarDays size={56} /></div>
          <h2 style={{ marginBottom: 12 }}>No hay clases hoy</h2>
          <p>No hay clases programadas para {DAY_NAMES[day]}.</p>
          <p style={{ marginTop: 8, fontSize: '0.85rem', color: 'var(--text-muted)' }}>
            Ve a Horarios para configurar el horario semanal.
          </p>
        </div>
      ) : finished ? (
        <div className="glass-card" style={{ textAlign: 'center', padding: '60px 20px' }}>
          <div style={{ fontSize: '4rem', marginBottom: 16 }}><Star size={56} /></div>
          <h2>¡Día Completado!</h2>
          <p style={{ color: 'var(--text-secondary)', marginTop: 8 }}>
            {dayData.dia_nombre} {dayData.fecha} - {clases.length} clases
          </p>
          <div style={{ marginTop: 24 }}>
            <button className="btn btn-primary" onClick={() => { setFinished(false); setIndex(0); }}>
              Generar otro día
            </button>
          </div>
        </div>
      ) : (
        <>
          <div className="qr-date">{dayData.dia_nombre}, {formatDate(dayData.fecha)}</div>
          <div className="glass-card qr-card">
            <div className="qr-class-name">{current?.clase_nombre}</div>
            <div className="qr-class-time">
              {formatTime(current?.hora_inicio)} - {formatTime(current?.hora_fin)}
            </div>
            <div className="qr-container">
              {qrDataUrl && <img src={qrDataUrl} alt="Código QR de asistencia" width={getQrSize()} height={getQrSize()} />}
            </div>
            <div className="qr-progress">
              Clase {index + 1} de {clases.length}
              <div className="progress-bar">
                <div className="progress-bar-fill" style={{ width: `${Math.round(((index + 1) / clases.length) * 100)}%` }}></div>
              </div>
            </div>
          </div>
          <div className="qr-nav-btn">
            {index < clases.length - 1 ? (
              <button className="btn btn-primary" onClick={nextClass}>
                Siguiente Clase <ArrowRight size={18} />
              </button>
            ) : (
              <button className="btn btn-success" onClick={finishDay}>
                <CheckCircle2 size={18} /> Finalizar Día
              </button>
            )}
          </div>
        </>
      )}
    </>
  );
}
