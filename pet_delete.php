<?php
require 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)$_POST['id'];
    $st = $pdo->prepare('SELECT * FROM pets WHERE id = ?');
    $st->execute([$id]);
    if ($p = $st->fetch()) {
        $used = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE pet_id = ?');
        $used->execute([$id]);
        if ($used->fetchColumn()) {
            flash("ไม่สามารถลบ {$p['name']} ได้ เนื่องจากมีประวัติการสั่งซื้อ (ใช้ในรายงานยอดขาย)", 'err');
        } else {
            $files = $pdo->prepare('SELECT file FROM pet_media WHERE pet_id = ?');
            $files->execute([$id]);
            $pdo->prepare('DELETE FROM pets WHERE id = ?')->execute([$id]);
            foreach ($files->fetchAll(PDO::FETCH_COLUMN) as $f) delete_file($f);
            delete_file($p['image']);
            flash("ลบข้อมูล {$p['name']} เรียบร้อย");
        }
    }
    redirect('pet_delete.php');
}

$id = (int)($_GET['id'] ?? 0);
$pet = null;
if ($id) {
    $st = $pdo->prepare('SELECT p.*, c.name AS cat, (SELECT COUNT(*) FROM order_items oi WHERE oi.pet_id = p.id) AS ordered FROM pets p JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
    $st->execute([$id]);
    $pet = $st->fetch();
}
$pets = $pdo->query('SELECT p.*, c.name AS cat, (SELECT COUNT(*) FROM order_items oi WHERE oi.pet_id = p.id) AS ordered FROM pets p JOIN categories c ON c.id = p.category_id ORDER BY p.name')->fetchAll();

$title = 'ลบข้อมูลน้อง';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1>ลบข้อมูลน้อง</h1><a class="btn ghost sm" href="admin.php">← กลับ</a></div>

  <?php if ($pet): ?>
    <div class="box auth" style="margin-top:0;text-align:center">
      <div class="thumb" style="max-width:200px;margin:0 auto 16px;border-radius:16px;overflow:hidden"><?= img_tag($pet['image']) ?></div>
      <h2>ยืนยันการลบ “<?= e($pet['name']) ?>”?</h2>
      <p class="muted"><?= e($pet['cat']) ?> · <?= e($pet['breed']) ?> · <?= money($pet['price']) ?><br>รูปภาพและวิดีโออัปเดตทั้งหมดจะถูกลบด้วย</p>
      <?php if ($pet['ordered']): ?>
        <div class="alert err">น้องตัวนี้มีประวัติการสั่งซื้อ จึงไม่สามารถลบได้</div>
      <?php else: ?>
        <form method="post" class="row" style="justify-content:center">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $pet['id'] ?>">
          <button class="btn danger">ยืนยันลบ</button><a class="btn ghost" href="pet_delete.php">ยกเลิก</a>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="tbl-wrap mt">
    <table>
      <thead><tr><th>น้อง</th><th>ประเภท</th><th class="num">ราคา</th><th>สถานะ</th><th class="num"></th></tr></thead>
      <tbody>
      <?php foreach ($pets as $p): ?>
        <tr>
          <td><div class="row" style="flex-wrap:nowrap"><span class="mini"><?= img_tag($p['image']) ?></span><strong><?= e($p['name']) ?></strong></div></td>
          <td><?= e($p['cat']) ?></td>
          <td class="num"><?= money($p['price']) ?></td>
          <td><span class="status <?= $p['status'] === 'sold' ? 'sold' : 'ok' ?>"><?= $p['status'] === 'sold' ? 'ได้บ้านแล้ว' : 'พร้อมรับเลี้ยง' ?></span></td>
          <td class="num">
            <?php if ($p['ordered']): ?><span class="small muted">มีประวัติขาย</span>
            <?php else: ?><a class="btn sm danger" href="pet_delete.php?id=<?= $p['id'] ?>">ลบ</a><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'footer.php'; ?>
