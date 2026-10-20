<?php
require 'config.php';
require_customer();
$uid = user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pid = (int)($_POST['pet_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'add') {
        $st = $pdo->prepare("SELECT name FROM pets WHERE id = ? AND status = 'available'");
        $st->execute([$pid]);
        if ($name = $st->fetchColumn()) {
            $pdo->prepare('INSERT IGNORE INTO cart_items (user_id, pet_id) VALUES (?, ?)')->execute([$uid, $pid]);
            flash("เพิ่ม $name ลงตะกร้าแล้ว");
        } else {
            flash('น้องตัวนี้ได้บ้านแล้ว', 'err');
        }
    } elseif (($_POST['action'] ?? '') === 'remove') {
        $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND pet_id = ?')->execute([$uid, $pid]);
        flash('นำออกจากตะกร้าแล้ว');
    }
    redirect('cart.php');
}

$st = $pdo->prepare('SELECT p.*, c.name AS cat FROM cart_items ci JOIN pets p ON p.id = ci.pet_id JOIN categories c ON c.id = p.category_id WHERE ci.user_id = ? ORDER BY ci.added_at');
$st->execute([$uid]);
$items = $st->fetchAll();
$total = array_sum(array_map(fn($i) => $i['status'] === 'available' ? $i['price'] : 0, $items));
$avail = count(array_filter($items, fn($i) => $i['status'] === 'available'));

$title = 'ตะกร้า';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1>ตะกร้าของฉัน</h1><span class="muted"><?= count($items) ?> รายการ</span></div>
  <?php if (!$items): ?>
    <div class="box center"><p class="muted">ตะกร้ายังว่างอยู่ 🐾</p><a class="btn" href="index.php#pets">เลือกดูน้องๆ</a></div>
  <?php else: ?>
  <div class="split">
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>น้อง</th><th>ประเภท</th><th class="num">ราคา</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
          <tr>
            <td><div class="row" style="flex-wrap:nowrap"><div class="mini"><?= img_tag($i['image']) ?></div>
              <div><a href="pet.php?id=<?= $i['id'] ?>"><strong><?= e($i['name']) ?></strong></a><br><span class="small muted"><?= e($i['breed']) ?> · <?= age_text($i['birth_date']) ?></span>
              <?php if ($i['status'] === 'sold'): ?><br><span class="status sold">ได้บ้านแล้ว – ไม่สามารถสั่งซื้อได้</span><?php endif; ?></div></div></td>
            <td><?= e($i['cat']) ?></td>
            <td class="num"><?= money($i['price']) ?></td>
            <td class="num"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="pet_id" value="<?= $i['id'] ?>"><button class="btn sm ghost">ลบ</button></form></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="box">
      <h3>สรุปคำสั่งซื้อ</h3>
      <div class="sum-row"><span class="muted">จำนวนน้อง</span><span><?= $avail ?> ตัว</span></div>
      <div class="sum-row"><span class="muted">ค่าจัดส่ง</span><span>ฟรี</span></div>
      <div class="sum-row total"><span>รวมทั้งสิ้น</span><span class="hl"><?= money($total) ?></span></div>
      <a class="btn block mt" href="checkout.php" <?= $avail ? '' : 'disabled' ?>>ดำเนินการชำระเงิน</a>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
