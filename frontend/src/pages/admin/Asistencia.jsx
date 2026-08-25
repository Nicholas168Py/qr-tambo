import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Search, X, Loader2, CheckCircle2, ChevronLeft, ChevronRight } from 'lucide-react';
import { api } from '../../api/client';
import { EmptyState } from '../../components/ui';
import { formatDate, formatTime, getTodayStr } from '../../lib/format';

export default function Asistencia() {
  const [fecha, setFecha] = useState('');
  const [cedula, setCedula] = useState('');
  const [clase, setClase] = useState('');
  const [page, setPage] = useState(1);
  const [applied, setApplied] = useState({});

  function buildParams() {
    const p = new URLSearchParams();
    if (applied.mes) p.set('mes', applied.mes);
    if (applied.anio) p.set('anio', applied.anio);
    if (applied.cedula) p.set('cedula', applied.cedula);
    if (applied.clase) p.set('clase', applied.clase);
    p.set('page', String(page));
    p.set('per_page', '10');
    return p.toString();
  }

  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['asistencia-admin', applied, page],
    queryFn: () => api(`asistencia?${buildParams()}`),
  });

  const rows = data?.data || [];
  const pagination = data?.pagination || { current_page: 1, last_page: 1, per_page: 10, total: 0 };

  function handleFilter(e) {
    e.preventDefault();
    const next = {};
    if (fecha) {
      const [y, m] = fecha.split('-');
      next.anio = y;
      next.mes = String(parseInt(m, 10));
    }
    if (cedula.trim()) next.cedula = cedula.trim();
    if (clase.trim()) next.clase = clase.trim();
    setApplied(next);
    setPage(1);
  }

  function handleClear() {
    setFecha('');
    setCedula('');
    setClase('');
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
      <div className="main-header">
        <h1>Asistencia</h1>
        <p>Consulta los registros de asistencia</p>
      </div>

      <form className="glass-card filter-bar" onSubmit={handleFilter}>
        <div className="form-group">
          <label htmlFor="filtroFecha">Fecha (Mes/Año)</label>
          <input id="filtroFecha" type="month" className="form-input" value={fecha} onChange={(e) => setFecha(e.target.value)} />
        </div>
        <div className="form-group">
          <label htmlFor="filtroCedula">Cédula</label>
          <input id="filtroCedula" className="form-input" placeholder="Filtrar por cédula..." value={cedula} onChange={(e) => setCedula(e.target.value)} />
        </div>
        <div className="form-group">
          <label htmlFor="filtroClase">Clase</label>
          <input id="filtroClase" className="form-input" placeholder="Filtrar por clase..." value={clase} onChange={(e) => setClase(e.target.value)} />
        </div>
        <button className="btn btn-primary" type="submit"><Search size={18} /> Filtrar</button>
        <button className="btn btn-secondary" type="button" onClick={handleClear}><X size={18} /> Limpiar</button>
      </form>

      {isLoading || isFetching ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : rows.length === 0 ? (
        <EmptyState icon={CheckCircle2} message="No hay registros de asistencia" />
      ) : (
        <>
          <div className="table-container">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cédula</th>
                  <th>Nombre</th>
                  <th>Clase</th>
                  <th>Fecha</th>
                  <th>Hora</th>
                </tr>
              </thead>
              <tbody>
                {rows.map((r, i) => (
                  <tr key={r.id}>
                    <td>{(pagination.current_page - 1) * pagination.per_page + i + 1}</td>
                    <td>{r.cedula}</td>
                    <td>{r.nombre}</td>
                    <td>{r.clase}</td>
                    <td>{formatDate(r.fecha)}</td>
                    <td>{formatTime(r.hora_registro)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
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
