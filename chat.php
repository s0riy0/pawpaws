<?php
require 'config.php';
require_login();
chat_schema();

$cid = 0;
$threads = [];
if (is_admin()) {
    if (!empty($_GET['c'])) $pdo->prepare('UPDATE messages SET is_read = 1 WHERE customer_id = ? AND sender_id = ?')->execute([(int)$_GET['c'], (int)$_GET['c']]);
    $threads = $pdo->query('SELECT u.id, u.full_name, m.body, m.created_at,
            (SELECT COUNT(*) FROM messages x WHERE x.customer_id = u.id AND x.sender_id = u.id AND x.is_read = 0) AS unread
        FROM users u JOIN messages m ON m.id = (SELECT MAX(id) FROM messages WHERE customer_id = u.id)
        ORDER BY m.id DESC')->fetchAll();
    $cid = (int)($_GET['c'] ?? ($threads[0]['id'] ?? 0));
    $cust = $pdo->prepare('SELECT full_name, phone, email FROM users WHERE id = ?');
    $cust->execute([$cid]);
    $cust = $cust->fetch();
} else {
    $cid = user()['id'];
}

$pet = null;
if (!empty($_GET['pet'])) {
    $st = $pdo->prepare('SELECT id, name, image FROM pets WHERE id = ?');
    $st->execute([(int)$_GET['pet']]);
    $pet = $st->fetch();
}

$title = 'แชท';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1><?= is_admin() ? 'แชทกับลูกค้า' : 'แชทกับ PawPaws' ?></h1><span class="muted small">อัปเดตอัตโนมัติทุก 3 วินาที</span></div>
  <div class="chat <?= is_admin() ? '' : 'single' ?>">
    <?php if (is_admin()): ?>
      <div class="threads" id="threads" data-cur="<?= $cid ?>">
        <?php if (!$threads): ?><p class="muted small" style="padding:16px">ยังไม่มีข้อความจากลูกค้า</p><?php endif; ?>
        <?php foreach ($threads as $t): ?>
          <a href="chat.php?c=<?= $t['id'] ?>" class="<?= $t['id'] == $cid ? 'on' : '' ?><?= $t['unread'] && $t['id'] != $cid ? ' unread' : '' ?>"><span class="cdot"></span><strong><?= e($t['full_name']) ?></strong><?php if ($t['unread'] && $t['id'] != $cid): ?><span class="cnt"><?= $t['unread'] ?></span><?php endif; ?><small><?= e($t['body']) ?></small></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($cid && (!is_admin() || $cust)): ?>
    <div class="chatbox">
      <div class="chat-head">
        <?php if (is_admin()): ?>
          <strong><?= e($cust['full_name']) ?></strong> <span class="small muted">· <?= e($cust['phone']) ?> · <?= e($cust['email']) ?></span>
        <?php else: ?>
          <strong>🐾 ทีมงาน PawPaws</strong> <span class="small muted">· สอบถามข้อมูลน้อง การจัดส่ง และการดูแล</span>
        <?php endif; ?>
        <?php if ($pet): ?><div class="small mt" style="margin-top:6px">กำลังสอบถามเกี่ยวกับ: <a class="hl" href="pet.php?id=<?= $pet['id'] ?>"><?= e($pet['name']) ?></a></div><?php endif; ?>
      </div>
      <div class="msgs" id="msgs"></div>
      <form class="chat-form" id="chatForm">
        <input name="body" id="body" autocomplete="off" placeholder="พิมพ์ข้อความ..." required maxlength="2000">
        <button class="btn">ส่ง</button>
      </form>
    </div>
    <?php else: ?>
      <div class="box center muted">เลือกห้องแชทจากรายชื่อด้านซ้าย</div>
    <?php endif; ?>
  </div>
</div>

<?php if ($cid && (!is_admin() || $cust)): ?>
<script>
(() => {
  const C = <?= $cid ?>, CSRF = <?= json_encode(csrf()) ?>;
  let pet = <?= $pet ? (int)$pet['id'] : 0 ?>, last = 0, first = true;
  const box = document.getElementById('msgs'), form = document.getElementById('chatForm'), input = document.getElementById('body');
  const esc = s => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  function render(res) {
    const list = res.msgs || [];
    box.querySelectorAll('.msg.me').forEach(d => d.classList.toggle('read', +d.dataset.id <= res.read_upto));
    if (!list.length) { first = false; return; }
    const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;
    list.forEach(m => {
      if (m.id <= last) return;
      last = m.id;
      const d = document.createElement('div');
      d.className = 'msg' + (m.mine ? ' me' : '') + (m.mine && m.id <= res.read_upto ? ' read' : '');
      d.dataset.id = m.id;
      d.innerHTML = (m.pet ? `<span class="ctx">🐾 เรื่อง: <a href="pet.php?id=${m.pet_id}">${esc(m.pet)}</a></span>` : '')
        + esc(m.body) + `<small>${m.mine ? 'คุณ' : esc(m.name)} · ${m.time}${m.mine ? '<span class="rs"></span>' : ''}</small>`;
      box.appendChild(d);
    });
    if (atBottom || list.some(m => m.mine)) box.scrollTop = box.scrollHeight;
    if (!first && list.some(m => !m.mine) && window.chatBeep) window.chatBeep();
    first = false;
  }
  async function load() {
    try { render(await (await fetch(`chat_api.php?c=${C}&after=${last}`, {cache: 'no-store'})).json()); } catch (e) {}
  }
  form.addEventListener('submit', async ev => {
    ev.preventDefault();
    const body = input.value.trim();
    if (!body) return;
    input.value = '';
    const fd = new FormData();
    fd.append('csrf', CSRF); fd.append('c', C); fd.append('body', body); fd.append('after', last);
    if (pet) { fd.append('pet', pet); pet = 0; }
    try { render(await (await fetch('chat_api.php', {method: 'POST', body: fd})).json()); } catch (e) {}
  });
  load().then(() => box.scrollTop = box.scrollHeight);
  setInterval(load, 3000);
})();
</script>
<?php endif; ?>
<?php include 'footer.php'; ?>
