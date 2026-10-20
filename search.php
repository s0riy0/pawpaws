<?php
require 'config.php';

$cats = $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll();
$q      = trim($_GET['q'] ?? '');
$cat    = (int)($_GET['cat'] ?? 0);
$gender = $_GET['gender'] ?? '';
$status = $_GET['status'] ?? '';
$min    = $_GET['min'] ?? '';
$max    = $_GET['max'] ?? '';
$sort   = $_GET['sort'] ?? 'new';

$where = [];
$args = [];
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.breed LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    array_push($args, "%$q%", "%$q%", "%$q%", "%$q%");
}
if ($cat) { $where[] = 'p.category_id = ?'; $args[] = $cat; }
if (in_array($gender, ['male', 'female'], true)) { $where[] = 'p.gender = ?'; $args[] = $gender; }
if (in_array($status, ['available', 'sold'], true)) { $where[] = 'p.status = ?'; $args[] = $status; }
if (is_numeric($min)) { $where[] = 'p.price >= ?'; $args[] = $min; }
if (is_numeric($max)) { $where[] = 'p.price <= ?'; $args[] = $max; }

$order = ['new' => 'p.created_at DESC', 'cheap' => 'p.price ASC', 'pricey' => 'p.price DESC', 'young' => 'p.birth_date DESC'][$sort] ?? 'p.created_at DESC';
$sql = 'SELECT p.*, c.name AS cat FROM pets p JOIN categories c ON c.id = p.category_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY $order";
$st = $pdo->prepare($sql);
$st->execute($args);
$pets = $st->fetchAll();

$sel = fn($a, $b) => (string)$a === (string)$b ? 'selected' : '';
$title = 'ค้นหาน้องๆ';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1>ค้นหาน้องๆ</h1><span class="muted">พบ <?= count($pets) ?> รายการ</span></div>
  <form class="filters box" method="get">
    <div><label>คำค้นหา</label><input name="q" value="<?= e($q) ?>" placeholder="ชื่อ สายพันธุ์ ..."></div>
    <div><label>ประเภท</label><select name="cat"><option value="0">ทั้งหมด</option>
      <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $sel($cat, $c['id']) ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>เพศ</label><select name="gender"><option value="">ทั้งหมด</option>
      <option value="male" <?= $sel($gender, 'male') ?>>ตัวผู้</option><option value="female" <?= $sel($gender, 'female') ?>>ตัวเมีย</option></select></div>
    <div><label>สถานะ</label><select name="status"><option value="">ทั้งหมด</option>
      <option value="available" <?= $sel($status, 'available') ?>>พร้อมรับเลี้ยง</option><option value="sold" <?= $sel($status, 'sold') ?>>ได้บ้านแล้ว</option></select></div>
    <div><label>ราคา (ต่ำสุด–สูงสุด)</label><div class="row" style="flex-wrap:nowrap;gap:6px"><input type="number" name="min" min="0" value="<?= e($min) ?>"><input type="number" name="max" min="0" value="<?= e($max) ?>"></div></div>
    <div><label>เรียงตาม</label><select name="sort">
      <option value="new" <?= $sel($sort, 'new') ?>>ล่าสุด</option><option value="cheap" <?= $sel($sort, 'cheap') ?>>ราคาต่ำ → สูง</option>
      <option value="pricey" <?= $sel($sort, 'pricey') ?>>ราคาสูง → ต่ำ</option><option value="young" <?= $sel($sort, 'young') ?>>อายุน้อยสุด</option></select></div>
    <button class="btn">ค้นหา</button>
  </form>

  <?php if (!$pets): ?>
    <div class="box center"><p class="muted">ไม่พบน้องที่ตรงกับเงื่อนไข 🐾</p><a class="btn ghost" href="search.php">ล้างตัวกรอง</a></div>
  <?php else: ?>
    <div class="grid"><?php foreach ($pets as $p) echo pet_card($p); ?></div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
