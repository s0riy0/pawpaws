<?php
require 'config.php';
require_admin();

$from = $_GET['from'] ?? date('Y-01-01');
$to   = $_GET['to'] ?? date('Y-m-d');
if (!DateTime::createFromFormat('Y-m-d', $from)) $from = date('Y-01-01');
if (!DateTime::createFromFormat('Y-m-d', $to)) $to = date('Y-m-d');
$r = $_GET['r'] ?? 'all';

$st = $pdo->prepare('SELECT c.name, COUNT(s.pet_id) AS qty, COALESCE(SUM(s.price),0) AS total
    FROM categories c
    LEFT JOIN pets p ON p.category_id = c.id
    LEFT JOIN (SELECT oi.pet_id, oi.price FROM order_items oi JOIN orders o ON o.id = oi.order_id
               WHERE DATE(o.paid_at) BETWEEN ? AND ?) s ON s.pet_id = p.id
    GROUP BY c.id, c.name ORDER BY total DESC');
$st->execute([$from, $to]);
$byCat = $st->fetchAll();

$st = $pdo->prepare("SELECT DATE_FORMAT(o.paid_at, '%Y-%m') AS m, COUNT(DISTINCT o.id) AS orders, COUNT(oi.pet_id) AS pets, SUM(oi.price) AS total
    FROM orders o JOIN order_items oi ON oi.order_id = o.id
    WHERE DATE(o.paid_at) BETWEEN ? AND ? GROUP BY m ORDER BY m");
$st->execute([$from, $to]);
$byMonth = $st->fetchAll();

$st = $pdo->prepare('SELECT u.full_name, u.email, u.phone, COUNT(DISTINCT o.id) AS orders, COUNT(oi.pet_id) AS pets, SUM(oi.price) AS total, MAX(o.paid_at) AS last
    FROM users u JOIN orders o ON o.user_id = u.id JOIN order_items oi ON oi.order_id = o.id
    WHERE DATE(o.paid_at) BETWEEN ? AND ? GROUP BY u.id, u.full_name, u.email, u.phone ORDER BY total DESC LIMIT 10');
$st->execute([$from, $to]);
$topCust = $st->fetchAll();

$stock = $pdo->query("SELECT c.name, COUNT(p.id) AS total,
    SUM(p.status = 'available') AS available, SUM(p.status = 'sold') AS sold,
    COALESCE(AVG(p.price),0) AS avg_price, COALESCE(MIN(p.price),0) AS min_price, COALESCE(MAX(p.price),0) AS max_price
    FROM categories c LEFT JOIN pets p ON p.category_id = c.id GROUP BY c.id, c.name ORDER BY c.id")->fetchAll();

$maxCat = max(array_column($byCat, 'total') ?: [0]) ?: 1;
$maxMon = max(array_column($byMonth, 'total') ?: [0]) ?: 1;
$thMonth = function ($ym) { $d = thai_date("$ym-01"); return substr($d, strpos($d, ' ') + 1); };
$show = fn($k) => $r === 'all' || $r === $k;

$title = 'รายงาน';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title">
    <div><h1>รายงาน</h1><span class="muted small">ช่วงวันที่ <?= thai_date($from) ?> – <?= thai_date($to) ?> · พิมพ์เมื่อ <?= thai_date(date('Y-m-d H:i'), true) ?></span></div>
    <button class="btn ghost no-print" onclick="print()">🖨️ พิมพ์รายงาน</button>
  </div>
  <form class="box row no-print" method="get" style="margin-bottom:28px;align-items:end">
    <div><label>ตั้งแต่</label><input type="date" name="from" value="<?= e($from) ?>"></div>
    <div><label>ถึง</label><input type="date" name="to" value="<?= e($to) ?>"></div>
    <div><label>รายงาน</label><select name="r">
      <?php foreach (['all' => 'ทั้งหมด', 'cat' => '1. ยอดขายแยกประเภท', 'month' => '2. ยอดขายรายเดือน', 'cust' => '3. ลูกค้ายอดซื้อสูงสุด', 'stock' => '4. สถานะสัตว์เลี้ยงคงเหลือ'] as $k => $l): ?>
        <option value="<?= $k ?>" <?= $r === $k ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select></div>
    <button class="btn">แสดงรายงาน</button>
  </form>

  <?php if ($show('cat')): ?>
  <section class="report">
    <h2>รายงานที่ 1 : ยอดขายแยกตามประเภทสัตว์เลี้ยง</h2>
    <div class="tbl-wrap"><table>
      <thead><tr><th>ประเภท</th><th class="num">จำนวนที่ขาย (ตัว)</th><th class="num">ยอดขาย (บาท)</th><th>สัดส่วน</th></tr></thead>
      <tbody><?php foreach ($byCat as $x): ?>
        <tr><td><?= e($x['name']) ?></td><td class="num"><?= $x['qty'] ?></td><td class="num"><?= number_format($x['total'], 2) ?></td>
            <td><div class="bar"><span style="width:<?= round($x['total'] / $maxCat * 100) ?>%"></span></div></td></tr>
      <?php endforeach; ?></tbody>
      <tfoot><tr><td>รวม</td><td class="num"><?= array_sum(array_column($byCat, 'qty')) ?></td><td class="num"><?= number_format(array_sum(array_column($byCat, 'total')), 2) ?></td><td></td></tr></tfoot>
    </table></div>
  </section>
  <?php endif; ?>

  <?php if ($show('month')): ?>
  <section class="report">
    <h2>รายงานที่ 2 : ยอดขายรายเดือน</h2>
    <div class="tbl-wrap"><table>
      <thead><tr><th>เดือน</th><th class="num">คำสั่งซื้อ</th><th class="num">จำนวนน้อง</th><th class="num">ยอดขาย (บาท)</th><th>กราฟ</th></tr></thead>
      <tbody><?php if (!$byMonth): ?><tr><td colspan="5" class="center muted">ไม่มีข้อมูลในช่วงที่เลือก</td></tr><?php endif; ?>
      <?php foreach ($byMonth as $x): ?>
        <tr><td><?= $thMonth($x['m']) ?></td><td class="num"><?= $x['orders'] ?></td><td class="num"><?= $x['pets'] ?></td><td class="num"><?= number_format($x['total'], 2) ?></td>
            <td><div class="bar"><span style="width:<?= round($x['total'] / $maxMon * 100) ?>%"></span></div></td></tr>
      <?php endforeach; ?></tbody>
      <tfoot><tr><td>รวม</td><td class="num"><?= array_sum(array_column($byMonth, 'orders')) ?></td><td class="num"><?= array_sum(array_column($byMonth, 'pets')) ?></td><td class="num"><?= number_format(array_sum(array_column($byMonth, 'total')), 2) ?></td><td></td></tr></tfoot>
    </table></div>
  </section>
  <?php endif; ?>

  <?php if ($show('cust')): ?>
  <section class="report">
    <h2>รายงานที่ 3 : ลูกค้าที่มียอดซื้อสูงสุด (Top 10)</h2>
    <div class="tbl-wrap"><table>
      <thead><tr><th>อันดับ</th><th>ชื่อลูกค้า</th><th>ติดต่อ</th><th class="num">คำสั่งซื้อ</th><th class="num">จำนวนน้อง</th><th class="num">ยอดซื้อ (บาท)</th><th>ซื้อล่าสุด</th></tr></thead>
      <tbody><?php if (!$topCust): ?><tr><td colspan="7" class="center muted">ไม่มีข้อมูลในช่วงที่เลือก</td></tr><?php endif; ?>
      <?php foreach ($topCust as $i => $x): ?>
        <tr><td><?= $i + 1 ?></td><td><?= e($x['full_name']) ?></td><td class="small"><?= e($x['email']) ?><br><?= e($x['phone']) ?></td>
            <td class="num"><?= $x['orders'] ?></td><td class="num"><?= $x['pets'] ?></td><td class="num"><?= number_format($x['total'], 2) ?></td><td><?= thai_date($x['last']) ?></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
  </section>
  <?php endif; ?>

  <?php if ($show('stock')): ?>
  <section class="report">
    <h2>รายงานที่ 4 : สถานะสัตว์เลี้ยงคงเหลือแยกตามประเภท</h2>
    <div class="tbl-wrap"><table>
      <thead><tr><th>ประเภท</th><th class="num">ทั้งหมด</th><th class="num">พร้อมรับเลี้ยง</th><th class="num">ได้บ้านแล้ว</th><th class="num">ราคาเฉลี่ย</th><th class="num">ต่ำสุด</th><th class="num">สูงสุด</th></tr></thead>
      <tbody><?php foreach ($stock as $x): ?>
        <tr><td><?= e($x['name']) ?></td><td class="num"><?= $x['total'] ?></td><td class="num"><?= (int)$x['available'] ?></td><td class="num"><?= (int)$x['sold'] ?></td>
            <td class="num"><?= number_format($x['avg_price'], 2) ?></td><td class="num"><?= number_format($x['min_price'], 2) ?></td><td class="num"><?= number_format($x['max_price'], 2) ?></td></tr>
      <?php endforeach; ?></tbody>
      <tfoot><tr><td>รวม</td><td class="num"><?= array_sum(array_column($stock, 'total')) ?></td><td class="num"><?= array_sum(array_column($stock, 'available')) ?></td><td class="num"><?= array_sum(array_column($stock, 'sold')) ?></td><td colspan="3"></td></tr></tfoot>
    </table></div>
  </section>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
