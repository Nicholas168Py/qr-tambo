import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Search, X, Loader2, CheckCircle2 } from 'lucide-react';
import { api } from '../../api/client';
import { EmptyState } from '../../components/ui';
import { formatDate, formatTime, getTodayStr } from '../../lib/format';

export default function Asistencia() {
  const [fecha, setFecha] = useState(getTodayStr());
  const [cedula, setCedula] = useState('');
  const [clase, setClase] = useState('');
  const [applied, setApplied] = useState(null);

  function buildParams() {
    const p = new URLSearchParams();
    if (applied.mes) p.set('mes', applied.mes);
    if (applied.anio) p.set('anio', applied.anio);
    if (applied.cedula) p.set('cedula', applied.cedula);
    if (applied.clase) p.set('clase', applied.clase);
    return p.toString();
  }

  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['asistencia-admin', applied],
    queryFn: () => api(`asistencia${buildParams() ? `?${buildParams()}` : ''}`),
    enabled: applied !== null,
  });

  const rows = data?.data || [];

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
  }

  function handleClear() {
    setFecha('');
    setCedula('');
    setClase('');
    setApplied(null);
  }

  return (
    <>
      <div className="main-header">
        <h1>Asistencia</h1>
        <p>Consulta los registros de asistencia</p>
      </div>

      <form className="glass-card filter-bar" onSubmit={handleFilter}>
        <div className="form-group">
          <label htmlFor="filtroFecha">Fecha</label>
          <input id="filtroFecha" type="date" className="form-input" value={fecha} onChange={(e) => setFecha(e.target.value)} />
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

      {applied === null ? (
        <EmptyState icon={Search} message="Usa los filtros y pulsa «Filtrar» para consultar asistencias" />
      ) : isLoading || isFetching ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : rows.length === 0 ? (
        <EmptyState icon={CheckCircle2} message="No hay registros de asistencia" />
      ) : (
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
                  <td>{i + 1}</td>
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
      )}
    </>
  );
}
