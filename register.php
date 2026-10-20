<?php
require 'config.php';
if (user()) redirect('index.php');

$err = [];
$v = array_map('trim', $_POST + ['username' => '', 'full_name' => '', 'email' => '', 'phone' => '', 'birth_date' => '', 'address' => '']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $v['username'])) $err[] = 'ชื่อผู้ใช้ต้องเป็น a-z, 0-9, _ ยาว 4-30 ตัว';
    if (strlen($_POST['password'] ?? '') < 6) $err[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    if (($_POST['password'] ?? '') !== ($_POST['password2'] ?? '')) $err[] = 'ยืนยันรหัสผ่านไม่ตรงกัน';
    if ($v['full_name'] === '') $err[] = 'กรุณากรอกชื่อ-นามสกุล';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'อีเมลไม่ถูกต้อง';
    if (!preg_match('/^0\d{8,9}$/', $v['phone'])) $err[] = 'เบอร์โทรไม่ถูกต้อง';
    $bd = DateTime::createFromFormat('Y-m-d', $v['birth_date']);
    if (!$bd) $err[] = 'กรุณาระบุวันเกิด';
    elseif ($bd->diff(new DateTime('today'))->y < 18 || $bd > new DateTime()) $err[] = 'ต้องมีอายุ 18 ปีขึ้นไปจึงจะสมัครสมาชิกได้';

    if (!$err) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $st->execute([$v['username'], $v['email']]);
        if ($st->fetchColumn()) $err[] = 'ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้แล้ว';
    }
    if (!$err) {
        $pdo->prepare('INSERT INTO users (username,password,full_name,email,phone,address,birth_date,role) VALUES (?,?,?,?,?,?,?,"customer")')
            ->execute([$v['username'], password_hash($_POST['password'], PASSWORD_DEFAULT), $v['full_name'], $v['email'], $v['phone'], $v['address'], $v['birth_date']]);
        flash('สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ');
        redirect('login.php');
    }
}

$title = 'สมัครสมาชิก';
include 'header.php';
?>
<div class="wrap">
  <div class="auth wide box">
    <h1>สมัครสมาชิก</h1>
    <p class="muted">สำหรับผู้ที่มีอายุ 18 ปีขึ้นไป</p>
    <?php if ($err): ?><div class="alert err"><?= implode('<br>', array_map('e', $err)) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="grid2">
        <div class="field"><label>ชื่อผู้ใช้</label><input name="username" required value="<?= e($v['username']) ?>"></div>
        <div class="field"><label>ชื่อ-นามสกุล</label><input name="full_name" required value="<?= e($v['full_name']) ?>"></div>
        <div class="field"><label>รหัสผ่าน</label><input type="password" name="password" required minlength="6"></div>
        <div class="field"><label>ยืนยันรหัสผ่าน</label><input type="password" name="password2" required minlength="6"></div>
        <div class="field"><label>อีเมล</label><input type="email" name="email" required value="<?= e($v['email']) ?>"></div>
        <div class="field"><label>เบอร์โทรศัพท์</label><input name="phone" required placeholder="08xxxxxxxx" value="<?= e($v['phone']) ?>"></div>
        <div class="field"><label>วันเกิด</label><input type="date" name="birth_date" required max="<?= date('Y-m-d', strtotime('-18 years')) ?>" value="<?= e($v['birth_date']) ?>"></div>
      </div>
      <div class="field"><label>ที่อยู่สำหรับจัดส่ง</label><textarea name="address"><?= e($v['address']) ?></textarea></div>
      <button class="btn block">สมัครสมาชิก</button>
    </form>
    <p class="center small muted mt">มีบัญชีแล้ว? <a class="hl" href="login.php">เข้าสู่ระบบ</a></p>
  </div>
</div>
<?php include 'footer.php'; ?>
