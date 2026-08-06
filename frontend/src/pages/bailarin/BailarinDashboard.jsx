import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Search, X, Loader2, CheckCircle2, ClipboardList } from 'lucide-react';
import { api } from '../../api/client';
import { EmptyState } from '../../components/ui';
import { formatDate, formatTime, getTodayStr } from '../../lib/format';

export default function BailarinDashboard() {
  const [fecha, setFecha] = useState(getTodayStr());
  const [applied, setApplied] = useState(null);

  const params = new URLSearchParams();
  if (applied) {
    const [y, m] = applied.split('-');
    params.set('mes', String(parseInt(m, 10)));
    params.set('anio', y);
  }
  const query = params.toString();

  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['asistencia-mia', query],
    queryFn: () => api(`asistencia${query ? `?${query}` : ''}`),
  });

  const rows = data?.data || [];

  function handleSearch(e) {
    e.preventDefault();
    setApplied(fecha || null);
  }

  function handleClear() {
    setFecha(getTodayStr());
    setApplied(null);
  }

  return (
    <>
      <h2 style={{ marginBottom: 20 }}><ClipboardList size={20} /> Mi Historial de Asistencia</h2>

      <form className="glass-card filter-bar" onSubmit={handleSearch} style={{ marginBottom: 20 }}>
        <div className="form-group">
          <label htmlFor="filterDate">Mes</label>
          <input id="filterDate" type="month" className="form-input" value={fecha} onChange={(e) => setFecha(e.target.value)} />
        </div>
        <button className="btn btn-primary btn-sm" type="submit"><Search size={16} /></button>
        <button className="btn btn-secondary btn-sm" type="button" onClick={handleClear}><X size={16} /></button>
      </form>

      {isLoading || isFetching ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : rows.length === 0 ? (
        <EmptyState icon={CheckCircle2} message="Aún no has asistido a clases" />
      ) : (
        <div className="history-section">
          <h3>Clases asistidas</h3>
          {rows.map((r) => (
            <div key={r.id} className="glass-card glass-card-sm history-item">
              <div>
                <div className="history-class">{r.clase}</div>
                <div className="history-date">
                  {formatDate(r.fecha)} · {formatTime(r.hora_registro)}
                </div>
              </div>
              <CheckCircle2 size={22} style={{ color: 'var(--success)', flexShrink: 0 }} />
            </div>
          ))}
        </div>
      )}
    </>
  );
}
