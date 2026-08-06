import { useToast } from '../stores/toast';

export default function ToastContainer() {
  const toasts = useToast((s) => s.toasts);
  const remove = useToast((s) => s.remove);

  return (
    <div id="toast-container">
      {toasts.map((t) => (
        <div key={t.id} className={`toast toast-${t.type}`} onClick={() => remove(t.id)}>
          <span>{t.message}</span>
        </div>
      ))}
    </div>
  );
}
