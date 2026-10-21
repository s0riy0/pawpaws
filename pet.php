<?php
require 'config.php';

$st = $pdo->prepare('SELECT p.*, c.name AS cat FROM pets p JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$p = $st->fetch();
if (!$p) { flash('ไม่พบข้อมูลสัตว์เลี้ยง', 'err'); redirect('index.php'); }

$st = $pdo->prepare('SELECT * FROM pet_media WHERE pet_id = ? ORDER BY created_at DESC');
$st->execute([$p['id']]);
$media = $st->fetchAll();

$inCart = false;
if (user() && !is_admin()) {
    $st = $pdo->prepare('SELECT 1 FROM cart_items WHERE user_id = ? AND pet_id = ?');
    $st->execute([user()['id'], $p['id']]);
    $inCart = (bool)$st->fetchColumn();
}
$sold = $p['status'] === 'sold';
$bd = new DateTime($p['birth_date']);
$nextBd = new DateTime(date('Y') . $bd->format('-m-d'));
if ($nextBd < new DateTime('today')) $nextBd->modify('+1 year');

$title = $p['name'];
include 'header.php';
?>
<div class="wrap page">
  <p class="small muted"><a href="index.php">หน้าแรก</a> / <a href="index.php?cat=<?= $p['category_id'] ?>#pets"><?= e($p['cat']) ?></a> / <?= e($p['name']) ?></p>
  <div class="detail">
    <div class="main-img <?= $sold ? 'pet sold' : '' ?>">
      <div class="thumb" style="height:100%"><?= img_tag($p['image'], $p['name']) ?>
        <?php if ($sold): ?><span class="home-badge lg">ได้บ้านแล้ว 🏡</span><?php endif; ?></div>
    </div>
    <div>
      <span class="tag"><?= e($p['cat']) ?></span>
      <h1 style="font-size:2.4rem;margin-top:10px"><?= e($p['name']) ?></h1>
      <div class="price" style="font-size:1.8rem;margin:0"><?= money($p['price']) ?></div>
      <div class="facts">
        <div><small>สายพันธุ์</small><?= e($p['breed'] ?: '-') ?></div>
        <div><small>เพศ</small><?= $p['gender'] === 'male' ? '♂ ตัวผู้' : '♀ ตัวเมีย' ?></div>
        <div><small>วันเกิด</small><?= thai_date($p['birth_date']) ?></div>
        <div><small>อายุ</small><?= age_text($p['birth_date']) ?></div>
        <div><small>วันเกิดครั้งถัดไป</small><?= thai_date($nextBd->format('Y-m-d')) ?></div>
        <div><small>สถานะ</small><span class="status <?= $sold ? 'sold' : 'ok' ?>"><?= $sold ? 'ได้บ้านแล้ว' : 'พร้อมรับเลี้ยง' ?></span></div>
      </div>
      <p><?= nl2br(e($p['description'])) ?></p>

      <div class="row mt">
        <?php if (is_admin()): ?>
          <a class="btn" href="pet_edit.php?id=<?= $p['id'] ?>">✏️ แก้ไขข้อมูล / อัปเดตรูป</a>
          <a class="btn ghost" href="pet_delete.php?id=<?= $p['id'] ?>">🗑️ ลบ</a>
        <?php elseif ($sold): ?>
          <button class="btn" disabled>น้องได้บ้านแล้ว</button>
        <?php elseif ($inCart): ?>
          <a class="btn" href="cart.php">อยู่ในตะกร้าแล้ว → ไปที่ตะกร้า</a>
        <?php else: ?>
          <form method="post" action="cart.php">
            <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="pet_id" value="<?= $p['id'] ?>">
            <button class="btn">🛒 รับเลี้ยงน้อง (ใส่ตะกร้า)</button>
          </form>
        <?php endif; ?>
        <?php if (!is_admin()): ?>
          <a class="btn ghost" href="chat.php?pet=<?= $p['id'] ?>">💬 สอบถามเกี่ยวกับน้อง</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <section class="mt" style="margin-top:56px">
    <div class="page-title"><h2 style="margin:0">อัปเดตรูปภาพ & วิดีโอตัวจริง</h2><span class="muted small"><?= count($media) ?> รายการ</span></div>
    <?php if (!$media): ?>
      <p class="muted">ยังไม่มีการอัปเดต</p>
    <?php else: ?>
      <div class="media">
        <?php foreach ($media as $m): ?>
          <figure>
            <?php if ($m['type'] === 'video'): ?>
              <video src="<?= e(media_url($m['file'])) ?>" controls preload="metadata"></video>
            <?php else: ?>
              <img src="<?= e(media_url($m['file'])) ?>" alt="" loading="lazy">
            <?php endif; ?>
            <figcaption><?= e($m['caption'] ?: ($m['type'] === 'video' ? 'วิดีโอ' : 'รูปภาพ')) ?><br><span class="muted"><?= thai_date($m['created_at'], true) ?></span></figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
<?php include 'footer.php'; ?>
