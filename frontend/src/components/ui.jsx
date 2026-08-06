export function StatCard({ icon: Icon, value, label }) {
  return (
    <div className="glass-card stat-card">
      <span className="stat-icon"><Icon size={32} /></span>
      <div className="stat-value">{value}</div>
      <div className="stat-label">{label}</div>
    </div>
  );
}

export function EmptyState({ icon: Icon, message }) {
  return (
    <div className="empty-state">
      <span className="empty-icon"><Icon size={52} /></span>
      <p>{message}</p>
    </div>
  );
}
