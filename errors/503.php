<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>503 — Service Indisponible · MAZAR</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;900&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;min-height:100vh;background:#0f172a;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.bg{position:fixed;inset:0;background:linear-gradient(135deg,#0f172a 0%,#1c1917 50%,#0f172a 100%);z-index:0}
.grid-bg{position:fixed;inset:0;background-image:linear-gradient(rgba(245,158,11,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(245,158,11,.04) 1px,transparent 1px);background-size:50px 50px;z-index:1}
.pulse-ring{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:2;pointer-events:none}
.pulse-ring::before,.pulse-ring::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);border-radius:50%;border:1px solid rgba(245,158,11,.15);animation:ringPulse 3s ease-out infinite}
.pulse-ring::before{width:400px;height:400px}
.pulse-ring::after{width:600px;height:600px;animation-delay:1.5s}
@keyframes ringPulse{0%{opacity:.6;transform:translate(-50%,-50%) scale(.5)}100%{opacity:0;transform:translate(-50%,-50%) scale(1.2)}}
.container{position:relative;z-index:10;width:100%;max-width:540px;padding:2rem;animation:slideUp .7s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(245,158,11,.15);border-radius:2rem;padding:3rem 2.5rem;text-align:center;backdrop-filter:blur(20px);box-shadow:0 40px 80px rgba(0,0,0,.5),0 0 60px rgba(245,158,11,.05) inset}
.gear-wrap{width:100px;height:100px;margin:0 auto 1.5rem;position:relative}
.gear-big{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);animation:spin 4s linear infinite}
.gear-small{position:absolute;top:10%;right:10%;animation:spinReverse 3s linear infinite}
@keyframes spin{from{transform:translate(-50%,-50%) rotate(0deg)}to{transform:translate(-50%,-50%) rotate(360deg)}}
@keyframes spinReverse{from{transform:rotate(0deg)}to{transform:rotate(-360deg)}}
.gear-big svg,.gear-small svg{display:block}
.code{font-family:'Cairo',sans-serif;font-size:6rem;font-weight:900;background:linear-gradient(135deg,#f59e0b,#d97706);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;margin-bottom:.5rem;letter-spacing:-2px}
.tag{display:inline-flex;align-items:center;gap:.4rem;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#fcd34d;font-size:.7rem;font-weight:700;padding:.3rem .85rem;border-radius:999px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:1.25rem}
h1{font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:.75rem;letter-spacing:-.02em}
p{color:#94a3b8;font-size:.9rem;line-height:1.7;margin-bottom:1.5rem}
.status-bar{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.15);border-radius:.875rem;padding:1rem 1.25rem;margin-bottom:2rem;display:flex;flex-direction:column;gap:.75rem}
.status-item{display:flex;align-items:center;justify-content:space-between}
.status-label{color:#94a3b8;font-size:.8rem;font-weight:500}
.status-val{display:flex;align-items:center;gap:.4rem;font-size:.78rem;font-weight:700}
.dot-ok{width:7px;height:7px;background:#10b981;border-radius:50%}
.dot-warn{width:7px;height:7px;background:#f59e0b;border-radius:50%;animation:blink 1s ease-in-out infinite}
.dot-err{width:7px;height:7px;background:#ef4444;border-radius:50%}
@keyframes blink{0%,100%{opacity:.3}50%{opacity:1}}
.timer{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:.75rem;padding:.65rem 1rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:center;gap:.6rem;color:#94a3b8;font-size:.82rem}
.timer strong{color:#fcd34d;font-variant-numeric:tabular-nums;font-weight:700}
.actions{display:flex;flex-direction:column;gap:.75rem}
.btn-primary{display:flex;align-items:center;justify-content:center;gap:.5rem;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-weight:700;font-size:.9rem;padding:.85rem 2rem;border-radius:.875rem;text-decoration:none;transition:all .25s;box-shadow:0 8px 25px rgba(245,158,11,.4);border:none;cursor:pointer;width:100%;font-family:'Poppins',sans-serif}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 35px rgba(245,158,11,.6)}
.btn-secondary{display:flex;align-items:center;justify-content:center;gap:.5rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#94a3b8;font-weight:600;font-size:.85rem;padding:.75rem 2rem;border-radius:.875rem;text-decoration:none;transition:all .2s}
.btn-secondary:hover{background:rgba(255,255,255,.1);color:#fff}
.brand{margin-top:2rem;display:flex;align-items:center;justify-content:center;gap:.6rem;opacity:.45}
.brand-dot{width:8px;height:8px;background:#f59e0b;border-radius:50%}
.brand span{color:#64748b;font-size:.75rem;font-weight:600;letter-spacing:.05em}
</style>
</head>
<body>
<div class="bg"></div>
<div class="grid-bg"></div>
<div class="pulse-ring"></div>
<div class="container">
  <div class="card">
    <div class="gear-wrap">
      <div class="gear-big">
        <svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="rgba(245,158,11,.7)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/>
          <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
        </svg>
      </div>
      <div class="gear-small">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="rgba(245,158,11,.5)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/>
          <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
        </svg>
      </div>
    </div>
    <div class="code">503</div>
    <div class="tag">⚙️ Service Indisponible</div>
    <h1>Maintenance en Cours</h1>
    <p>Le serveur MAZAR est temporairement indisponible pour maintenance ou surcharge. Nous travaillons activement pour rétablir le service.</p>
    <div class="status-bar">
      <div class="status-item">
        <span class="status-label">Serveur Web</span>
        <span class="status-val"><div class="dot-warn"></div><span style="color:#fcd34d">Maintenance</span></span>
      </div>
      <div class="status-item">
        <span class="status-label">Base de données</span>
        <span class="status-val"><div class="dot-ok"></div><span style="color:#6ee7b7">Opérationnel</span></span>
      </div>
      <div class="status-item">
        <span class="status-label">CDN / Assets</span>
        <span class="status-val"><div class="dot-ok"></div><span style="color:#6ee7b7">Opérationnel</span></span>
      </div>
    </div>
    <div class="timer">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Nouvelle tentative dans <strong id="countdown">30</strong> secondes
    </div>
    <div class="actions">
      <button class="btn-primary" onclick="location.reload()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
        Réessayer maintenant
      </button>
      <a href="/" class="btn-secondary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Retour à l'accueil
      </a>
    </div>
    <div class="brand"><div class="brand-dot"></div><span>MAZAR Education</span></div>
  </div>
</div>
<script>
let t=30;
const el=document.getElementById('countdown');
const iv=setInterval(()=>{t--;el.textContent=t;if(t<=0){clearInterval(iv);location.reload()}},1000);
</script>
</body>
</html>