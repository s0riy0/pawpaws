<?php
$u = user();
$cur = basename($_SERVER['PHP_SELF']);
$nav = function ($href, $label) use ($cur) {
    $active = $cur === basename(strtok($href, '?')) ? ' class="active"' : '';
    return "<a href=\"$href\"$active>$label</a>";
};
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'PawPaws') ?> · PawPaws</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<meta name="theme-color" content="#f4eee9" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0a0a12" media="(prefers-color-scheme: dark)">
<script>try{var t=localStorage.getItem('pp-theme');if(t)document.documentElement.dataset.theme=t;}catch(e){}</script>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="nav">
  <div class="wrap nav-in">
    <a class="logo" href="index.php"><span>🐾</span> PawPaws</a>
    <input type="checkbox" id="menu-t" hidden>
    <label for="menu-t" class="burger">☰</label>
    <nav class="links">
      <?= $nav('index.php', 'หน้าแรก') ?>
      <?= $nav('search.php', 'ค้นหาน้องๆ') ?>
      <?php if (is_admin()): ?>
        <?= $nav('admin.php', 'จัดการน้องๆ') ?>
        <?= $nav('pet_add.php', 'เพิ่มน้อง') ?>
        <?= $nav('orders.php', 'คำสั่งซื้อ') ?>
        <?= $nav('chat.php', 'แชท <span class="chat-dot" id="chatDot"></span>') ?>
        <?= $nav('reports.php', 'รายงาน') ?>
      <?php elseif ($u): ?>
        <?= $nav('orders.php', 'ติดตามน้อง') ?>
        <?= $nav('chat.php', 'แชทกับเรา <span class="chat-dot" id="chatDot"></span>') ?>
        <?= $nav('cart.php', 'ตะกร้า <span class="badge">' . cart_count() . '</span>') ?>
      <?php endif; ?>
      <span class="sp"></span>
      <?php if ($u): ?>
        <span class="who"><?= is_admin() ? '🛡️' : '👤' ?> <?= e($u['full_name']) ?></span>
        <a class="btn sm ghost" href="logout.php">ออกจากระบบ</a>
      <?php else: ?>
        <a href="login.php">เข้าสู่ระบบ</a>
        <a class="btn sm" href="register.php">สมัครสมาชิก</a>
      <?php endif; ?>
    </nav>
    <button type="button" class="theme-t" id="themeT" aria-label="สลับโหมดสว่าง/มืด" title="สลับโหมดสว่าง/มืด">🌙</button>
  </div>
</header>
<script>
(() => {
  const b = document.getElementById('themeT'), r = document.documentElement;
  const dark = () => (r.dataset.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark';
  const sync = () => b.textContent = dark() ? '☀️' : '🌙';
  b.onclick = () => { r.dataset.theme = dark() ? 'light' : 'dark'; try { localStorage.setItem('pp-theme', r.dataset.theme); } catch (e) {} sync(); };
  sync();
})();
</script>
<main>
<?php if ($f = flash()): ?>
  <div class="wrap"><div class="alert <?= e($f[1]) ?>"><?= e($f[0]) ?></div></div>
<?php endif; ?>
