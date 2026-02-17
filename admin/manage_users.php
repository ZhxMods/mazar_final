<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

$lang      = getCurrentLang();
$dir       = getDirection();
$db        = getDB();
$pageTitle = t('manage_users');
$msg       = '';
$msgType   = 'success';

// ── Handle POST Actions ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['user_id'] ?? 0);

    if ($uid && $action === 'toggle_status') {
        $current = $db->prepare("SELECT status FROM users WHERE id=?");
        $current->execute([$uid]);
        $row     = $current->fetch();
        $newStatus = ($row['status'] === 'active') ? 'banned' : 'active';
        $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newStatus, $uid]);
        $msg = "Statut mis à jour : {$newStatus}";
        logActivity($_SESSION[SESS_USER_ID], 'admin_action', "User #{$uid} status → {$newStatus}");
    }

    if ($uid && $action === 'add_xp') {
        $amount = (int)($_POST['xp_amount'] ?? 0);
        if ($amount > 0 && $amount <= 9999) {
            awardXP($uid, $amount, 'Admin XP bonus');
            $msg = "+{$amount} XP ajoutés à l'utilisateur #{$uid}";
        } else {
            $msg = 'Montant XP invalide (1–9999)';
            $msgType = 'error';
        }
    }

    if ($uid && $action === 'reset_xp') {
        $db->prepare("UPDATE users SET xp_points=0, level=1 WHERE id=?")->execute([$uid]);
        $msg = "XP remis à 0 pour l'utilisateur #{$uid}";
        logActivity($_SESSION[SESS_USER_ID], 'admin_action', "User #{$uid} XP reset");
    }
}

// ── Fetch Users ───────────────────────────────────────────────
$users = $db->query(
    "SELECT u.id, u.full_name, u.email, u.role, u.xp_points, u.level, u.status, u.created_at,
            lv.name_fr AS grade,
            (SELECT COUNT(*) FROM user_lesson_completions ulc WHERE ulc.user_id=u.id) AS completions
     FROM users u
     JOIN levels lv ON lv.id = u.grade_level_id
     ORDER BY u.xp_points DESC"
)->fetchAll();

require dirname(__DIR__) . '/admin/_layout.php';
?>

<!-- ── Message ── -->
<?php if($msg): ?>
<div class="mb-5 p-4 rounded-xl text-sm font-semibold flex items-center gap-2
     <?= $msgType==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
  <?= $msgType==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- ── Stats Row ── -->
<div class="grid grid-cols-3 gap-4 mb-6">
  <?php
  $active  = count(array_filter($users, fn($u)=>$u['status']==='active'));
  $banned  = count($users) - $active;
  $totalXP = array_sum(array_column($users,'xp_points'));
  $cards   = [
    ['Étudiants actifs', $active,  'user-check','#10B981'],
    ['Suspendus',        $banned,  'user-x',    '#EF4444'],
    ['XP Total',         number_format($totalXP), 'zap', '#F59E0B'],
  ];
  foreach($cards as [$label,$val,$icon,$color]):
  ?>
  <div class="stat-card">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:<?= $color ?>18">
      <i data-lucide="<?= $icon ?>" class="w-5 h-5" style="color:<?= $color ?>"></i>
    </div>
    <div class="text-2xl font-black text-gray-900"><?= $val ?></div>
    <div class="text-gray-500 text-sm"><?= $label ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Users Table ── -->
<div class="admin-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="dt-table w-full text-sm">
      <thead>
        <tr class="bg-gray-50 text-gray-600 text-left">
          <th class="px-4 py-3 font-semibold">#</th>
          <th class="px-4 py-3 font-semibold"><?= t('full_name') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('email') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('grade_level') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('xp_points') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('total_completions') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('status') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('joined_at') ?></th>
          <th class="px-4 py-3 font-semibold"><?= t('actions') ?></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach($users as $u): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-3 text-gray-400 text-xs"><?= $u['id'] ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs flex-shrink-0">
                <?= mb_strtoupper(mb_substr($u['full_name'],0,1)) ?>
              </div>
              <div>
                <div class="font-semibold text-gray-900"><?= htmlspecialchars($u['full_name']) ?></div>
                <div class="text-xs text-gray-400"><?= ucfirst($u['role']) ?></div>
              </div>
            </div>
          </td>
          <td class="px-4 py-3 text-gray-500 text-xs"><?= htmlspecialchars($u['email']) ?></td>
          <td class="px-4 py-3 text-gray-500 text-xs"><?= htmlspecialchars($u['grade']) ?></td>
          <td class="px-4 py-3">
            <div class="font-bold text-yellow-600"><?= number_format($u['xp_points']) ?> XP</div>
            <div class="text-gray-400 text-xs">Niv. <?= $u['level'] ?></div>
          </td>
          <td class="px-4 py-3 text-gray-600 text-sm"><?= $u['completions'] ?></td>
          <td class="px-4 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $u['status']==='active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
              <?= $u['status']==='active' ? t('active') : t('banned') ?>
            </span>
          </td>
          <td class="px-4 py-3 text-gray-400 text-xs"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td class="px-4 py-3">
            <?php if ($u['id'] != $_SESSION[SESS_USER_ID]): ?>
            <div class="flex flex-wrap gap-1">
              <!-- Toggle Status -->
              <form method="POST" class="inline">
                <?= csrfField() ?>
                <input type="hidden" name="action"  value="toggle_status">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button type="submit"
                        class="text-xs px-2 py-1 rounded-lg font-semibold transition <?= $u['status']==='active' ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' ?>">
                  <?= $u['status']==='active' ? t('ban_user') : t('activate_user') ?>
                </button>
              </form>

              <!-- Add XP -->
              <button onclick="openXpModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['full_name'])) ?>')"
                      class="text-xs px-2 py-1 rounded-lg font-semibold bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition">
                ⚡ <?= t('add_xp') ?>
              </button>

              <!-- Reset XP -->
              <form method="POST" class="inline" onsubmit="return confirm('Réinitialiser le XP ?')">
                <?= csrfField() ?>
                <input type="hidden" name="action"  value="reset_xp">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button type="submit"
                        class="text-xs px-2 py-1 rounded-lg font-semibold bg-gray-50 text-gray-600 hover:bg-gray-100 transition">
                  <?= t('reset_xp') ?>
                </button>
              </form>
            </div>
            <?php else: ?>
            <span class="text-gray-300 text-xs">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?>
        <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400"><?= t('no_results') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add XP Modal -->
<div id="xp-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeXpModal()">
  <div class="bg-white rounded-2xl p-7 w-full max-w-sm shadow-2xl">
    <h3 class="font-black text-gray-900 text-lg mb-1">Ajouter XP</h3>
    <p id="xp-user-name" class="text-gray-500 text-sm mb-5"></p>
    <form method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="action"  value="add_xp">
      <input type="hidden" name="user_id" id="xp-uid" value="">
      <label class="block text-sm font-semibold text-gray-700 mb-2">Montant XP (1–9999)</label>
      <input type="number" name="xp_amount" min="1" max="9999" value="50"
             class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm mb-4">
      <div class="flex gap-3">
        <button type="submit" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 rounded-xl transition">⚡ Ajouter</button>
        <button type="button" onclick="closeXpModal()" class="px-5 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
function openXpModal(uid, name) {
  document.getElementById('xp-uid').value = uid;
  document.getElementById('xp-user-name').textContent = 'Étudiant : ' + name;
  document.getElementById('xp-modal').classList.remove('hidden');
}
function closeXpModal() {
  document.getElementById('xp-modal').classList.add('hidden');
}
</script>

<?php require dirname(__DIR__) . '/admin/_layout_end.php'; ?>
