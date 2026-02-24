<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>401 — Non autorisé · MAZAR</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Poppins',sans-serif;background:#0f0a1a;color:#fff;overflow:hidden}
body{display:flex;align-items:center;justify-content:center;min-height:100vh;position:relative}
.bg-mesh{position:fixed;inset:0;z-index:0;background:radial-gradient(ellipse at 30% 40%,rgba(109,40,217,.35) 0%,transparent 55%),radial-gradient(ellipse at 75% 70%,rgba(67,20,133,.4) 0%,transparent 50%),radial-gradient(ellipse at 60% 10%,rgba(139,92,246,.12) 0%,transparent 40%),#0f0a1a}
.stars{position:fixed;inset:0;z-index:1;pointer-events:none}
.star{position:absolute;border-radius:50%;animation:twinkle ease-in-out infinite}
@keyframes twinkle{0%,100%{opacity:.1;transform:scale(1)}50%{opacity:.8;transform:scale(1.4)}}
.scan{position:fixed;inset:0;z-index:1;background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(139,92,246,.015) 3px,rgba(139,92,246,.015) 4px);pointer-events:none}
.card{position:relative;z-index:10;text-align:center;max-width:520px;width:90%;padding:3rem 2.5rem;background:rgba(109,40,217,.06);border:1px solid rgba(139,92,246,.25);border-radius:2rem;backdrop-filter:blur(24px);box-shadow:0 0 60px rgba(109,40,217,.2),0 40px 80px rgba(0,0,0,.6);animation:cardIn .7s cubic-bezier(.34,1.56,.64,1) both}
@keyframes cardIn{from{opacity:0;transform:translateY(30px) scale(.95)}to{opacity:1;transform:translateY(0) scale(1)}}
.lock-icon{width:80px;height:80px;margin:0 auto 1.5rem;position:relative}
.lock-body{width:52px;height:44px;background:linear-gradient(135deg,rgba(109,40,217,.5),rgba(139,92,246,.3));border:2px solid rgba(139,92,246,.5);border-radius:10px;margin:28px auto 0;display:flex;align-items:center;justify-content:center;box-shadow:0 0 20px rgba(139,92,246,.3)}
.lock-shackle{width:28px;height:28px;border:2.5px solid rgba(139,92,246,.6);border-bottom:none;border-radius:14px 14px 0 0;position:absolute;top:0;left:50%;transform:translateX(-50%);animation:lockBounce 3s ease-in-out infinite}
@keyframes lockBounce{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(-4px)}}
.keyhole{width:10px;height:14px;background:rgba(139,92,246,.8);border-radius:50% 50% 4px 4px;position:relative;top:2px}
.code{font-family:'Cairo',sans-serif;font-size:clamp(5rem,18vw,9rem);font-weight:900;line-height:1;letter-spacing:-.04em;background:linear-gradient(135deg,#a78bfa,#c4b5fd,#ede9fe);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.divider{width:48px;height:3px;background:linear-gradient(90deg,#7c3aed,#a78bfa);border-radius:99px;margin:.75rem auto 1.5rem}
.title{font-size:1.6rem;font-weight:800;margin-bottom:.75rem;color:#e2e8f0}
.desc{font-size:.95rem;color:#94a3b8;line-height:1.7;margin-bottom:2rem}
.desc strong{color:#a78bfa;font-weight:600}
.tags{display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-bottom:2rem}
.tag{font-size:.7rem;font-weight:600;padding:.3rem .8rem;border-radius:999px;background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.25);color:#c4b5fd;letter-spacing:.05em;text-transform:uppercase}
.btns{display:flex;gap:.75rem;flex-wrap:wrap;justify-content:center}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.75rem;border-radius:1rem;font-size:.88rem;font-weight:700;text-decoration:none;transition:all .22s cubic-bezier(.34,1.56,.64,1);cursor:pointer;border:none;font-family:'Poppins',sans-serif}
.btn-primary{background:linear-gradient(135deg,#7c3aed,#4c1d95);color:#fff;box-shadow:0 4px 20px rgba(124,58,237,.4)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(124,58,237,.6)}
.btn-login{background:linear-gradient(135deg,#1d4ed8,#1e3a8a);color:#fff;box-shadow:0 4px 20px rgba(29,78,216,.35)}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(29,78,216,.55)}
.btn-ghost{background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.1)}
.btn-ghost:hover{background:rgba(255,255,255,.1);color:#e2e8f0;transform:translateY(-1px)}
.brand{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:10;font-size:.75rem;color:rgba(255,255,255,.2);font-weight:600;letter-spacing:.1em;text-transform:uppercase}
</style></head>
<body>
<div class="bg-mesh"></div>
<div class="scan"></div>
<div class="stars" id="stars"></div>
<div class="card">
  <div class="lock-icon">
    <div class="lock-shackle"></div>
    <div class="lock-body"><div class="keyhole"></div></div>
  </div>
  <div class="code">401</div>
  <div class="divider"></div>
  <h1 class="title">Non autorisé</h1>
  <p class="desc">Vous devez être <strong>connecté</strong> pour accéder à cette ressource. Veuillez vous identifier avec vos identifiants MAZAR.</p>
  <div class="tags"><span class="tag">Connexion requise</span><span class="tag">Session expirée</span><span class="tag">Unauthorized</span></div>
  <div class="btns">
    <a href="/login.php" class="btn btn-login"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>Se connecter</a>
    <a href="/" class="btn btn-primary"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Accueil</a>
  </div>
</div>
<div class="brand">MAZAR Education</div>
<script>const s=document.getElementById('stars');for(let i=0;i<80;i++){const d=document.createElement('div');const sz=Math.random()*3+1;d.className='star';d.style.cssText=`left:${Math.random()*100}%;top:${Math.random()*100}%;width:${sz}px;height:${sz}px;background:rgba(167,139,250,.8);animation-duration:${Math.random()*4+2}s;animation-delay:${Math.random()*4}s;`;s.appendChild(d);}</script>
</body></html>