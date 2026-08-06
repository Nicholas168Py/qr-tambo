import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { CalendarDays, Clock, Loader2 } from 'lucide-react';
import { api } from '../../api/client';
import { EmptyState } from '../../components/ui';
import { formatTime, getCurrentDayNumber } from '../../lib/format';

const DAY_SHORT = ['', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const DAYS = [1, 2, 3, 4, 5, 6, 7];

export default function Horarios() {
  const [day, setDay] = useState(getCurrentDayNumber());

  const { data, isLoading } = useQuery({ queryKey: ['horarios'], queryFn: () => api('horarios') });
  const all = data?.data || [];

  const filtered = all
    .filter((h) => h.dia_semana === day)
    .sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));

  return (
    <>
      <h2 style={{ marginBottom: 8 }}>Horarios de Clases</h2>
      <p style={{ color: 'var(--text-secondary)', fontSize: '0.9rem', marginBottom: 20 }}>
        Consulta las clases programadas de la semana
      </p>

      <div className="day-slider" style={{ marginBottom: 24 }}>
        <div className="day-slider-indicator" style={{ transform: `translateX(${(day - 1) * 100}%)` }}></div>
        {DAYS.map((d) => (
          <button
            key={d}
            className={`day-slider-btn ${d === day ? 'active' : ''}`}
            onClick={() => setDay(d)}
          >
            {DAY_SHORT[d]}
          </button>
        ))}
      </div>

      {isLoading ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : all.length === 0 ? (
        <EmptyState icon={CalendarDays} message="No hay horarios disponibles" />
      ) : filtered.length === 0 ? (
        <EmptyState icon={CalendarDays} message="No hay clases para este día" />
      ) : (
        <div className="timeline-container">
          {filtered.map((h, i) => (
            <div key={h.id} className="timeline-card" style={{ animationDelay: `${i * 0.08}s` }}>
              <div className="timeline-time">
                {formatTime(h.hora_inicio)}
                <div className="timeline-dot"></div>
              </div>
              <div className="timeline-body">
                <h3>{h.clase_nombre}</h3>
                <div className="meta">
                  <span><Clock size={13} /> {formatTime(h.hora_inicio)} - {formatTime(h.hora_fin)}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </>
  );
}
