<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Page Introuvable · MAZAR</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;900&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;min-height:100vh;background:#0f172a;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.bg{position:fixed;inset:0;background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#0f172a 100%);z-index:0}
.stars{position:fixed;inset:0;z-index:1}
.star{position:absolute;background:#fff;border-radius:50%;animation:twinkle ease-in-out infinite}
@keyframes twinkle{0%,100%{opacity:.1;transform:scale(1)}50%{opacity:.8;transform:scale(1.3)}}
.glow-blue{position:fixed;top:20%;left:20%;width:500px;height:500px;background:radial-gradient(circle,rgba(59,130,246,.1) 0%,transparent 70%);z-index:2;pointer-events:none}
.glow-purple{position:fixed;bottom:20%;right:20%;width:400px;height:400px;background:radial-gradient(circle,rgba(139,92,246,.08) 0%,transparent 70%);z-index:2;pointer-events:none}
.container{position:relative;z-index:10;width:100%;max-width:560px;padding:2rem;animation:slideUp .7s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:2rem;padding:3rem 2.5rem;text-align:center;backdrop-filter:blur(20px);box-shadow:0 40px 80px rgba(0,0,0,.5)}
.astronaut{width:110px;height:110px;margin:0 auto 1.5rem;animation:float 4s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0) rotate(-5deg)}50%{transform:translateY(-18px) rotate(5deg)}}
.astronaut-inner{width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,rgba(59,130,246,.2),rgba(139,92,246,.2));border:2px solid rgba(59,130,246,.3);display:flex;align-items:center;justify-content:center;font-size:3.5rem;box-shadow:0 0 40px rgba(59,130,246,.2)}
.code{font-family:'Cairo',sans-serif;font-size:6rem;font-weight:900;background:linear-gradient(135deg,#3b82f6,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;margin-bottom:.5rem;letter-spacing:-2px}
.tag{display:inline-flex;align-items:center;gap:.4rem;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);color:#93c5fd;font-size:.7rem;font-weight:700;padding:.3rem .85rem;border-radius:999px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:1.25rem}
h1{font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:.75rem;letter-spacing:-.02em}
p{color:#94a3b8;font-size:.9rem;line-height:1.7;margin-bottom:.5rem}
.search-hint{background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.15);border-radius:.875rem;padding:.75rem 1rem;margin-bottom:2rem;display:flex;align-items:center;gap:.6rem;text-align:left}
.search-hint svg{flex-shrink:0;color:#60a5fa}
.search-hint span{color:#93c5fd;font-size:.8rem;font-weight:500}
.actions{display:flex;flex-direction:column;gap:.75rem}
.btn-primary{display:flex;align-items:center;justify-content:center;gap:.5rem;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;font-weight:700;font-size:.9rem;padding:.85rem 2rem;border-radius:.875rem;text-decoration:none;transition:all .25s;box-shadow:0 8px 25px rgba(59,130,246,.4)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 35px rgba(59,130,246,.6)}
.btn-row{display:flex;gap:.75rem}
.btn-secondary{flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#94a3b8;font-weight:600;font-size:.82rem;padding:.7rem 1rem;border-radius:.875rem;text-decoration:none;transition:all .2s}
.btn-secondary:hover{background:rgba(255,255,255,.1);color:#fff}
.brand{margin-top:2rem;display:flex;align-items:center;justify-content:center;gap:.6rem;opacity:.45}
.brand-dot{width:8px;height:8px;background:#3b82f6;border-radius:50%;animation:blink 2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:.4}50%{opacity:1}}
.brand span{color:#64748b;font-size:.75rem;font-weight:600;letter-spacing:.05em}
</style>
</head>
<body>
<div class="bg"></div>
<div class="stars" id="stars"></div>
<div class="glow-blue"></div>
<div class="glow-purple"></div>
<div class="container">
  <div class="card">
    <div class="astronaut">
      <div class="astronaut-inner">🚀</div>
    </div>
    <div class="code">404</div>
    <div class="tag">🔍 Page Introuvable</div>
    <h1>Oups ! Page Perdue dans l'Espace</h1>
    <p>La page que vous cherchez n'existe pas ou a été déplacée. Peut-être que l'URL est incorrecte ?</p>
    <div class="search-hint">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <span>Vérifiez l'URL ou utilisez la navigation ci-dessous pour trouver ce que vous cherchez.</span>
    </div>
    <div class="actions">
      <a href="/" class="btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Retour à l'accueil
      </a>
      <div class="btn-row">
        <a href="/student/dashboard.php" class="btn-secondary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Dashboard
        </a>
        <a href="javascript:history.back()" class="btn-secondary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Page précédente
        </a>
      </div>
    </div>
    <div class="brand"><div class="brand-dot"></div><span>MAZAR Education</span></div>
  </div>
</div>
<script>
const s=document.getElementById('stars');
for(let i=0;i<80;i++){const d=document.createElement('div');d.className='star';const sz=Math.random()*2.5+.5;d.style.cssText=`width:${sz}px;height:${sz}px;top:${Math.random()*100}%;left:${Math.random()*100}%;animation-duration:${Math.random()*4+2}s;animation-delay:${Math.random()*5}s`;s.appendChild(d)}
</script>
</body>
</html>