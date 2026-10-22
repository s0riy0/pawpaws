<?php
require 'config.php';
require_admin();

$s = $pdo->query("SELECT
    (SELECT COUNT(*) FROM pets) AS pets,
    (SELECT COUNT(*) FROM pets WHERE status='available') AS available,
    (SELECT COUNT(*) FROM orders) AS orders,
    (SELECT COALESCE(SUM(total),0) FROM orders) AS revenue,
    (SELECT COUNT(*) FROM users WHERE role='customer') AS customers")->fetch();

$pets = $pdo->query('SELECT p.*, c.name AS cat, (SELECT COUNT(*) FROM pet_media m WHERE m.pet_id = p.id) AS media
                     FROM pets p JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();

$title = 'จัดการน้องๆ';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1>แผงควบคุมผู้ดูแลระบบ</h1><div class="row"><a class="btn ghost" href="fetch_images.php">บันทึกรูปตัวอย่างลงเครื่อง</a><a class="btn" href="pet_add.php">+ เพิ่มน้องใหม่</a></div></div>
  <div class="stats">
    <div class="stat"><small>น้องทั้งหมด</small><strong><?= $s['pets'] ?></strong><span class="small muted">พร้อมรับเลี้ยง <?= $s['available'] ?></span></div>
    <div class="stat"><small>คำสั่งซื้อ</small><strong><?= $s['orders'] ?></strong></div>
    <div class="stat"><small>ยอดขายรวม</small><strong class="hl"><?= money($s['revenue']) ?></strong></div>
    <div class="stat"><small>สมาชิก (ลูกค้า)</small><strong><?= $s['customers'] ?></strong></div>
  </div>
  <div class="tbl-wrap">
    <table>
      <thead><tr><th>น้อง</th><th>ประเภท</th><th>เพศ</th><th>อายุ</th><th class="num">ราคา</th><th>สื่ออัปเดต</th><th>สถานะ</th><th class="num">จัดการ</th></tr></thead>
      <tbody>
      <?php foreach ($pets as $p): ?>
        <tr>
          <td><a class="row" style="flex-wrap:nowrap" href="pet.php?id=<?= $p['id'] ?>"><span class="mini"><?= img_tag($p['image']) ?></span><span><strong><?= e($p['name']) ?></strong><br><span class="small muted"><?= e($p['breed']) ?></span></span></a></td>
          <td><?= e($p['cat']) ?></td>
          <td><?= $p['gender'] === 'male' ? '♂' : '♀' ?></td>
          <td><?= age_text($p['birth_date']) ?></td>
          <td class="num"><?= money($p['price']) ?></td>
          <td><?= $p['media'] ?> ไฟล์</td>
          <td><span class="status <?= $p['status'] === 'sold' ? 'sold' : 'ok' ?>"><?= $p['status'] === 'sold' ? 'ได้บ้านแล้ว' : 'พร้อมรับเลี้ยง' ?></span></td>
          <td class="num" style="white-space:nowrap"><a class="btn sm ghost" href="pet_edit.php?id=<?= $p['id'] ?>">แก้ไข</a> <a class="btn sm ghost" href="pet_delete.php?id=<?= $p['id'] ?>">ลบ</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'footer.php'; ?>
