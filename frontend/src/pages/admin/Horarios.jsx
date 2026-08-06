import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Plus, Trash2, Loader2, CalendarDays, Clock, CalendarCheck } from 'lucide-react';
import { api } from '../../api/client';
import { useToast } from '../../stores/toast';
import { EmptyState } from '../../components/ui';
import { formatTime, getDayName } from '../../lib/format';

const DAYS_ORDER = [1, 2, 3, 4, 5, 6];
const ADMIN_DAY_NAMES = { 1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 5: 'Viernes', 6: 'Sábado' };
const ADMIN_DAY_SHORT = { 1: 'Lun', 2: 'Mar', 3: 'Mié', 4: 'Jue', 5: 'Vie', 6: 'Sáb' };

export default function Horarios() {
  const toast = useToast();
  const queryClient = useQueryClient();
  const [claseId, setClaseId] = useState('');
  const [dia, setDia] = useState('1');
  const [inicio, setInicio] = useState('');
  const [fin, setFin] = useState('');

  const clasesQ = useQuery({ queryKey: ['clases'], queryFn: () => api('clases') });
  const horariosQ = useQuery({ queryKey: ['horarios'], queryFn: () => api('horarios') });

  const clases = clasesQ.data?.data || [];
  const allHorarios = horariosQ.data?.data || [];

  const create = useMutation({
    mutationFn: (body) => api('horarios', 'POST', body),
    onSuccess: (res) => {
      if (res.success) {
        toast.success('Horario creado exitosamente');
        setClaseId('');
        setInicio('');
        setFin('');
        queryClient.invalidateQueries({ queryKey: ['horarios'] });
      } else {
        toast.error(res.message);
      }
    },
  });

  const remove = useMutation({
    mutationFn: (id) => api(`horarios/${id}`, 'DELETE'),
    onSuccess: (res) => {
      if (res.success) {
        toast.success('Horario eliminado');
        queryClient.invalidateQueries({ queryKey: ['horarios'] });
      } else {
        toast.error(res.message);
      }
    },
  });

  function handleCreate(e) {
    e.preventDefault();
    if (!claseId || !inicio || !fin) {
      toast.warning('Completa todos los campos');
      return;
    }
    create.mutate({ clase_id: parseInt(claseId, 10), dia_semana: parseInt(dia, 10), hora_inicio: inicio, hora_fin: fin });
  }

  function handleDelete(h) {
    if (window.confirm(`¿Eliminar el horario de "${h.clase_nombre}"?`)) {
      remove.mutate(h.id);
    }
  }

  // Weekly grid
  const timeSlots = [...new Set(allHorarios.map((h) => h.hora_inicio))].sort();
  const grid = {};
  allHorarios.forEach((h) => {
    if (!grid[h.hora_inicio]) grid[h.hora_inicio] = {};
    grid[h.hora_inicio][h.dia_semana] = h;
  });

  return (
    <>
      <div className="main-header">
        <h1>Horarios</h1>
        <p>Configura los horarios semanales de cada clase</p>
      </div>

      <form className="glass-card add-form" onSubmit={handleCreate}>
        <div className="form-group">
          <label htmlFor="horarioClase">Clase</label>
          <select id="horarioClase" className="form-input" value={claseId} onChange={(e) => setClaseId(e.target.value)}>
            <option value="">Seleccionar clase...</option>
            {clases.map((c) => (
              <option key={c.id} value={c.id}>{c.nombre}</option>
            ))}
          </select>
        </div>
        <div className="form-group">
          <label htmlFor="horarioDia">Día</label>
          <select id="horarioDia" className="form-input" value={dia} onChange={(e) => setDia(e.target.value)}>
            {DAYS_ORDER.map((d) => (
              <option key={d} value={d}>{ADMIN_DAY_NAMES[d]}</option>
            ))}
          </select>
        </div>
        <div className="form-group">
          <label htmlFor="horarioInicio">Inicio</label>
          <input id="horarioInicio" type="time" className="form-input" value={inicio} onChange={(e) => setInicio(e.target.value)} required />
        </div>
        <div className="form-group">
          <label htmlFor="horarioFin">Fin</label>
          <input id="horarioFin" type="time" className="form-input" value={fin} onChange={(e) => setFin(e.target.value)} required />
        </div>
        <button className="btn btn-primary" type="submit" disabled={create.isPending}>
          {create.isPending ? <Loader2 size={18} className="spin" /> : <Plus size={18} />}
          Agregar
        </button>
      </form>

      {horariosQ.isLoading ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : allHorarios.length === 0 ? (
        <EmptyState icon={CalendarDays} message="Aún no hay horarios configurados" />
      ) : (
        <>
          {/* Desktop weekly grid */}
          <div className="schedule-grid-wrapper" style={{ display: 'none' }}>
            <table className="schedule-grid">
              <thead>
                <tr>
                  <th>Hora</th>
                  {DAYS_ORDER.map((d) => <th key={d}>{ADMIN_DAY_NAMES[d]}</th>)}
                </tr>
              </thead>
              <tbody>
                {timeSlots.map((hora) => (
                  <tr key={hora}>
                    <td style={{ textAlign: 'right', paddingRight: 10, color: 'var(--text-muted)', fontSize: '0.78rem', whiteSpace: 'nowrap' }}>
                      {formatTime(hora)}
                    </td>
                    {DAYS_ORDER.map((diaN) => {
                      const h = grid[hora] && grid[hora][diaN];
                      return h ? (
                        <td key={diaN} className="slot-class" title={h.clase_nombre}>
                          <span className="class-name">{h.clase_nombre}</span>
                          <span className="class-time">{formatTime(h.hora_fin)}</span>
                          <button className="btn-sm-ghost" onClick={() => handleDelete(h)} title="Eliminar">
                            <Trash2 size={14} style={{ color: 'var(--error)' }} />
                          </button>
                        </td>
                      ) : (
                        <td key={diaN} className="slot-empty"><span>–</span></td>
                      );
                    })}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Mobile timeline per day */}
          <div className="day-slider" style={{ marginBottom: 20 }}>
            <div className="day-slider-indicator" style={{ transform: `translateX(${(dia - 1) * 100}%)` }}></div>
            {DAYS_ORDER.map((d) => (
              <button
                key={d}
                className={`day-slider-btn ${String(d) === String(dia) ? 'active' : ''}`}
                onClick={() => setDia(String(d))}
              >
                {ADMIN_DAY_SHORT[d]}
              </button>
            ))}
          </div>

          <div className="timeline-container">
            {allHorarios
              .filter((h) => h.dia_semana === parseInt(dia, 10))
              .sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio))
              .map((h, i) => (
                <div key={h.id} className="timeline-card" style={{ animationDelay: `${i * 0.08}s` }}>
                  <div className="timeline-time">
                    {formatTime(h.hora_inicio)}
                    <div className="timeline-dot"></div>
                  </div>
                  <div className="timeline-body">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 8 }}>
                      <div>
                        <h3>{h.clase_nombre}</h3>
                        <div className="meta">
                          <span><Clock size={13} /> {formatTime(h.hora_inicio)} - {formatTime(h.hora_fin)}</span>
                          <span><CalendarCheck size={13} /> {getDayName(h.dia_semana)}</span>
                        </div>
                      </div>
                      <button className="btn btn-danger btn-sm" onClick={() => handleDelete(h)} style={{ flexShrink: 0, padding: '6px 10px', fontSize: '0.78rem' }}>
                        <Trash2 size={14} />
                      </button>
                    </div>
                  </div>
                </div>
              ))}
          </div>
        </>
      )}
    </>
  );
}
