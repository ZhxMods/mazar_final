<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 — Accès Interdit · MAZAR</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;900&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;min-height:100vh;background:#0f172a;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.bg{position:fixed;inset:0;background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);z-index:0}
.grid-bg{position:fixed;inset:0;background-image:linear-gradient(rgba(239,68,68,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(239,68,68,.06) 1px,transparent 1px);background-size:60px 60px;z-index:1}
.particles{position:fixed;inset:0;z-index:2;overflow:hidden}
.particle{position:absolute;border-radius:50%;animation:float linear infinite;opacity:0}
@keyframes float{0%{transform:translateY(100vh) scale(0);opacity:0}10%{opacity:.4}90%{opacity:.2}100%{transform:translateY(-100px) scale(1);opacity:0}}
.glow{position:fixed;top:30%;left:50%;transform:translate(-50%,-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(239,68,68,.12) 0%,transparent 70%);z-index:2;pointer-events:none}
.container{position:relative;z-index:10;width:100%;max-width:520px;padding:2rem;animation:slideUp .7s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.card{background:rgba(255,255,255,.05);border:1px solid rgba(239,68,68,.2);border-radius:2rem;padding:3rem 2.5rem;text-align:center;backdrop-filter:blur(20px);box-shadow:0 40px 80px rgba(0,0,0,.5),0 0 0 1px rgba(239,68,68,.1) inset}
.icon-wrap{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,rgba(239,68,68,.2),rgba(220,38,38,.3));border:2px solid rgba(239,68,68,.4);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;animation:iconPulse 2s ease-in-out infinite;box-shadow:0 0 40px rgba(239,68,68,.3)}
@keyframes iconPulse{0%,100%{box-shadow:0 0 40px rgba(239,68,68,.3)}50%{box-shadow:0 0 70px rgba(239,68,68,.6)}}
.shield-icon{width:50px;height:50px}
.shield-icon path{stroke:#ef4444;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.code{font-family:'Cairo',sans-serif;font-size:6rem;font-weight:900;background:linear-gradient(135deg,#ef4444,#dc2626);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;margin-bottom:.5rem;letter-spacing:-2px}
.tag{display:inline-flex;align-items:center;gap:.4rem;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:.7rem;font-weight:700;padding:.3rem .85rem;border-radius:999px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:1.25rem}
h1{font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:.75rem;letter-spacing:-.02em}
p{color:#94a3b8;font-size:.9rem;line-height:1.7;margin-bottom:2rem}
.actions{display:flex;flex-direction:column;gap:.75rem}
.btn-primary{display:flex;align-items:center;justify-content:center;gap:.5rem;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-weight:700;font-size:.9rem;padding:.85rem 2rem;border-radius:.875rem;text-decoration:none;transition:all .25s;box-shadow:0 8px 25px rgba(239,68,68,.4)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 35px rgba(239,68,68,.6)}
.btn-secondary{display:flex;align-items:center;justify-content:center;gap:.5rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:#94a3b8;font-weight:600;font-size:.85rem;padding:.75rem 2rem;border-radius:.875rem;text-decoration:none;transition:all .2s}
.btn-secondary:hover{background:rgba(255,255,255,.1);color:#fff}
.brand{margin-top:2rem;display:flex;align-items:center;justify-content:center;gap:.6rem;opacity:.5}
.brand-dot{width:8px;height:8px;background:#ef4444;border-radius:50%}
.brand span{color:#64748b;font-size:.75rem;font-weight:600;letter-spacing:.05em}
</style>
</head>
<body>
<div class="bg"></div>
<div class="grid-bg"></div>
<div class="glow"></div>
<div class="particles" id="particles"></div>
<div class="container">
  <div class="card">
    <div class="icon-wrap">
      <svg class="shield-icon" viewBox="0 0 24 24">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        <path d="M9.5 12l2 2 3-3" stroke="#ef4444" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 9v3" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/>
        <circle cx="12" cy="14" r=".5" fill="#ef4444"/>
      </svg>
    </div>
    <div class="code">403</div>
    <div class="tag">⛔ Accès Interdit</div>
    <h1>Permission Refusée</h1>
    <p>Vous n'avez pas les droits nécessaires pour accéder à cette ressource. Connectez-vous avec un compte autorisé ou contactez l'administrateur.</p>
    <div class="actions">
      <a href="/login.php" class="btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Se connecter
      </a>
      <a href="/" class="btn-secondary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Retour à l'accueil
      </a>
    </div>
    <div class="brand"><div class="brand-dot"></div><span>MAZAR Education</span></div>
  </div>
</div>
<script>
const p=document.getElementById('particles');
for(let i=0;i<25;i++){const d=document.createElement('div');d.className='particle';const s=Math.random()*6+3;d.style.cssText=`width:${s}px;height:${s}px;left:${Math.random()*100}%;background:rgba(239,68,68,${Math.random()*.3+.1});animation-duration:${Math.random()*15+10}s;animation-delay:${Math.random()*10}s`;p.appendChild(d)}
</script>
</body>
</html>