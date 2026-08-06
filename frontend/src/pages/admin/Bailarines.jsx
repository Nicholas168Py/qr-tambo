import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Trash2, Users, Loader2 } from 'lucide-react';
import { api } from '../../api/client';
import { useToast } from '../../stores/toast';
import { EmptyState } from '../../components/ui';
import { formatDate } from '../../lib/format';

export default function Bailarines() {
  const toast = useToast();
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({ queryKey: ['usuarios'], queryFn: () => api('usuarios') });
  const usuarios = data?.data || [];
  const total = data?.total ?? usuarios.length;

  const remove = useMutation({
    mutationFn: (id) => api(`usuarios/${id}`, 'DELETE'),
    onSuccess: (res) => {
      if (res.success) {
        toast.success('Bailarín eliminado correctamente');
        queryClient.invalidateQueries({ queryKey: ['usuarios'] });
      } else {
        toast.error(res.message);
      }
    },
  });

  function handleDelete(u) {
    if (window.confirm(`¿Eliminar al bailarín "${u.nombre}"?`)) {
      remove.mutate(u.id);
    }
  }

  return (
    <>
      <div className="main-header">
        <h1>Bailarines</h1>
        <p>Bailarines Registrados</p>
      </div>

      {isLoading ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : usuarios.length === 0 ? (
        <EmptyState icon={Users} message="Aún no hay bailarines registrados" />
      ) : (
        <>
          <div className="glass-card" style={{ padding: '12px 16px', marginBottom: 20, display: 'inline-block' }}>
            <span className="badge badge-bailarin">{total} registrados</span>
          </div>
          <div className="table-container">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cédula</th>
                  <th>Nombre</th>
                  <th>Registrado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                {usuarios.map((u, i) => (
                  <tr key={u.id}>
                    <td>{i + 1}</td>
                    <td>{u.cedula}</td>
                    <td>{u.nombre}</td>
                    <td>{formatDate(String(u.created_at).slice(0, 10))}</td>
                    <td>
                      <button className="btn btn-danger btn-sm" onClick={() => handleDelete(u)}>
                        <Trash2 size={14} /> Eliminar
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      )}
    </>
  );
}
