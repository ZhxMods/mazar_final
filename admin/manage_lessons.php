<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

$lang      = getCurrentLang();
$dir       = getDirection();
$db        = getDB();
$pageTitle = t('manage_lessons');
$msg       = '';
$msgType   = 'success';

// ── Handle POST Actions ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $titleAr   = trim($_POST['title_ar']   ?? '');
        $titleFr   = trim($_POST['title_fr']   ?? '');
        $titleEn   = trim($_POST['title_en']   ?? '');
        $type      = $_POST['type']       ?? 'video';
        $url       = trim($_POST['url']        ?? '');
        $levelId   = (int)($_POST['level_id']  ?? 0);
        $subjectId = (int)($_POST['subject_id']?? 0);
        $thumb     = trim($_POST['thumbnail']  ?? '');
        $duration  = (int)($_POST['duration']  ?? 0);
        $order     = (int)($_POST['order_num'] ?? 0);
        $published = isset($_POST['published']) ? 1 : 0;

        if (!$titleFr || !$url || !$levelId || !$subjectId) {
            $msg = 'Titre (FR), URL, Niveau et Matière sont obligatoires.';
            $msgType = 'error';
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $msg = 'URL invalide. Elle doit commencer par https://';
            $msgType = 'error';
        } else {
            if ($action === 'add') {
                $db->prepare(
                    "INSERT INTO lessons (title_ar,title_fr,title_en,type,url,thumbnail,level_id,subject_id,duration,xp_reward,order_num,published)
                     VALUES (?,?,?,?,?,?,?,?,?,10,?,?)"
                )->execute([$titleAr,$titleFr,$titleEn,$type,$url,$thumb,$levelId,$subjectId,$duration,$order,$published]);
                $msg = 'Cours ajouté avec succès !';
            } else {
                $id = (int)($_POST['edit_id'] ?? 0);
                $db->prepare(
                    "UPDATE lessons SET title_ar=?,title_fr=?,title_en=?,type=?,url=?,thumbnail=?,level_id=?,subject_id=?,duration=?,order_num=?,published=? WHERE id=?"
                )->execute([$titleAr,$titleFr,$titleEn,$type,$url,$thumb,$levelId,$subjectId,$duration,$order,$published,$id]);
                $msg = 'Cours modifié avec succès !';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['delete_id'] ?? 0);
        $db->prepare("DELETE FROM lessons WHERE id=?")->execute([$id]);
        $msg = 'Cours supprimé.';
        $msgType = 'success';
    }
}

// ── Fetch Data ────────────────────────────────────────────────
$levels  = getAllLevels();
$lessons = $db->query(
    "SELECT l.id, l.title_fr, l.title_ar, l.type, l.url, l.published, l.order_num, l.duration,
            lv.name_fr AS level_name, s.name_fr AS subject_name
     FROM lessons l
     JOIN levels lv ON lv.id = l.level_id
     JOIN subjects s ON s.id = l.subject_id
     ORDER BY lv.order_num ASC, l.order_num ASC, l.created_at DESC"
)->fetchAll();

// Subjects JSON for JS (level_id → subjects)
$allSubjects = $db->query("SELECT id, name_fr AS name, level_id FROM subjects ORDER BY level_id, order_num")->fetchAll();
$subjectsByLevel = [];
foreach($allSubjects as $s) {
    $subjectsByLevel[$s['level_id']][] = $s;
}

require dirname(__DIR__) . '/admin/_layout.php';
?>

<!-- ── Success/Error Message ── -->
<?php if($msg): ?>
<div class="mb-5 p-4 rounded-xl text-sm font-semibold flex items-center gap-2
     <?= $msgType==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
  <?= $msgType==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- ── Action Bar ── -->
<div class="flex justify-between items-center mb-5">
  <p class="text-gray-500 text-sm"><?= count($lessons) ?> cours au total</p>
  <button onclick="openAddModal()"
          class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition text-sm">
    <i data-lucide="plus" class="w-4 h-4"></i>
    <?= t('add_lesson') ?>
  </button>
</div>

<!-- ── Lessons Table ── -->
<div class="admin-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="dt-table w-full text-sm">
      <thead>
        <tr class="bg-gray-50 text-gray-600 text-left">
          <th class="px-4 py-3 font-semibold">#</th>
          <th class="px-4 py-3 font-semibold"><?= t('lesson_title') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('grade_level') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('subject') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('lesson_type') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('status') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('actions') ?></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach($lessons as $lesson): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-3 text-gray-400 text-xs"><?= $lesson['id'] ?></td>
          <td class="px-4 py-3">
            <div class="font-semibold text-gray-900"><?= htmlspecialchars($lesson['title_fr']) ?></div>
            <div class="text-gray-400 text-xs"><?= htmlspecialchars($lesson['title_ar']) ?></div>
          </td>
          <td class="px-4 py-3 text-gray-600 text-xs"><?= htmlspecialchars($lesson['level_name']) ?></td>
          <td class="px-4 py-3 text-gray-600 text-xs"><?= htmlspecialchars($lesson['subject_name']) ?></td>
          <td class="px-4 py-3">
            <?php $typeColors=['video'=>'bg-blue-100 text-blue-700','pdf'=>'bg-green-100 text-green-700','book'=>'bg-purple-100 text-purple-700']; ?>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $typeColors[$lesson['type']] ?? 'bg-gray-100' ?>">
              <?= ucfirst($lesson['type']) ?>
            </span>
          </td>
          <td class="px-4 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $lesson['published'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= $lesson['published'] ? t('published') : t('draft') ?>
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex gap-2">
              <button onclick='openEditModal(<?= json_encode($lesson) ?>)'
                      class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition" title="<?= t('edit') ?>">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
              </button>
              <form method="POST" onsubmit="return confirm('<?= t('confirm_delete') ?>')">
                <?= csrfField() ?>
                <input type="hidden" name="action"    value="delete">
                <input type="hidden" name="delete_id" value="<?= $lesson['id'] ?>">
                <button type="submit" class="text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition" title="<?= t('delete') ?>">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($lessons)): ?>
        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400"><?= t('no_results') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ════════════════════════════════════════
     ADD/EDIT LESSON MODAL
════════════════════════════════════════ -->
<div id="lesson-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div class="px-7 py-5 border-b border-gray-100 flex items-center justify-between">
      <h2 id="modal-title" class="font-black text-gray-900 text-lg"><?= t('add_lesson') ?></h2>
      <button onclick="closeModal()" class="text-gray-400 hover:text-gray-700 p-2 rounded-xl hover:bg-gray-100 transition">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form id="lesson-form" method="POST" class="px-7 py-6 space-y-5">
      <?= csrfField() ?>
      <input type="hidden" name="action"  id="form-action"  value="add">
      <input type="hidden" name="edit_id" id="form-edit-id" value="">

      <!-- Titles row -->
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="label-sm"><?= t('title_ar') ?></label>
          <input type="text" name="title_ar" id="f-title_ar" class="inp" dir="rtl" placeholder="العنوان بالعربية">
        </div>
        <div>
          <label class="label-sm"><?= t('title_fr') ?> *</label>
          <input type="text" name="title_fr" id="f-title_fr" class="inp" placeholder="Titre en français" required>
        </div>
        <div>
          <label class="label-sm"><?= t('title_en') ?></label>
          <input type="text" name="title_en" id="f-title_en" class="inp" placeholder="Title in English">
        </div>
      </div>

      <!-- Level & Subject -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="label-sm"><?= t('grade_level') ?> *</label>
          <select name="level_id" id="f-level" class="inp" onchange="loadSubjects(this.value)" required>
            <option value=""><?= t('select_grade') ?></option>
            <?php foreach($levels as $lv): ?>
            <option value="<?= $lv['id'] ?>"><?= htmlspecialchars($lv['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label-sm"><?= t('subject') ?> *</label>
          <select name="subject_id" id="f-subject" class="inp" required>
            <option value="">— Sélectionner la matière —</option>
          </select>
        </div>
      </div>

      <!-- Type -->
      <div>
        <label class="label-sm"><?= t('lesson_type') ?> *</label>
        <select name="type" id="f-type" class="inp" onchange="updatePreviewType()">
          <option value="video"><?= t('video') ?></option>
          <option value="pdf"><?= t('pdf') ?></option>
          <option value="book"><?= t('book') ?></option>
        </select>
      </div>

      <!-- URL + Live Preview -->
      <div>
        <label class="label-sm"><?= t('lesson_url') ?> *</label>
        <div class="flex gap-3">
          <input type="url" name="url" id="f-url" class="inp flex-1" placeholder="https://..." required
                 oninput="updatePreview(this.value)">
          <a id="preview-test-link" href="#" target="_blank" rel="noopener"
             class="flex-shrink-0 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-1">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            <?= t('test_link') ?>
          </a>
        </div>
        <!-- Preview area -->
        <div id="url-preview" class="mt-3 hidden">
          <!-- YouTube embed -->
          <div id="yt-preview" class="hidden">
            <iframe id="yt-iframe" class="w-full rounded-xl border border-gray-200"
                    height="200" frameborder="0" allowfullscreen
                    allow="accelerometer; autoplay; encrypted-media; gyroscope"></iframe>
          </div>
          <!-- File preview -->
          <div id="file-preview" class="hidden flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <div id="file-icon" class="text-4xl"></div>
            <div>
              <div id="file-name" class="font-semibold text-gray-800 text-sm"></div>
              <div class="text-gray-400 text-xs">MediaFire / Lien externe</div>
            </div>
          </div>
        </div>
        <!-- Validation warning -->
        <div id="url-warning" class="hidden mt-2 text-amber-600 text-xs flex items-center gap-1">
          <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
          L'URL doit commencer par https://
        </div>
      </div>

      <!-- Thumbnail & Duration -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="label-sm"><?= t('thumbnail') ?> (URL)</label>
          <input type="url" name="thumbnail" id="f-thumb" class="inp" placeholder="https://img.youtube.com/...">
        </div>
        <div>
          <label class="label-sm"><?= t('duration') ?></label>
          <input type="number" name="duration" id="f-duration" class="inp" min="0" placeholder="45">
        </div>
      </div>

      <!-- Order + Published -->
      <div class="grid md:grid-cols-2 gap-4 items-center">
        <div>
          <label class="label-sm"><?= t('lesson_order') ?></label>
          <input type="number" name="order_num" id="f-order" class="inp" min="0" value="0">
        </div>
        <div class="flex items-center gap-3 pt-5">
          <input type="checkbox" name="published" id="f-published" value="1" checked
                 class="w-5 h-5 text-blue-600 rounded cursor-pointer">
          <label for="f-published" class="text-sm font-medium text-gray-700 cursor-pointer"><?= t('published') ?></label>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 pt-2">
        <button type="submit" id="save-btn"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
          <i data-lucide="save" class="w-4 h-4"></i>
          <?= t('save') ?>
        </button>
        <button type="button" onclick="closeModal()"
                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition">
          <?= t('cancel') ?>
        </button>
      </div>
    </form>
  </div>
</div>

<style>
  .inp { width:100%; padding:.625rem 1rem; border:1px solid #e5e7eb; border-radius:.75rem; font-size:.875rem; outline:none; transition: border-color .15s, box-shadow .15s; background:#fff; }
  .inp:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
  .label-sm { display:block; font-size:.8125rem; font-weight:600; color:#374151; margin-bottom:.375rem; }
</style>

<script>
const SUBJECTS_MAP = <?= json_encode($subjectsByLevel) ?>;

function loadSubjects(levelId, selectedId = null) {
  const sel = document.getElementById('f-subject');
  sel.innerHTML = '<option value="">— Sélectionner la matière —</option>';
  const subs = SUBJECTS_MAP[levelId] || [];
  subs.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.id;
    opt.textContent = s.name;
    if (selectedId && s.id == selectedId) opt.selected = true;
    sel.appendChild(opt);
  });
}

function openAddModal() {
  document.getElementById('modal-title').textContent = '<?= t('add_lesson') ?>';
  document.getElementById('form-action').value = 'add';
  document.getElementById('lesson-form').reset();
  document.getElementById('url-preview').classList.add('hidden');
  document.getElementById('lesson-modal').classList.remove('hidden');
  lucide.createIcons();
}

function openEditModal(lesson) {
  document.getElementById('modal-title').textContent = '<?= t('edit') ?> Cours #' + lesson.id;
  document.getElementById('form-action').value = 'edit';
  document.getElementById('form-edit-id').value = lesson.id;

  document.getElementById('f-title_ar').value  = lesson.title_ar || '';
  document.getElementById('f-title_fr').value  = lesson.title_fr || '';
  document.getElementById('f-title_en').value  = lesson.title_en || '';
  document.getElementById('f-type').value      = lesson.type     || 'video';
  document.getElementById('f-url').value       = lesson.url      || '';
  document.getElementById('f-thumb').value     = lesson.thumbnail|| '';
  document.getElementById('f-duration').value  = lesson.duration || 0;
  document.getElementById('f-order').value     = lesson.order_num|| 0;
  document.getElementById('f-published').checked = lesson.published == 1;

  loadSubjects(lesson.level_id, lesson.subject_id);
  document.getElementById('f-level').value = lesson.level_id;

  updatePreview(lesson.url);
  document.getElementById('lesson-modal').classList.remove('hidden');
  lucide.createIcons();
}

function closeModal() {
  document.getElementById('lesson-modal').classList.add('hidden');
}

function updatePreview(url) {
  const testLink    = document.getElementById('preview-test-link');
  const preview     = document.getElementById('url-preview');
  const ytDiv       = document.getElementById('yt-preview');
  const fileDiv     = document.getElementById('file-preview');
  const warning     = document.getElementById('url-warning');
  const saveBtn     = document.getElementById('save-btn');

  if (!url || url.trim() === '') {
    preview.classList.add('hidden');
    warning.classList.add('hidden');
    saveBtn.disabled = false;
    return;
  }

  if (!url.startsWith('https://')) {
    preview.classList.add('hidden');
    warning.classList.remove('hidden');
    saveBtn.disabled = true;
    return;
  }

  warning.classList.add('hidden');
  saveBtn.disabled = false;
  testLink.href = url;

  // YouTube?
  const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
  if (ytMatch) {
    const id = ytMatch[1];
    document.getElementById('yt-iframe').src = `https://www.youtube.com/embed/${id}`;
    ytDiv.classList.remove('hidden');
    fileDiv.classList.add('hidden');
    preview.classList.remove('hidden');
    // Auto-fill thumbnail
    const thumbField = document.getElementById('f-thumb');
    if (!thumbField.value) thumbField.value = `https://img.youtube.com/vi/${id}/hqdefault.jpg`;
    return;
  }

  // MediaFire / PDF
  ytDiv.classList.add('hidden');
  const type = document.getElementById('f-type').value;
  const icons = { video:'🎬', pdf:'📄', book:'📗' };
  const names = { video:'Fichier Vidéo', pdf:'Document PDF', book:'Livre' };
  document.getElementById('file-icon').textContent = icons[type] || '📎';
  document.getElementById('file-name').textContent = names[type] || 'Fichier';
  fileDiv.classList.remove('hidden');
  preview.classList.remove('hidden');
}

function updatePreviewType() {
  const url = document.getElementById('f-url').value;
  if (url) updatePreview(url);
}
</script>

<?php require dirname(__DIR__) . '/admin/_layout_end.php'; ?>
