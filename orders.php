<?php
require 'config.php';
require_login();

$sql = 'SELECT o.*, u.full_name, TIMESTAMPDIFF(SECOND, o.paid_at, NOW()) AS elapsed,
          (SELECT GROUP_CONCAT(p.name SEPARATOR ", ") FROM order_items oi JOIN pets p ON p.id = oi.pet_id WHERE oi.order_id = o.id) AS pets
        FROM orders o JOIN users u ON u.id = o.user_id'
     . (is_admin() ? '' : ' WHERE o.user_id = ?') . ' ORDER BY o.paid_at DESC';
$st = $pdo->prepare($sql);
$st->execute(is_admin() ? [] : [user()['id']]);
$orders = $st->fetchAll();

$title = is_admin() ? 'คำสั่งซื้อทั้งหมด' : 'ติดตามน้อง';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1><?= $title ?></h1><span class="muted"><?= count($orders) ?> คำสั่งซื้อ</span></div>
  <?php if (!$orders): ?>
    <div class="box center"><p class="muted">ยังไม่มีคำสั่งซื้อ</p><a class="btn" href="index.php#pets">เลือกดูน้องๆ</a></div>
  <?php else: ?>
  <div class="tbl-wrap">
    <table>
      <thead><tr><th>#</th><?php if (is_admin()): ?><th>ลูกค้า</th><?php endif; ?><th>น้อง</th><th>วันที่ชำระ</th><th>ชำระโดย</th><th class="num">ยอดรวม</th><th>สถานะ</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o):
          $done = $o['status'] === 'received';
          $label = $done ? 'รับน้องแล้ว' : TRACK_TEXT[track_step((int)$o['elapsed'])]; ?>
        <tr>
          <td><?= $o['id'] ?></td>
          <?php if (is_admin()): ?><td><?= e($o['full_name']) ?></td><?php endif; ?>
          <td><?= e($o['pets']) ?></td>
          <td><?= thai_date($o['paid_at'], true) ?></td>
          <td><?= PAY_METHODS[$o['payment_method']] ?></td>
          <td class="num"><?= money($o['total']) ?></td>
          <td><span class="status <?= $done ? 'ok' : 'sold' ?>"><?= e($label) ?></span></td>
          <td><a class="btn sm ghost" href="track.php?id=<?= $o['id'] ?>">ติดตาม</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
