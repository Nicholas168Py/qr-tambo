import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Search, X, Loader2, CheckCircle2, ClipboardList, ChevronLeft, ChevronRight } from 'lucide-react';
import { api } from '../../api/client';
import { EmptyState } from '../../components/ui';
import { formatDate, formatTime, getTodayStr } from '../../lib/format';

export default function BailarinAsistencias() {
  const [fecha, setFecha] = useState('');
  const [applied, setApplied] = useState({});
  const [page, setPage] = useState(1);
  const [perPage] = useState(10);

  function buildParams() {
    const p = new URLSearchParams();
    p.set('page', page);
    p.set('per_page', perPage);
    if (applied.mes) p.set('mes', applied.mes);
    if (applied.anio) p.set('anio', applied.anio);
    return p.toString();
  }

  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['asistencia-mia', applied, page],
    queryFn: () => api(`asistencia?${buildParams()}`),
  });

  const rows = data?.data || [];
  const pagination = data?.pagination || { current_page: 1, last_page: 1, per_page: 10, total: 0 };

  function handleSearch(e) {
    e.preventDefault();
    const next = {};
    if (fecha) {
      const [y, m] = fecha.split('-');
      next.anio = y;
      next.mes = String(parseInt(m, 10));
    }
    setApplied(next);
    setPage(1);
  }

  function handleClear() {
    setFecha('');
    setApplied({});
    setPage(1);
  }

  function handlePageChange(newPage) {
    if (newPage >= 1 && newPage <= pagination.last_page) {
      setPage(newPage);
    }
  }

  return (
    <>
      <h2 style={{ marginBottom: 20 }}><ClipboardList size={20} /> Mis Asistencias</h2>

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
        <>
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

          {pagination.last_page > 1 && (
            <div className="pagination" style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '8px', marginTop: '16px', flexWrap: 'wrap' }}>
              <button
                className="btn btn-secondary btn-sm"
                onClick={() => handlePageChange(pagination.current_page - 1)}
                disabled={pagination.current_page <= 1}
              >
                <ChevronLeft size={16} /> Anterior
              </button>
              <span style={{ padding: '0 12px', color: 'var(--text-secondary)' }}>
                Página {pagination.current_page} de {pagination.last_page} ({pagination.total} registros)
              </span>
              <button
                className="btn btn-secondary btn-sm"
                onClick={() => handlePageChange(pagination.current_page + 1)}
                disabled={pagination.current_page >= pagination.last_page}
              >
                Siguiente <ChevronRight size={16} />
              </button>
            </div>
          )}
        </>
      )}
    </>
  );
}