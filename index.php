<?php
require 'config.php';

$cats = $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll();
$cat = (int)($_GET['cat'] ?? 0);

$sql = 'SELECT p.*, c.name AS cat FROM pets p JOIN categories c ON c.id = p.category_id'
     . ($cat ? ' WHERE p.category_id = ?' : '')
     . ' ORDER BY p.status = "sold", p.created_at DESC';
$st = $pdo->prepare($sql);
$st->execute($cat ? [$cat] : []);
$pets = $st->fetchAll();

$heroes = $pdo->query("SELECT id, image, name, breed FROM pets WHERE image IS NOT NULL AND image <> '' AND status='available' ORDER BY RAND()")->fetchAll();
$homed = (int)$pdo->query("SELECT COUNT(*) FROM pets WHERE status='sold'")->fetchColumn();

$title = 'หน้าแรก';
include 'header.php';
?>
<section class="hero wrap">
  <div>
    <span class="pill">🐾 บ้านใหม่ที่เต็มไปด้วยความรัก</span>
    <h1>ค้นหาสมาชิกใหม่<br><span class="hl">สี่ขาของครอบครัวคุณ</span></h1>
    <p>เราดูแลสัตว์เลี้ยงทุกตัวด้วยความรักและความเอาใจใส่ สุขภาพแข็งแรง ได้รับวัคซีนครบถ้วน พร้อมส่งมอบน้องๆ สู่บ้านใหม่ที่อบอุ่นของคุณ</p>
    <div class="row">
      <a class="btn" href="#pets">รับเลี้ยงน้องๆ</a>
      <a class="btn ghost" href="#how">วิธีการรับเลี้ยง</a>
    </div>
    <p class="small muted mt">🏡 น้องๆ ได้บ้านแล้ว <?= $homed ?> ตัว</p>
  </div>
  <div class="hero-img">
    <?php if (!$heroes): ?>
      <?= img_tag(null) ?>
    <?php else: ?>
      <div class="slides" id="heroSlides">
        <?php foreach ($heroes as $i => $h): ?>
          <a class="slide<?= $i ? '' : ' on' ?>" href="pet.php?id=<?= $h['id'] ?>">
            <?= img_tag($h['image'], $h['name']) ?>
            <span class="slide-cap"><?= e($h['name']) ?> · <?= e($h['breed']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
// สุ่มเปลี่ยนรูปทุก 3 วินาที
(() => {
  const s = [...document.querySelectorAll('#heroSlides .slide')];
  if (s.length < 2) return;
  let cur = 0;
  setInterval(() => {
    let n;
    do { n = Math.floor(Math.random() * s.length); } while (n === cur);
    s[cur].classList.remove('on');
    s[n].classList.add('on');
    cur = n;
  }, 3000);
})();
</script>

<section class="section" id="pets">
  <div class="wrap">
    <div class="sec-head">
      <h2>น้องๆ ที่กำลังหาบ้าน</h2>
      <p>เลือกดูน้องหมาและน้องแมวในสไตล์มินิมอลสบายตา</p>
    </div>
    <div class="tabs">
      <a href="index.php#pets" class="<?= $cat ? '' : 'on' ?>">ทั้งหมด</a>
      <?php foreach ($cats as $c): ?>
        <a href="index.php?cat=<?= $c['id'] ?>#pets" class="<?= $cat == $c['id'] ? 'on' : '' ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php if (!$pets): ?>
      <p class="center muted">ยังไม่มีน้องในหมวดนี้</p>
    <?php else: ?>
      <div class="grid"><?php foreach ($pets as $p) echo pet_card($p); ?></div>
    <?php endif; ?>
  </div>
</section>

<section class="section plain" id="how">
  <div class="wrap">
    <div class="sec-head"><h2>วิธีการรับเลี้ยง</h2><p>ง่ายๆ เพียง 4 ขั้นตอน</p></div>
    <div class="steps">
      <div class="step"><b>1</b><strong>สมัครสมาชิก</strong><p class="muted small">สำหรับผู้ที่มีอายุ 18 ปีขึ้นไป</p></div>
      <div class="step"><b>2</b><strong>เลือกน้องที่ใช่</strong><p class="muted small">ดูโปรไฟล์ รูป และวิดีโออัปเดตตัวจริง</p></div>
      <div class="step"><b>3</b><strong>ชำระเงิน</strong><p class="muted small">โอน พร้อมเพย์ บัตร หรือปลายทาง</p></div>
      <div class="step"><b>4</b><strong>ติดตามการจัดส่ง</strong><p class="muted small">อัปเดตสถานะแบบเรียลไทม์จนน้องถึงมือ</p></div>
    </div>
  </div>
</section>
<?php include 'footer.php'; ?>
