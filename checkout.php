<?php
require 'config.php';
require_customer();
$uid = user()['id'];

$me = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$me->execute([$uid]);
$me = $me->fetch();

$load = $pdo->prepare("SELECT p.* FROM cart_items ci JOIN pets p ON p.id = ci.pet_id WHERE ci.user_id = ? AND p.status = 'available'");
$load->execute([$uid]);
$items = $load->fetchAll();
if (!$items) { flash('ไม่มีน้องในตะกร้าที่พร้อมสั่งซื้อ', 'err'); redirect('cart.php'); }

$err = [];
$v = ['ship_name' => $me['full_name'], 'ship_phone' => $me['phone'], 'ship_address' => $me['address'], 'payment_method' => 'promptpay'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $v = array_map('trim', array_intersect_key($_POST, $v)) + $v;
    if ($v['ship_name'] === '') $err[] = 'กรุณากรอกชื่อผู้รับ';
    if (!preg_match('/^0\d{8,9}$/', $v['ship_phone'])) $err[] = 'เบอร์โทรไม่ถูกต้อง';
    if ($v['ship_address'] === '') $err[] = 'กรุณากรอกที่อยู่จัดส่ง';
    if (!isset(PAY_METHODS[$v['payment_method']])) $err[] = 'กรุณาเลือกวิธีชำระเงิน';

    if (!$err) {
        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare("SELECT p.id, p.price FROM cart_items ci JOIN pets p ON p.id = ci.pet_id WHERE ci.user_id = ? AND p.status = 'available' FOR UPDATE");
            $lock->execute([$uid]);
            $rows = $lock->fetchAll();
            if (!$rows) throw new Exception('น้องในตะกร้าถูกรับเลี้ยงไปแล้ว');

            $total = array_sum(array_column($rows, 'price'));
            $pdo->prepare('INSERT INTO orders (user_id, total, payment_method, ship_name, ship_phone, ship_address, paid_at) VALUES (?,?,?,?,?,?,NOW())')
                ->execute([$uid, $total, $v['payment_method'], $v['ship_name'], $v['ship_phone'], $v['ship_address']]);
            $oid = $pdo->lastInsertId();

            $ins  = $pdo->prepare('INSERT INTO order_items (order_id, pet_id, price) VALUES (?,?,?)');
            $sold = $pdo->prepare("UPDATE pets SET status = 'sold' WHERE id = ?");
            $rm   = $pdo->prepare('DELETE FROM cart_items WHERE pet_id = ?');
            foreach ($rows as $r) {
                $ins->execute([$oid, $r['id'], $r['price']]);
                $sold->execute([$r['id']]);
                $rm->execute([$r['id']]);
            }
            $pdo->commit();
            flash('ชำระเงินเรียบร้อย! ระบบกำลังจัดส่งน้องไปหาคุณ');
            redirect('track.php?id=' . $oid);
        } catch (Exception $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $err[] = $ex->getMessage();
        }
    }
}
$total = array_sum(array_column($items, 'price'));

$title = 'ชำระเงิน';
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1>ชำระเงิน</h1></div>
  <?php if ($err): ?><div class="alert err"><?= implode('<br>', array_map('e', $err)) ?></div><?php endif; ?>
  <form method="post" class="split">
    <?= csrf_field() ?>
    <div class="box">
      <h3>ข้อมูลการจัดส่ง</h3>
      <div class="grid2">
        <div class="field"><label>ชื่อผู้รับ</label><input name="ship_name" required value="<?= e($v['ship_name']) ?>"></div>
        <div class="field"><label>เบอร์โทร</label><input name="ship_phone" required value="<?= e($v['ship_phone']) ?>"></div>
      </div>
      <div class="field"><label>ที่อยู่จัดส่ง</label><textarea name="ship_address" required><?= e($v['ship_address']) ?></textarea></div>
      <h3 class="mt">วิธีชำระเงิน</h3>
      <div class="pay">
        <?php foreach (PAY_METHODS as $k => $label): ?>
          <label><input type="radio" name="payment_method" value="<?= $k ?>" <?= $v['payment_method'] === $k ? 'checked' : '' ?>> <?= $label ?></label>
        <?php endforeach; ?>
      </div>

      <div class="pay-info">
        <div class="pi" data-pay="transfer">
          <p class="pi-title">โอนเข้าบัญชีของร้าน ยอด <strong><?= money($total) ?></strong></p>
          <?php foreach (SHOP_BANKS as $b): ?>
            <div class="bank">
              <span class="bank-logo" style="--c:<?= e($b['color']) ?>"><?= e($b['code']) ?></span>
              <div class="bank-txt">
                <small><?= e($b['name']) ?></small>
                <strong class="acc"><?= e($b['acc']) ?></strong>
                <small>ชื่อบัญชี <?= e($b['holder']) ?></small>
              </div>
              <button type="button" class="btn sm ghost copy" data-copy="<?= e(preg_replace('/\D/', '', $b['acc'])) ?>">คัดลอก</button>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="pi pp" data-pay="promptpay">
          <div class="qr-card">
            <div class="qr-head">พร้อมเพย์ · PromptPay</div>
            <div class="qr-img" id="ppQr" data-payload="<?= e(promptpay_payload(SHOP_PROMPTPAY, $total)) ?>"></div>
            <div class="qr-name">🐾 <?= e(SHOP_PAY_NAME) ?></div>
            <div class="qr-amt"><?= money($total) ?></div>
            <small>พร้อมเพย์ <?= e(substr(SHOP_PROMPTPAY, 0, 3) . '-xxx-' . substr(SHOP_PROMPTPAY, -4)) ?></small>
          </div>
          <div class="qr-steps">
            <ol>
              <li>เปิดแอปธนาคารบนมือถือ</li>
              <li>เลือก “สแกน QR” แล้วสแกนรหัสนี้ — ยอดเงินถูกใส่ให้อัตโนมัติ</li>
              <li>โอนเสร็จแล้วกด “ยืนยันการชำระเงิน”</li>
            </ol>
            <a class="btn sm ghost" id="ppSave" href="#" download="pawpaws-promptpay.gif"> บันทึก QR</a>
          </div>
        </div>

        <div class="pi" data-pay="card">
          <p class="pi-title">💳 ชำระด้วยบัตรเครดิต/เดบิต ยอด <strong><?= money($total) ?></strong></p>
          <p class="small muted" style="margin:0">ระบบจะจำลองการตัดบัตรเมื่อกด “ยืนยันการชำระเงิน”</p>
        </div>

        <div class="pi" data-pay="cod">
          <p class="pi-title">🚚 ชำระเงินสด <strong><?= money($total) ?></strong> กับพนักงานเมื่อรับน้อง</p>
          <p class="small muted" style="margin:0">กรุณาเตรียมเงินให้พอดีเพื่อความรวดเร็ว</p>
        </div>
      </div>

      <p class="small muted mt"></p>
    </div>
    <div class="box">
      <h3>รายการน้อง</h3>
      <?php foreach ($items as $i): ?>
        <div class="sum-row"><span class="row" style="flex-wrap:nowrap"><span class="mini"><?= img_tag($i['image']) ?></span><?= e($i['name']) ?></span><span><?= money($i['price']) ?></span></div>
      <?php endforeach; ?>
      <div class="sum-row"><span class="muted">ค่าจัดส่ง</span><span>ฟรี</span></div>
      <div class="sum-row total"><span>รวม</span><span class="hl"><?= money($total) ?></span></div>
      <button class="btn block mt">ยืนยันการชำระเงิน</button>
    </div>
  </form>
</div>
<script src="assets/qrcode.js"></script>
<script>
(() => {
  // QR พร้อมเพย์
  const box = document.getElementById('ppQr'), save = document.getElementById('ppSave');
  if (box && window.qrcode) {
    const q = qrcode(0, 'M');
    q.addData(box.dataset.payload);
    q.make();
    box.innerHTML = q.createSvgTag({ cellSize: 6, margin: 2, scalable: true, alt: 'PromptPay QR' });
    if (save) save.href = q.createDataURL(10, 24);
  }

  const radios = document.querySelectorAll('input[name="payment_method"]');
  const show = () => {
    const v = document.querySelector('input[name="payment_method"]:checked')?.value;
    document.querySelectorAll('.pi').forEach(p => p.hidden = p.dataset.pay !== v);
  };
  radios.forEach(r => r.addEventListener('change', show));
  show();

  document.querySelectorAll('.copy').forEach(b => b.addEventListener('click', async () => {
    const t = b.dataset.copy;
    try { await navigator.clipboard.writeText(t); }
    catch (e) { const a = document.createElement('textarea'); a.value = t; document.body.append(a); a.select(); document.execCommand('copy'); a.remove(); }
    const old = b.textContent;
    b.textContent = 'คัดลอกแล้ว ✓'; b.classList.add('done');
    setTimeout(() => { b.textContent = old; b.classList.remove('done'); }, 1600);
  }));
})();
</script>
<?php include 'footer.php'; ?>
