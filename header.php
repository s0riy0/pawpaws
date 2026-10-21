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
  </div>
</header>
<main>
<?php if ($f = flash()): ?>
  <div class="wrap"><div class="alert <?= e($f[1]) ?>"><?= e($f[0]) ?></div></div>
<?php endif; ?>
