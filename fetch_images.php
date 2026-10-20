<?php
// ดาวน์โหลดรูปตัวอย่างจากอินเทอร์เน็ตมาเก็บไว้ใน uploads/ (ใช้ครั้งเดียว เพื่อให้เว็บทำงานแบบออฟไลน์ได้)
require 'config.php';
require_admin();

$log = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    set_time_limit(300);
    $ctx = stream_context_create(['http' => ['timeout' => 30, 'header' => "User-Agent: PawPaws\r\n"]]);
    $cache = [];
    $save = function ($url) use (&$cache, $ctx) {
        if (isset($cache[$url])) return $cache[$url];
        $data = @file_get_contents($url, false, $ctx);
        if (!$data || !str_starts_with((new finfo(FILEINFO_MIME_TYPE))->buffer($data), 'image/')) return $cache[$url] = null;
        if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0775, true);
        $name = date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.jpg';
        file_put_contents(__DIR__ . '/uploads/' . $name, $data);
        return $cache[$url] = $name;
    };

    foreach ($pdo->query("SELECT id, name, image FROM pets WHERE image LIKE 'http%'") as $p) {
        $f = $save($p['image']);
        if ($f) $pdo->prepare('UPDATE pets SET image = ? WHERE id = ?')->execute([$f, $p['id']]);
        $log[] = [$p['name'], (bool)$f];
    }
    foreach ($pdo->query("SELECT id, file FROM pet_media WHERE file LIKE 'http%'") as $m) {
        if ($f = $save($m['file'])) $pdo->prepare('UPDATE pet_media SET file = ? WHERE id = ?')->execute([$f, $m['id']]);
    }
}
$left = (int)$pdo->query("SELECT COUNT(*) FROM pets WHERE image LIKE 'http%'")->fetchColumn();

$title = 'ดาวน์โหลดรูปตัวอย่าง';
include 'header.php';
?>
<div class="wrap page">
  <div class="auth box" style="margin-top:0">
    <h1>ดาวน์โหลดรูปตัวอย่าง</h1>
    <p class="muted">บันทึกรูปน้องๆ จากอินเทอร์เน็ตลงโฟลเดอร์ <code>uploads/</code> เพื่อให้เปิดเว็บได้แม้ไม่มีอินเทอร์เน็ต</p>
    <?php foreach ($log as [$n, $ok]): ?>
      <div class="sum-row"><span><?= e($n) ?></span><span class="status <?= $ok ? 'ok' : 'sold' ?>"><?= $ok ? 'สำเร็จ' : 'ไม่สำเร็จ' ?></span></div>
    <?php endforeach; ?>
    <p>รูปที่ยังเป็นลิงก์ภายนอก: <strong><?= $left ?></strong></p>
    <?php if ($left): ?>
      <form method="post"><?= csrf_field() ?><button class="btn block">ดาวน์โหลดตอนนี้</button></form>
    <?php else: ?>
      <a class="btn block" href="admin.php">เรียบร้อย กลับหน้าแอดมิน</a>
    <?php endif; ?>
  </div>
</div>
<?php include 'footer.php'; ?>
