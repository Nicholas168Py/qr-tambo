import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { BarChart3, Loader2, Users, ClipboardList, CalendarDays } from 'lucide-react';
import { api } from '../../api/client';
import { MONTH_NAMES, formatDate } from '../../lib/format';

export default function Reportes() {
  const now = new Date();
  const [mes, setMes] = useState(String(now.getMonth() + 1));
  const [anio, setAnio] = useState(String(now.getFullYear()));
  const [applied, setApplied] = useState({ mes: now.getMonth() + 1, anio: now.getFullYear() });

  const { data, isLoading, refetch } = useQuery({
    queryKey: ['reporte-mensual', applied],
    queryFn: () => api(`asistencia/reporte-mensual?mes=${applied.mes}&anio=${applied.anio}`),
    enabled: !!applied.mes && !!applied.anio,
  });

  const r = data?.data;

  function handleSubmit(e) {
    e.preventDefault();
    setApplied({ mes: parseInt(mes, 10), anio: parseInt(anio, 10) });
  }

  return (
    <>
      <div className="main-header">
        <h1>Reportes</h1>
        <p>Reporte mensual de asistencia</p>
      </div>

      <form className="glass-card filter-bar" onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="reporteMes">Mes</label>
          <select id="reporteMes" className="form-input" value={mes} onChange={(e) => setMes(e.target.value)}>
            {MONTH_NAMES.map((m, i) => (
              <option key={i + 1} value={i + 1}>{m}</option>
            ))}
          </select>
        </div>
        <div className="form-group">
          <label htmlFor="reporteAnio">Año</label>
          <input id="reporteAnio" type="number" min="2020" className="form-input" value={anio} onChange={(e) => setAnio(e.target.value)} />
        </div>
        <button className="btn btn-primary" type="submit"><BarChart3 size={18} /> Ver Reporte</button>
      </form>

      {isLoading ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : !r ? (
        <div className="empty-state"><p>No hay datos para mostrar</p></div>
      ) : (
        <>
          <div className="stats-grid">
            <div className="glass-card stat-card">
              <span className="stat-icon"><ClipboardList size={32} /></span>
              <div className="stat-value">{r.total_registros}</div>
              <div className="stat-label">Registros Totales</div>
            </div>
            <div className="glass-card stat-card">
              <span className="stat-icon"><Users size={32} /></span>
              <div className="stat-value">{r.bailarines_unicos}</div>
              <div className="stat-label">Bailarines Únicos</div>
            </div>
          </div>

          <div className="glass-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginBottom: 12 }}>Asistencia por Clase</h2>
            <div className="table-container">
              <table>
                <thead><tr><th>Clase</th><th>Total</th></tr></thead>
                <tbody>
                  {r.por_clase.map((c) => (
                    <tr key={c.clase}><td>{c.clase}</td><td>{c.total}</td></tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div className="glass-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginBottom: 12 }}>Asistencia por Bailarín</h2>
            <div className="table-container">
              <table>
                <thead><tr><th>#</th><th>Cédula</th><th>Nombre</th><th>Total</th></tr></thead>
                <tbody>
                  {r.por_bailarin.map((b, i) => (
                    <tr key={b.cedula}><td>{i + 1}</td><td>{b.cedula}</td><td>{b.nombre}</td><td>{b.total}</td></tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div className="glass-card">
            <h2 style={{ marginBottom: 12 }}>Asistencia por Día</h2>
            <div className="table-container">
              <table>
                <thead><tr><th>Fecha</th><th>Total</th></tr></thead>
                <tbody>
                  {r.por_dia.map((d) => (
                    <tr key={d.fecha}><td><CalendarDays size={14} /> {formatDate(d.fecha)}</td><td>{d.total}</td></tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}
    </>
  );
}
