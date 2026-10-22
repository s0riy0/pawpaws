<?php
require 'config.php';
require_admin();

const TARGET = 12;
$customers = [
    ['Nutchapol', 'ปันปัน รักเด็ก', 'customer@pawpaws.test', '0812345678', '99 ถ.สุขุมวิท กรุงเทพฯ 10110', '2000-05-15'],
    ['nidnoi', 'นิดหน่อย ใจดี', 'nidnoi@pawpaws.test', '0823456789', '12 ถ.นิมมานเหมินท์ เชียงใหม่ 50200', '1998-08-21'],
    ['tonkla', 'ต้นกล้า มีสุข', 'tonkla@pawpaws.test', '0834567890', '45 ถ.มิตรภาพ ขอนแก่น 40000', '1995-02-10'],
    ['praew', 'แพรว สายรุ้ง', 'praew@pawpaws.test', '0845678901', '8 ถ.ราชดำเนิน นครศรีธรรมราช 80000', '2001-11-03'],
    ['boss', 'บอส ทองดี', 'boss@pawpaws.test', '0856789012', '220 ถ.เพชรเกษม หาดใหญ่ สงขลา 90110', '1993-06-27'],
];
$methods = array_keys(PAY_METHODS);

$have = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$need = TARGET - $have;
$log = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $need > 0) {
    check_csrf();
    try {
        $pdo->beginTransaction();

        $insU = $pdo->prepare("INSERT IGNORE INTO users (username,password,full_name,email,phone,address,birth_date,role) VALUES (?,?,?,?,?,?,?,'customer')");
        $hash = password_hash('123456', PASSWORD_DEFAULT);
        foreach ($customers as $c) $insU->execute([$c[0], $hash, $c[1], $c[2], $c[3], $c[4], $c[5]]);
        $users = $pdo->query("SELECT id, full_name, phone, address FROM users WHERE role = 'customer' ORDER BY id")->fetchAll();

        $pets = $pdo->query("SELECT id, name, price FROM pets WHERE status = 'available' ORDER BY RAND() FOR UPDATE")->fetchAll();
        if (count($pets) < $need) throw new Exception("น้องที่ยังว่างมีแค่ " . count($pets) . " ตัว ไม่พอสร้าง $need คำสั่งซื้อ");

        $insO  = $pdo->prepare('INSERT INTO orders (user_id,total,payment_method,ship_name,ship_phone,ship_address,status,paid_at,received_at) VALUES (?,?,?,?,?,?,"received",?,?)');
        $insI  = $pdo->prepare('INSERT INTO order_items (order_id,pet_id,price) VALUES (?,?,?)');
        $sold  = $pdo->prepare("UPDATE pets SET status = 'sold' WHERE id = ?");
        $unCart = $pdo->prepare('DELETE FROM cart_items WHERE pet_id = ?');

        $start = strtotime(date('Y-01-05 10:00'));
        $end = strtotime('-1 day');
        $spare = count($pets) - $need;

        for ($i = 0; $i < $need; $i++) {
            $u = $users[$i % count($users)];
            $take = [array_shift($pets)];
            if ($spare > 6 && $i % 4 === 1) { $take[] = array_shift($pets); $spare--; } // บางคำสั่งซื้อรับ 2 ตัว
            $total = array_sum(array_column($take, 'price'));
            $t = $start + (int)(($end - $start) * ($i + 0.5) / $need) + random_int(0, 6 * 3600);
            $paid = date('Y-m-d H:i:s', $t);
            $recv = date('Y-m-d H:i:s', $t + random_int(60, 600));

            $insO->execute([$u['id'], $total, $methods[$i % count($methods)], $u['full_name'], $u['phone'] ?: '0800000000', $u['address'] ?: '-', $paid, $recv]);
            $oid = $pdo->lastInsertId();
            foreach ($take as $p) {
                $insI->execute([$oid, $p['id'], $p['price']]);
                $sold->execute([$p['id']]);
                $unCart->execute([$p['id']]);
            }
            $log[] = ['#' . $oid, $u['full_name'], implode(', ', array_column($take, 'name')), $total, $paid];
        }
        $pdo->commit();
        $have = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $need = TARGET - $have;
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $ex->getMessage();
    }
}

$title = 'สร้างคำสั่งซื้อตัวอย่าง';
include 'header.php';
?>
<div class="wrap page">
  <div class="auth wide box" style="margin-top:0">
    <h1>สร้างคำสั่งซื้อตัวอย่าง</h1>
    <p class="muted">คำสั่งซื้อตอนนี้ <strong><?= $have ?></strong> รายการ · เป้าหมาย <?= TARGET ?> รายการ</p>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

    <?php if ($log): ?>
      <div class="alert">สร้างเพิ่ม <?= count($log) ?> คำสั่งซื้อเรียบร้อย</div>
      <div class="tbl-wrap"><table>
        <thead><tr><th>#</th><th>ลูกค้า</th><th>น้อง</th><th class="num">ยอด</th><th>วันที่</th></tr></thead>
        <tbody><?php foreach ($log as $r): ?>
          <tr><td><?= $r[0] ?></td><td><?= e($r[1]) ?></td><td><?= e($r[2]) ?></td><td class="num"><?= money($r[3]) ?></td><td><?= thai_date($r[4], true) ?></td></tr>
        <?php endforeach; ?></tbody>
      </table></div>
    <?php endif; ?>

    <?php if ($need > 0): ?>
      <p>จะสร้างเพิ่ม <strong><?= $need ?></strong> คำสั่งซื้อ (สถานะ “รับน้องแล้ว”) กระจายวันที่ตั้งแต่ต้นปี และเพิ่มลูกค้าตัวอย่าง 4 คน (รหัสผ่าน 123456) น้องที่ถูกซื้อจะขึ้น “ได้บ้านแล้ว”</p>
      <form method="post"><?= csrf_field() ?><button class="btn block">สร้างคำสั่งซื้อ</button></form>
    <?php else: ?>
      <div class="row mt"><a class="btn" href="orders.php">ดูคำสั่งซื้อ</a><a class="btn ghost" href="reports.php">ดูรายงาน</a></div>
    <?php endif; ?>
  </div>
</div>
<?php include 'footer.php'; ?>
