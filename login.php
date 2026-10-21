<?php
require 'config.php';
if (user()) redirect(is_admin() ? 'admin.php' : 'index.php');

$next = $_GET['next'] ?? $_POST['next'] ?? '';
if (!preg_match('#^/[^/\\\\]#', $next)) $next = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $st = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
    $st->execute([trim($_POST['username']), trim($_POST['username'])]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'], $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'username' => $u['username'], 'full_name' => $u['full_name'], 'role' => $u['role']];
        flash('ยินดีต้อนรับ คุณ' . $u['full_name']);
        redirect($next ?: ($u['role'] === 'admin' ? 'admin.php' : 'index.php'));
    }
    $err = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}

$title = 'เข้าสู่ระบบ';
include 'header.php';
?>
<div class="wrap">
  <div class="auth box">
    <h1>เข้าสู่ระบบ</h1>
    <p class="muted">ยินดีต้อนรับกลับมาที่ PawPaws 🐾</p>
    <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field"><label>ชื่อผู้ใช้ หรือ อีเมล</label><input name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>"></div>
      <div class="field"><label>รหัสผ่าน</label><input type="password" name="password" required></div>
      <button class="btn block">เข้าสู่ระบบ</button>
    </form>
    <p class="center small muted mt">ยังไม่มีบัญชี? <a class="hl" href="register.php">สมัครสมาชิก</a></p>
  </div>
</div>
<?php include 'footer.php'; ?>
