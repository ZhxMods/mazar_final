<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MAZAR AI — Assistant Éducatif</title>
<meta name="description" content="MAZAR AI — Votre assistant éducatif intelligent dédié à Mazar Education.">

<!-- Same CDNs as MAZAR site -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: { extend: {
      colors: { primary: { 50:'#eff6ff',100:'#dbeafe',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' } },
      fontFamily: { sans: ['Poppins','sans-serif'] }
    }}
  }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: 'Poppins', sans-serif; background: #f1f5f9; -webkit-font-smoothing: antialiased; }

/* ── Gradient matching MAZAR sidebar ── */
.gradient-hero { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%); }
.gradient-text  {
  background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ── Chat Layout ── */
#chat-app {
  display: flex;
  flex-direction: column;
  height: 100vh;
  max-width: 900px;
  margin: 0 auto;
}

/* ── Header ── */
#chat-header {
  flex-shrink: 0;
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  position: relative;
  z-index: 10;
}

.back-link {
  display: flex;
  align-items: center;
  gap: .4rem;
  color: #64748b;
  text-decoration: none;
  font-size: .82rem;
  font-weight: 600;
  padding: .45rem .9rem;
  border-radius: .75rem;
  border: 1px solid #e2e8f0;
  transition: all .18s;
  white-space: nowrap;
  flex-shrink: 0;
}
.back-link:hover { color: #2563eb; border-color: #bfdbfe; background: #eff6ff; }

.ai-avatar-wrap {
  position: relative;
  flex-shrink: 0;
}
.ai-avatar {
  width: 46px; height: 46px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(37,99,235,.35);
}
.ai-avatar svg { width: 22px; height: 22px; color: #fff; }
.ai-online-dot {
  position: absolute;
  bottom: -2px; right: -2px;
  width: 12px; height: 12px;
  background: #10b981;
  border-radius: 50%;
  border: 2.5px solid #fff;
  animation: dotPulse 2s ease-in-out infinite;
}
@keyframes dotPulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,.4); }
  50%      { box-shadow: 0 0 0 5px rgba(16,185,129,0); }
}

.ai-header-info { flex: 1; min-width: 0; }
.ai-header-name {
  font-weight: 800;
  font-size: 1rem;
  color: #1e293b;
  letter-spacing: -.01em;
}
.ai-header-sub {
  font-size: .73rem;
  color: #64748b;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: .4rem;
  margin-top: .05rem;
}
.ai-header-sub-dot {
  width: 6px; height: 6px;
  background: #10b981;
  border-radius: 50%;
  animation: dotPulse 2s infinite;
}

.ai-edu-badge {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  background: #fefce8;
  border: 1px solid #fde68a;
  color: #92400e;
  font-size: .72rem;
  font-weight: 700;
  padding: .35rem .85rem;
  border-radius: 999px;
}

/* ── Messages scroll area ── */
#chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  scroll-behavior: smooth;
}
#chat-messages::-webkit-scrollbar { width: 4px; }
#chat-messages::-webkit-scrollbar-track { background: transparent; }
#chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

/* ── Welcome block ── */
.welcome-block {
  text-align: center;
  padding: 2rem 1rem 1.5rem;
  animation: fadeSlideUp .5s ease both;
}
.welcome-icon-wrap {
  width: 72px; height: 72px;
  margin: 0 auto 1.25rem;
  border-radius: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(37,99,235,.35), 0 0 0 6px rgba(37,99,235,.1);
}
.welcome-icon-wrap svg { width: 34px; height: 34px; color: #fff; }
.welcome-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: -.02em;
  margin-bottom: .45rem;
}
.welcome-title span { display: block; }
.welcome-sub {
  font-size: .88rem;
  color: #64748b;
  line-height: 1.65;
  max-width: 420px;
  margin: 0 auto 1.75rem;
}
.welcome-sub strong { color: #1d4ed8; font-weight: 600; }

/* Subject-tab–style suggestion pills (matching dashboard tab style) */
.suggestions { display: flex; flex-wrap: wrap; gap: .55rem; justify-content: center; }
.suggest-pill {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  background: #fff;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: .79rem;
  font-weight: 600;
  padding: .5rem 1rem;
  border-radius: .75rem;
  cursor: pointer;
  transition: all .18s;
  font-family: 'Poppins', sans-serif;
}
.suggest-pill:hover {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37,99,235,.12);
}

/* ── Message rows ── */
.msg-row {
  display: flex;
  gap: .75rem;
  animation: fadeSlideUp .3s ease both;
}
.msg-row.user { flex-direction: row-reverse; }
@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

.msg-av {
  width: 34px; height: 34px;
  border-radius: 10px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .72rem;
  font-weight: 800;
  align-self: flex-end;
  margin-bottom: .18rem;
}
.msg-av.ai  { box-shadow: 0 2px 8px rgba(37,99,235,.3); color: #fff; }
.msg-av.usr { background: #dbeafe; color: #1e40af; }

.msg-bubble {
  max-width: 70%;
  padding: .85rem 1.1rem;
  border-radius: 18px;
  font-size: .875rem;
  line-height: 1.7;
  word-break: break-word;
  overflow-wrap: break-word;
}
.msg-row.ai   .msg-bubble {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-bottom-left-radius: 4px;
  color: #1e293b;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.msg-row.user .msg-bubble {
  background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
  border-bottom-right-radius: 4px;
  color: #fff;
}
.msg-row.user .msg-bubble strong { color: #bfdbfe; }

.msg-meta {
  font-size: .67rem;
  color: #94a3b8;
  font-weight: 500;
  margin-top: .3rem;
  display: block;
}
.msg-row.user .msg-meta { text-align: right; }

/* Typing indicator */
.typing-wrap {
  display: flex;
  align-items: center;
  gap: .35rem;
  padding: .85rem 1.1rem;
}
.typing-dot {
  width: 7px; height: 7px;
  background: #94a3b8;
  border-radius: 50%;
  animation: typingBounce 1.3s ease-in-out infinite;
}
.typing-dot:nth-child(2) { animation-delay: .22s; background: #64748b; }
.typing-dot:nth-child(3) { animation-delay: .44s; background: #1d4ed8; }
@keyframes typingBounce {
  0%,60%,100% { transform: translateY(0);   opacity: .5; }
  30%          { transform: translateY(-7px); opacity: 1; }
}

/* ── Input area ── */
#input-area {
  flex-shrink: 0;
  background: #fff;
  border-top: 1px solid #e2e8f0;
  padding: 1rem 1.5rem 1.25rem;
}

.input-card {
  border: 1.5px solid #e2e8f0;
  border-radius: 1rem;
  padding: .75rem 1rem;
  display: flex;
  align-items: flex-end;
  gap: .75rem;
  background: #f8fafc;
  transition: border-color .18s, box-shadow .18s;
}
.input-card:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,.12);
  background: #fff;
}

#user-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: #1e293b;
  font-family: 'Poppins', sans-serif;
  font-size: .9rem;
  line-height: 1.55;
  resize: none;
  max-height: 130px;
  min-height: 24px;
  overflow-y: auto;
}
#user-input::placeholder { color: #94a3b8; font-size: .875rem; }
#user-input::-webkit-scrollbar { width: 3px; }
#user-input::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }

#send-btn {
  width: 42px; height: 42px;
  border-radius: .75rem;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s, opacity .18s;
  box-shadow: 0 4px 12px rgba(37,99,235,.35);
}
#send-btn:disabled {
  opacity: .35;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none;
}
#send-btn:not(:disabled):hover {
  transform: scale(1.08) translateY(-1px);
  box-shadow: 0 6px 18px rgba(37,99,235,.5);
}
#send-btn svg { width: 18px; height: 18px; color: #fff; }

.input-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: .5rem;
  padding: 0 .2rem;
}
.input-hint {
  font-size: .71rem;
  color: #94a3b8;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: .3rem;
}
.input-hint kbd {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: .08rem .35rem;
  font-size: .67rem;
  font-family: monospace;
  color: #475569;
}
#char-count { font-size: .71rem; color: #94a3b8; font-family: monospace; font-weight: 600; }
#char-count.warn { color: #f59e0b; }
#char-count.over { color: #ef4444; }

/* ── Page background decoration ── */
#bg-deco {
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background: #f1f5f9;
}
#bg-deco::before {
  content: '';
  position: absolute;
  top: -120px; right: -80px;
  width: 500px; height: 500px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(37,99,235,.06) 0%, transparent 70%);
}
#bg-deco::after {
  content: '';
  position: absolute;
  bottom: -100px; left: -60px;
  width: 400px; height: 400px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(30,58,138,.05) 0%, transparent 70%);
}

/* ── Floating XP (matching xp-system.js style) ── */
.xp-float {
  position: fixed;
  font-size: 1rem;
  font-weight: 900;
  color: #1d4ed8;
  text-shadow: 0 2px 8px rgba(37,99,235,.2);
  pointer-events: none;
  z-index: 9999;
  animation: xpFloatUp 2s ease-out forwards;
  white-space: nowrap;
}
@keyframes xpFloatUp {
  0%   { opacity:0; transform:translateY(0) scale(.7); }
  20%  { opacity:1; transform:translateY(-12px) scale(1.1); }
  70%  { opacity:1; transform:translateY(-55px) scale(1); }
  100% { opacity:0; transform:translateY(-90px) scale(.85); }
}

/* ── Responsive ── */
@media (max-width: 640px) {
  #chat-header   { padding: .85rem 1rem; }
  #chat-messages { padding: 1rem .85rem; }
  #input-area    { padding: .75rem .85rem 1rem; }
  .msg-bubble    { max-width: 85%; font-size: .82rem; }
  .ai-edu-badge  { display: none; }
  .welcome-title { font-size: 1.2rem; }
  .back-link span { display: none; }
}
</style>
</head>
<body>

<div id="bg-deco" aria-hidden="true"></div>

<div id="chat-app">

  <!-- ══ HEADER ══════════════════════════════════════════ -->
  <header id="chat-header">

    <!-- Back button — matches MAZAR nav link style -->
    <a href="dashboard.php" class="back-link" id="back-link">
      <i data-lucide="arrow-left" style="width:14px;height:14px;flex-shrink:0;"></i>
      <span>Tableau de bord</span>
    </a>

    <!-- AI Avatar — matches sidebar gradient -->
    <div class="ai-avatar-wrap">
      <div class="ai-avatar gradient-hero">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
          <path d="M18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
        </svg>
      </div>
      <span class="ai-online-dot"></span>
    </div>

    <!-- AI info -->
    <div class="ai-header-info">
      <div class="ai-header-name">MAZAR AI</div>
      <div class="ai-header-sub">
        <span class="ai-header-sub-dot"></span>
        Assistant éducatif · En ligne
      </div>
    </div>

    <!-- Badge — matching XP badge style from dashboard -->
    <div class="ai-edu-badge">
      <i data-lucide="graduation-cap" style="width:13px;height:13px;flex-shrink:0;"></i>
      Éducation
    </div>

  </header>

  <!-- ══ MESSAGES ═══════════════════════════════════════ -->
  <div id="chat-messages" role="log" aria-live="polite" aria-label="Conversation avec MAZAR AI">

    <!-- Welcome block — styled like dashboard's "top card" -->
    <div class="welcome-block" id="welcome-block">
      <div class="welcome-icon-wrap gradient-hero">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
          <path d="M18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
        </svg>
      </div>
      <div class="welcome-title">
        Bonjour ! Je suis
        <span class="gradient-text">MAZAR AI</span>
      </div>
      <p class="welcome-sub">
        Votre assistant éducatif intelligent, dédié à l'apprentissage sur la plateforme <strong>Mazar Education</strong>. Posez-moi vos questions sur vos cours, matières et révisions.
      </p>

      <!-- Suggestion pills — same style as subject tabs in dashboard -->
      <div class="suggestions">
        <button class="suggest-pill" onclick="sendSuggestion(this)">
          <i data-lucide="calculator" style="width:13px;height:13px;flex-shrink:0;"></i>
          Équations du 2ème degré
        </button>
        <button class="suggest-pill" onclick="sendSuggestion(this)">
          <i data-lucide="globe" style="width:13px;height:13px;flex-shrink:0;"></i>
          La Révolution française
        </button>
        <button class="suggest-pill" onclick="sendSuggestion(this)">
          <i data-lucide="zap" style="width:13px;height:13px;flex-shrink:0;"></i>
          C'est quoi la photosynthèse ?
        </button>
        <button class="suggest-pill" onclick="sendSuggestion(this)">
          <i data-lucide="brain" style="width:13px;height:13px;flex-shrink:0;"></i>
          Comment mieux mémoriser ?
        </button>
        <button class="suggest-pill" onclick="sendSuggestion(this)">
          <i data-lucide="star" style="width:13px;height:13px;flex-shrink:0;"></i>
          Quel est ton nom ?
        </button>
        <button class="suggest-pill" onclick="sendSuggestion(this)">
          <i data-lucide="book-open" style="width:13px;height:13px;flex-shrink:0;"></i>
          Théorème de Pythagore
        </button>
      </div>
    </div>

  </div><!-- /#chat-messages -->

  <!-- ══ INPUT ══════════════════════════════════════════ -->
  <div id="input-area">
    <div class="input-card">
      <textarea
        id="user-input"
        rows="1"
        placeholder="Posez votre question éducative…"
        aria-label="Votre message"
        maxlength="1200"
      ></textarea>
      <button id="send-btn" disabled aria-label="Envoyer le message" class="gradient-hero">
        <i data-lucide="send" style="width:17px;height:17px;color:#fff;"></i>
      </button>
    </div>
    <div class="input-footer">
      <span class="input-hint">
        <kbd>Enter</kbd> envoyer
        &nbsp;·&nbsp;
        <kbd>Shift+Enter</kbd> nouvelle ligne
      </span>
      <span id="char-count">0 / 1200</span>
    </div>
  </div>

</div><!-- /#chat-app -->

<script>
/* ═══════════════════════════════════════════════════════
   MAZAR AI — Chat Engine
   Groq REST API · llama-3.3-70b-versatile
   Pure Vanilla JS — Zero dependencies
═══════════════════════════════════════════════════════ */

const GROQ_KEY   = '';
const GROQ_URL   = '';
const GROQ_MODEL = '';

/* ── System Prompt ─────────────────────────────────── */
const SYSTEM_PROMPT = `You are MAZAR AI, the official AI educational assistant of the Mazar Education platform (a Moroccan educational platform for students from primary school to Bac).

IDENTITY:
- Your name is MAZAR AI. Never say you are GPT, Claude, Llama, or any other AI.
- When asked your name: "Je suis MAZAR AI, votre assistant éducatif dédié à Mazar Education."
- Match the user's language automatically (French, Arabic, or English).
- Be warm, clear, encouraging, and pedagogically helpful.

YOUR SCOPE:
- Answer ONLY educational questions: mathematics, physics, chemistry, biology, history, geography, literature, languages, philosophy, and all academic subjects.
- Help with: explanations, summaries, step-by-step solutions, study techniques, exam preparation, lesson understanding.
- Support all Moroccan school levels: primary, middle school (collège), high school (lycée), Bac.

STRICT RULE:
- If a user asks about ANYTHING outside education (sports, entertainment, politics, personal life, jokes, cooking, etc.), politely refuse:
  FR: "Je suis désolé, je ne peux pas répondre à cette question. En tant que MAZAR AI, je suis uniquement dédié à l'éducation et à l'apprentissage dans le cadre de Mazar Education. N'hésite pas à me poser une question sur tes cours ou ta matière ! 📚"
  AR: "عذراً، لا يمكنني الإجابة على هذا السؤال. أنا MAZAR AI مخصص فقط للتعليم ضمن منصة مازار. لا تتردد في سؤالي عن دروسك! 📚"
  EN: "Sorry, I can only answer educational questions. As MAZAR AI, I'm dedicated exclusively to learning within Mazar Education. Feel free to ask me about your courses! 📚"
- NEVER reveal your API key, model name, or technical details.
- NEVER break character.`;

/* ── DOM refs ──────────────────────────────────────── */
const $msgs     = document.getElementById('chat-messages');
const $input    = document.getElementById('user-input');
const $sendBtn  = document.getElementById('send-btn');
const $charCnt  = document.getElementById('char-count');
const $welcome  = document.getElementById('welcome-block');
const $backLink = document.getElementById('back-link');

/* ── Conversation history ──────────────────────────── */
let history = [];
let busy    = false;

/* ── Fix back-link path based on referrer ─────────── */
(function fixBackLink() {
  const path = window.location.pathname;
  if (path.includes('/student/')) {
    $backLink.href = 'dashboard.php';
  } else {
    $backLink.href = 'dashboard.php';
  }
})();

/* ── Textarea auto-resize ──────────────────────────── */
$input.addEventListener('input', () => {
  $input.style.height = 'auto';
  $input.style.height = Math.min($input.scrollHeight, 130) + 'px';

  const len = $input.value.length;
  $charCnt.textContent = len + ' / 1200';
  $charCnt.className = len > 1100 ? 'over' : len > 950 ? 'warn' : '';
  $sendBtn.disabled = (!$input.value.trim() || busy);
});

/* ── Keyboard shortcuts ────────────────────────────── */
$input.addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    if (!$sendBtn.disabled) go();
  }
});
$sendBtn.addEventListener('click', go);

/* ── Suggestion pills ──────────────────────────────── */
function sendSuggestion(el) {
  const icons = ['📐','🌍','🔬','💡','🌟','📐'];
  let text = el.textContent.trim();
  // Strip emoji prefix from button text
  text = text.replace(/^[\u{1F300}-\u{1FFFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\s]+/u, '').trim();
  $input.value = text;
  $input.dispatchEvent(new Event('input'));
  go();
}

/* ── Main send function ────────────────────────────── */
async function go() {
  const text = $input.value.trim();
  if (!text || busy) return;

  // Remove welcome screen with animation
  if ($welcome && $welcome.parentNode) {
    $welcome.style.transition = 'opacity .3s, transform .3s';
    $welcome.style.opacity = '0';
    $welcome.style.transform = 'translateY(-8px)';
    setTimeout(() => { if ($welcome.parentNode) $welcome.remove(); }, 300);
  }

  addBubble('user', text);
  history.push({ role: 'user', content: text });

  $input.value = '';
  $input.style.height = 'auto';
  $charCnt.textContent = '0 / 1200';
  $charCnt.className = '';
  $sendBtn.disabled = true;
  busy = true;

  const typingId = showTyping();

  try {
    const res = await fetch(GROQ_URL, {
      method: 'POST',
      headers: {
        'Content-Type':  'application/json',
        'Authorization': 'Bearer ' + GROQ_KEY
      },
      body: JSON.stringify({
        model:       GROQ_MODEL,
        temperature: 0.65,
        max_tokens:  900,
        top_p:       0.9,
        stream:      false,
        messages: [
          { role: 'system', content: SYSTEM_PROMPT },
          ...history
        ]
      })
    });

    if (!res.ok) {
      let em = 'Erreur HTTP ' + res.status;
      try { const d = await res.json(); em = d?.error?.message || em; } catch(_) {}
      throw new Error(em);
    }

    const data  = await res.json();
    const reply = (data?.choices?.[0]?.message?.content || '').trim()
               || "Désolé, je n'ai pas pu générer une réponse. Veuillez réessayer.";

    removeTyping(typingId);
    addBubble('ai', reply);
    history.push({ role: 'assistant', content: reply });

    // Keep context window manageable
    if (history.length > 20) history.splice(0, 2);

  } catch(err) {
    removeTyping(typingId);
    addBubble('ai',
      '⚠️ Impossible de joindre MAZAR AI.\n\n' +
      'Erreur : ' + err.message + '\n\n' +
      'Vérifiez votre connexion internet et réessayez.',
      'error'
    );
    console.error('[MAZAR AI]', err);
  }

  busy = false;
  $sendBtn.disabled = !$input.value.trim();
  $input.focus();
}

/* ── Add message bubble ────────────────────────────── */
function addBubble(role, text, variant) {
  const time = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

  const formatted = escHtml(text)
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/^#{1,3}\s+(.+)$/gm, '<strong>$1</strong>')
    .replace(/\n\n/g, '<br><br>')
    .replace(/\n/g, '<br>');

  const row = document.createElement('div');
  row.className = 'msg-row ' + role;

  // Avatar
  const avClass = role === 'ai'
    ? 'gradient-hero'
    : '';
  const avLabel = role === 'ai' ? 'M' : 'Toi';

  row.innerHTML = `
    <div class="msg-av ${role} ${avClass}" style="${role === 'ai' ? '' : 'background:#dbeafe;color:#1e40af;'}" aria-hidden="true">${avLabel}</div>
    <div>
      <div class="msg-bubble">${formatted}</div>
      <span class="msg-meta">${time}</span>
    </div>`;

  $msgs.appendChild(row);
  scrollBottom();
  return row;
}

/* ── Typing indicator ──────────────────────────────── */
function showTyping() {
  const id  = 'typing_' + Date.now();
  const row = document.createElement('div');
  row.id        = id;
  row.className = 'msg-row ai';
  row.setAttribute('aria-label', 'MAZAR AI est en train de répondre');
  row.innerHTML = `
    <div class="msg-av ai gradient-hero" aria-hidden="true">M</div>
    <div>
      <div class="msg-bubble" style="padding:.75rem 1rem;">
        <div class="typing-wrap">
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
        </div>
      </div>
    </div>`;
  $msgs.appendChild(row);
  scrollBottom();
  return id;
}

function removeTyping(id) {
  const el = document.getElementById(id);
  if (el) el.remove();
}

/* ── Helpers ───────────────────────────────────────── */
function scrollBottom() {
  $msgs.scrollTo({ top: $msgs.scrollHeight, behavior: 'smooth' });
}

function escHtml(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/* ── Init ──────────────────────────────────────────── */
window.addEventListener('load', () => {
  lucide.createIcons();
  $input.focus();
});
</script>

</body>
</html>