<?php
require 'config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT o.*, u.full_name, TIMESTAMPDIFF(SECOND, o.paid_at, NOW()) AS elapsed FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
$st->execute([$id]);
$o = $st->fetch();
if (!$o || (!is_admin() && $o['user_id'] != user()['id'])) { flash('ไม่พบคำสั่งซื้อ', 'err'); redirect('orders.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm') {
    check_csrf();
    if (!is_admin() && $o['status'] === 'shipping' && track_step((int)$o['elapsed']) === 3) {
        $pdo->prepare("UPDATE orders SET status = 'received', received_at = NOW() WHERE id = ?")->execute([$id]);
        flash('ยืนยันการรับน้องเรียบร้อย ขอบคุณที่มอบบ้านอบอุ่นให้น้องนะ 🧡');
    }
    redirect("track.php?id=$id");
}

$it = $pdo->prepare('SELECT p.id, p.name, p.breed, p.image, oi.price FROM order_items oi JOIN pets p ON p.id = oi.pet_id WHERE oi.order_id = ?');
$it->execute([$id]);
$items = $it->fetchAll();

$received = $o['status'] === 'received';
$step = $received ? 4 : track_step((int)$o['elapsed']);
$steps = [
    ['💳', 'ชำระเงินเรียบร้อย', thai_date($o['paid_at'], true)],
    ['📦', TRACK_TEXT[1], 'เริ่มจัดส่งหลังชำระเงิน 5 วินาที'],
    ['🚚', TRACK_TEXT[2], 'อัปเดตเมื่อผ่านไป 10 วินาที'],
    ['📍', TRACK_TEXT[3], 'อัปเดตเมื่อผ่านไป 15 วินาที'],
    ['🏡', 'รับน้องเรียบร้อยแล้ว', $received ? thai_date($o['received_at'], true) : 'รอลูกค้ากดยืนยัน'],
];

$title = 'ติดตามคำสั่งซื้อ #' . $id;
include 'header.php';
?>
<div class="wrap page track">
  <div class="page-title">
    <div><h1>ติดตามน้อง · คำสั่งซื้อ #<?= $id ?></h1><span class="muted small">ผู้สั่ง: <?= e($o['full_name']) ?> · <?= PAY_METHODS[$o['payment_method']] ?></span></div>
    <a class="btn ghost sm" href="orders.php">← คำสั่งซื้อทั้งหมด</a>
  </div>

  <div id="live" class="live <?= $received ? 'doneb' : '' ?>"><?= e($received ? 'รับน้องเรียบร้อยแล้ว ขอให้มีความสุขกับสมาชิกใหม่' : TRACK_TEXT[track_step((int)$o['elapsed'])]) ?></div>

  <ul class="timeline" id="tl">
    <?php foreach ($steps as $i => $s): ?>
      <li data-i="<?= $i ?>" class="<?= $i <= $step ? 'done' : '' ?> <?= $i === $step && !$received ? 'now' : '' ?>">
        <span class="dot"><?= $s[0] ?></span><strong><?= e($s[1]) ?></strong><span class="small"><?= e($s[2]) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>

  <form method="post" id="confirm" style="<?= $step === 3 && !$received && !is_admin() ? '' : 'display:none' ?>" class="box center">
    <?= csrf_field() ?><input type="hidden" name="action" value="confirm">
    <p><strong>น้องมาถึงแล้ว!</strong> กรุณาออกไปรับน้อง แล้วกดยืนยันด้านล่าง</p>
    <label class="row" style="justify-content:center;font-weight:400"><input type="checkbox" required style="width:auto"> ฉันได้รับน้องเรียบร้อยแล้ว</label>
    <button class="btn mt">✅ ยืนยันว่ารับน้องแล้ว</button>
  </form>

  <div class="tbl-wrap mt">
    <table>
      <thead><tr><th>น้อง</th><th class="num">ราคา</th></tr></thead>
      <tbody>
      <?php foreach ($items as $i): ?>
        <tr><td><a class="row" style="flex-wrap:nowrap" href="pet.php?id=<?= $i['id'] ?>"><span class="mini"><?= img_tag($i['image']) ?></span><span><strong><?= e($i['name']) ?></strong><br><span class="small muted"><?= e($i['breed']) ?></span></span></a></td><td class="num"><?= money($i['price']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot><tr><td>รวม</td><td class="num"><?= money($o['total']) ?></td></tr></tfoot>
    </table>
    <p class="small muted" style="padding:0 16px">จัดส่งถึง: <?= e($o['ship_name']) ?> (<?= e($o['ship_phone']) ?>) – <?= e($o['ship_address']) ?></p>
  </div>
</div>

<?php if (!$received): ?>
<script>
(() => {
  const live = document.getElementById('live'), confirmBox = document.getElementById('confirm');
  const items = document.querySelectorAll('#tl li');
  async function poll() {
    try {
      const d = await (await fetch('track_api.php?id=<?= $id ?>', {cache: 'no-store'})).json();
      live.textContent = d.text;
      live.classList.toggle('doneb', d.received);
      items.forEach(li => {
        const i = +li.dataset.i;
        li.classList.toggle('done', i <= d.step);
        li.classList.toggle('now', i === d.step && !d.received);
      });
      confirmBox.style.display = d.can_confirm ? '' : 'none';
      if (d.received) return location.reload();
    } catch (e) {}
    setTimeout(poll, 5000); // ส่งข้อมูลทุก 5 วินาที
  }
  setTimeout(poll, 5000);
})();
</script>
<?php endif; ?>
<?php include 'footer.php'; ?>
