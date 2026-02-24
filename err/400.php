<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>400 — Requête invalide · MAZAR</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Poppins',sans-serif;background:#0f172a;color:#fff;overflow:hidden}
body{display:flex;align-items:center;justify-content:center;min-height:100vh;position:relative}
.bg-mesh{position:fixed;inset:0;z-index:0;background:radial-gradient(ellipse at 20% 50%,rgba(29,78,216,.35) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(30,58,138,.4) 0%,transparent 50%),#0f172a}
.particles{position:fixed;inset:0;z-index:1;pointer-events:none;overflow:hidden}
.p{position:absolute;border-radius:50%;animation:drift linear infinite;opacity:0}
@keyframes drift{0%{transform:translateY(100vh) translateX(0);opacity:0}10%{opacity:.6}90%{opacity:.4}100%{transform:translateY(-10vh) translateX(var(--dx));opacity:0}}
.grid-lines{position:fixed;inset:0;z-index:1;background-image:linear-gradient(rgba(96,165,250,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(96,165,250,.04) 1px,transparent 1px);background-size:60px 60px}
.card{position:relative;z-index:10;text-align:center;max-width:520px;width:90%;padding:3rem 2.5rem;background:rgba(255,255,255,.04);border:1px solid rgba(96,165,250,.18);border-radius:2rem;backdrop-filter:blur(24px);box-shadow:0 40px 80px rgba(0,0,0,.5);animation:cardIn .7s cubic-bezier(.34,1.56,.64,1) both}
@keyframes cardIn{from{opacity:0;transform:translateY(30px) scale(.95)}to{opacity:1;transform:translateY(0) scale(1)}}
.code{font-family:'Cairo',sans-serif;font-size:clamp(5rem,18vw,9rem);font-weight:900;line-height:1;letter-spacing:-.04em;background:linear-gradient(135deg,#60a5fa,#93c5fd,#dbeafe);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.icon-wrap{width:72px;height:72px;margin:0 auto 1.5rem;border-radius:20px;background:linear-gradient(135deg,rgba(29,78,216,.4),rgba(30,58,138,.6));border:1px solid rgba(96,165,250,.3);display:flex;align-items:center;justify-content:center;font-size:2rem;animation:pulse 2.5s ease-in-out infinite;box-shadow:0 0 30px rgba(96,165,250,.2)}
@keyframes pulse{0%,100%{box-shadow:0 0 20px rgba(96,165,250,.2)}50%{box-shadow:0 0 40px rgba(96,165,250,.5)}}
.divider{width:48px;height:3px;background:linear-gradient(90deg,#1d4ed8,#60a5fa);border-radius:99px;margin:.75rem auto 1.5rem}
.title{font-size:1.6rem;font-weight:800;margin-bottom:.75rem;color:#e2e8f0}
.desc{font-size:.95rem;color:#94a3b8;line-height:1.7;margin-bottom:2rem}
.desc strong{color:#60a5fa;font-weight:600}
.tags{display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-bottom:2rem}
.tag{font-size:.7rem;font-weight:600;padding:.3rem .8rem;border-radius:999px;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.2);color:#93c5fd;letter-spacing:.05em;text-transform:uppercase}
.btns{display:flex;gap:.75rem;flex-wrap:wrap;justify-content:center}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.75rem;border-radius:1rem;font-size:.88rem;font-weight:700;text-decoration:none;transition:all .22s cubic-bezier(.34,1.56,.64,1);cursor:pointer;border:none;font-family:'Poppins',sans-serif}
.btn-primary{background:linear-gradient(135deg,#1d4ed8,#1e3a8a);color:#fff;box-shadow:0 4px 20px rgba(29,78,216,.4)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(29,78,216,.6)}
.btn-ghost{background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.1)}
.btn-ghost:hover{background:rgba(255,255,255,.1);color:#e2e8f0;transform:translateY(-1px)}
.brand{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:10;font-size:.75rem;color:rgba(255,255,255,.2);font-weight:600;letter-spacing:.1em;text-transform:uppercase}
</style></head>
<body>
<div class="bg-mesh"></div>
<div class="grid-lines"></div>
<div class="particles" id="p"></div>
<div class="card">
  <div class="icon-wrap">🔧</div>
  <div class="code">400</div>
  <div class="divider"></div>
  <h1 class="title">Requête invalide</h1>
  <p class="desc">Le serveur n'a pas pu comprendre votre requête. Elle contient peut-être une <strong>syntaxe incorrecte</strong> ou des paramètres invalides.</p>
  <div class="tags"><span class="tag">Paramètre manquant</span><span class="tag">Syntaxe incorrecte</span><span class="tag">Bad Request</span></div>
  <div class="btns">
    <a href="/" class="btn btn-primary"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Accueil</a>
    <button onclick="history.back()" class="btn btn-ghost"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>Retour</button>
  </div>
</div>
<div class="brand">MAZAR Education</div>
<script>const c=document.getElementById('p');for(let i=0;i<25;i++){const d=document.createElement('div');const s=Math.random()*6+2;d.className='p';d.style.cssText=`left:${Math.random()*100}%;width:${s}px;height:${s}px;background:rgba(96,165,250,${Math.random()*.5+.1});--dx:${(Math.random()-0.5)*200}px;animation-duration:${Math.random()*12+8}s;animation-delay:${Math.random()*8}s;`;c.appendChild(d);}</script>
</body></html>