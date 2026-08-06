import { useRef, useState } from 'react';
import { Html5Qrcode } from 'html5-qrcode';
import { Camera, CheckCircle2, Info, XCircle, Loader2, RefreshCw } from 'lucide-react';
import { api } from '../../api/client';
import { formatDate, formatTime } from '../../lib/format';

export default function Escanear() {
  const scannerRef = useRef(null);
  const [status, setStatus] = useState('idle'); // idle | scanning | processing | success | duplicate | error | no-camera | no-token
  const [result, setResult] = useState(null);
  const [errorMsg, setErrorMsg] = useState('');
  const [cameraError, setCameraError] = useState('');

  async function startCamera() {
    setStatus('scanning');
    setResult(null);
    setErrorMsg('');
    setCameraError('');

    if (!scannerRef.current) {
      scannerRef.current = new Html5Qrcode('scanner-container');
    }

    try {
      await scannerRef.current.start(
        { facingMode: 'environment' },
        { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
        onScanSuccess,
        () => {}
      );
    } catch (err) {
      console.error('[SCANNER] Error al iniciar cámara:', err);
      setStatus('no-camera');
      setCameraError(err?.message || String(err));
      try { await scannerRef.current?.clear(); } catch (e) {}
      scannerRef.current = null;
    }
  }

  async function stopCamera() {
    if (scannerRef.current) {
      try {
        await scannerRef.current.stop();
        await scannerRef.current.clear();
      } catch (e) {}
      scannerRef.current = null;
    }
  }

  async function onScanSuccess(decodedText) {
    await stopCamera();
    await processQRCode(decodedText);
  }

  async function processQRCode(url) {
    setStatus('processing');
    try {
      const parsedUrl = new URL(url);
      const token = parsedUrl.searchParams.get('token');
      if (!token) {
        setStatus('no-token');
        return;
      }

      const res = await api('asistencia/registrar', 'POST', { token });

      if (res.success) {
        setStatus('success');
        setResult(res.data);
      } else {
        const isDuplicate = res.message && res.message.includes('Ya registraste');
        setStatus(isDuplicate ? 'duplicate' : 'error');
        setResult(res.message);
        setErrorMsg(`Código: ${res.code || ''}`);
      }
    } catch (e) {
      console.error('[SCANNER] processQRCode error:', e);
      setStatus('error');
      setResult('No se pudo conectar con el servidor.');
      setErrorMsg(e?.message || '');
    }
  }

  async function reset() {
    await stopCamera();
    setStatus('idle');
    setResult(null);
    setErrorMsg('');
    setCameraError('');
  }

  if (status === 'success') {
    return (
      <div className="scan-result">
        <span className="result-icon"><CheckCircle2 size={72} /></span>
        <h2>¡Asistencia Registrada!</h2>
        <div className="result-details">
          <p>{result.nombre}</p>
          <p>{result.clase}</p>
          <p>{formatDate(result.fecha)} - {formatTime(result.hora_registro)}</p>
        </div>
        <button className="btn btn-primary" onClick={reset}><RefreshCw size={18} /> Escanear otro QR</button>
      </div>
    );
  }

  if (status === 'duplicate' || status === 'error' || status === 'no-token' || status === 'no-camera') {
    const isDuplicate = status === 'duplicate';
    return (
      <div className="scan-result">
        <span className="result-icon">
          {status === 'no-camera'
            ? <Camera size={72} />
            : isDuplicate
              ? <Info size={72} />
              : <XCircle size={72} />}
        </span>
        <h2>
          {status === 'no-camera' ? 'Permiso de Cámara Requerido'
            : status === 'no-token' ? 'Código Inválido'
              : isDuplicate ? 'Ya Registrado' : 'Error'}
        </h2>
        <div className="result-details">
          <p>
            {status === 'no-camera'
              ? 'QR Tambo necesita acceso a tu cámara para escanear códigos QR. Permite el acceso cuando el navegador lo solicite.'
              : status === 'no-token'
                ? 'Este código QR no es válido para QR Tambo.'
                : result}
          </p>
          {errorMsg && <p style={{ marginTop: 8, fontSize: '0.75rem', color: 'var(--text-muted)' }}>{errorMsg}</p>}
          {status === 'no-camera' && cameraError && (
            <p style={{ marginTop: 8, fontSize: '0.75rem', color: 'var(--text-muted)' }}>{cameraError}</p>
          )}
        </div>
        <button className="btn btn-primary" onClick={startCamera} style={{ marginTop: 16 }}>
          {status === 'no-camera' ? 'Intentar de nuevo' : 'Escanear otro QR'}
        </button>
      </div>
    );
  }

  return (
    <>
      <h2 style={{ marginBottom: 8 }}>Escanea el Código QR</h2>
      <p style={{ color: 'var(--text-secondary)', fontSize: '0.9rem', marginBottom: 20 }}>
        Apunta la cámara al código QR que muestra el instructor
      </p>

      {status === 'scanning' && (
        <div id="scanner-container" style={{ display: 'block' }}></div>
      )}

      {status === 'idle' ? (
        <div className="glass-card scan-card" onClick={startCamera}>
          <span className="scan-icon"><Camera size={52} /></span>
          <h2>Iniciar Cámara</h2>
          <p>Listo para escanear</p>
        </div>
      ) : status === 'processing' ? (
        <div className="scan-result">
          <div className="spinner" style={{ margin: '20px auto' }}></div>
          <h2>Registrando asistencia...</h2>
        </div>
      ) : status === 'scanning' ? (
        <div className="scan-result" style={{ padding: '20px 0' }}>
          <div className="spinner" style={{ margin: '0 auto' }}></div>
          <p style={{ color: 'var(--text-secondary)', marginTop: 12 }}>Buscando código QR...</p>
        </div>
      ) : (
        <div className="scan-result">
          <Loader2 size={40} className="spin" />
        </div>
      )}
    </>
  );
}
