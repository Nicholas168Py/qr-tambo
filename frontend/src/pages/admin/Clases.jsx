import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Plus, Trash2, Music, Loader2 } from 'lucide-react';
import { api } from '../../api/client';
import { useToast } from '../../stores/toast';
import { EmptyState } from '../../components/ui';

export default function Clases() {
  const toast = useToast();
  const queryClient = useQueryClient();
  const [nombre, setNombre] = useState('');
  const [descripcion, setDescripcion] = useState('');

  const { data, isLoading } = useQuery({ queryKey: ['clases'], queryFn: () => api('clases') });
  const clases = data?.data || [];

  const create = useMutation({
    mutationFn: (body) => api('clases', 'POST', body),
    onSuccess: (res) => {
      if (res.success) {
        toast.success('Clase creada exitosamente');
        setNombre('');
        setDescripcion('');
        queryClient.invalidateQueries({ queryKey: ['clases'] });
      } else {
        toast.error(res.message);
      }
    },
  });

  const remove = useMutation({
    mutationFn: (id) => api(`clases/${id}`, 'DELETE'),
    onSuccess: (res) => {
      if (res.success) {
        toast.success('Clase eliminada');
        queryClient.invalidateQueries({ queryKey: ['clases'] });
      } else {
        toast.error(res.message);
      }
    },
  });

  function handleCreate(e) {
    e.preventDefault();
    if (!nombre.trim()) {
      toast.warning('El nombre de la clase es requerido');
      return;
    }
    create.mutate({ nombre: nombre.trim(), descripcion: descripcion.trim() });
  }

  function handleDelete(c) {
    if (window.confirm(`¿Eliminar la clase "${c.nombre}"?`)) {
      remove.mutate(c.id);
    }
  }

  return (
    <>
      <div className="main-header">
        <h1>Clases</h1>
        <p>Administra las clases disponibles en la academia</p>
      </div>

      <form className="glass-card add-form" onSubmit={handleCreate}>
        <div className="form-group">
          <label htmlFor="claseNombre">Nombre de la clase</label>
          <input
            id="claseNombre"
            className="form-input"
            placeholder="Ej: Salsa Básica"
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
          />
        </div>
        <div className="form-group">
          <label htmlFor="claseDesc">Descripción (opcional)</label>
          <input
            id="claseDesc"
            className="form-input"
            placeholder="Ej: Nivel principiante"
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
          />
        </div>
        <button className="btn btn-primary" type="submit" disabled={create.isPending}>
          {create.isPending ? <Loader2 size={18} className="spin" /> : <Plus size={18} />}
          Agregar
        </button>
      </form>

      {isLoading ? (
        <div className="empty-state"><Loader2 size={40} className="spin" /></div>
      ) : clases.length === 0 ? (
        <EmptyState icon={Music} message="Aún no hay clases registradas" />
      ) : (
        <div className="cards-grid">
          {clases.map((c) => (
            <div key={c.id} className="glass-card item-card">
              <h3>{c.nombre}</h3>
              <p>{c.descripcion || 'Sin descripción'}</p>
              <div className="card-actions">
                <button className="btn btn-danger btn-sm" onClick={() => handleDelete(c)}>
                  <Trash2 size={16} /> Eliminar
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </>
  );
}
