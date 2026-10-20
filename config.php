<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

$DB_HOST = 'localhost';
$DB_NAME = 'pawpaws';
$DB_USER = 'root';
$DB_PASS = '';

$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec("SET time_zone = '+07:00'");

const IMG_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const VID_EXT = ['mp4', 'webm', 'mov'];
const PAY_METHODS = ['transfer' => 'โอนผ่านธนาคาร', 'promptpay' => 'พร้อมเพย์', 'card' => 'บัตรเครดิต/เดบิต', 'cod' => 'เก็บเงินปลายทาง'];

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function user() { return $_SESSION['user'] ?? null; }
function is_admin() { return (user()['role'] ?? '') === 'admin'; }
function redirect($url) { header("Location: $url"); exit; }

function require_login() {
    if (!user()) redirect('login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
}
function require_admin() {
    require_login();
    if (!is_admin()) { flash('หน้านี้สำหรับผู้ดูแลระบบเท่านั้น', 'err'); redirect('index.php'); }
}
function require_customer() {
    require_login();
    if (is_admin()) { flash('บัญชีแอดมินไม่สามารถสั่งซื้อได้', 'err'); redirect('admin.php'); }
}

function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('Invalid request'); }
}

function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function age_text($birth) {
    $d = (new DateTime($birth))->diff(new DateTime('today'));
    if ($d->y > 0) return $d->y . ' ปี' . ($d->m ? ' ' . $d->m . ' เดือน' : '');
    if ($d->m > 0) return $d->m . ' เดือน' . ($d->d ? ' ' . $d->d . ' วัน' : '');
    return $d->d . ' วัน';
}
function thai_date($date, $time = false) {
    if (!$date) return '-';
    $m = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $t = strtotime($date);
    return date('j', $t) . ' ' . $m[(int)date('n', $t)] . ' ' . (date('Y', $t) + 543) . ($time ? ' ' . date('H:i', $t) . ' น.' : '');
}
function money($n) { return '฿' . number_format((float)$n, 0); }

function upload($field, array $allowed) {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new Exception('อัปโหลดไฟล์ไม่สำเร็จ (ไฟล์อาจใหญ่เกินไป)');
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
    $okMime = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/');
    if (!in_array($ext, $allowed, true) || !$okMime) throw new Exception('ชนิดไฟล์ไม่รองรับ: .' . $ext);
    $name = date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0775, true);
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], __DIR__ . '/uploads/' . $name)) throw new Exception('บันทึกไฟล์ไม่สำเร็จ');
    return $name;
}
function delete_file($name) {
    if (preg_match('#^https?://#i', (string)$name)) return;
    if ($name && is_file(__DIR__ . '/uploads/' . basename($name))) @unlink(__DIR__ . '/uploads/' . basename($name));
}

function media_url($file) {
    return preg_match('#^https?://#i', (string)$file) ? $file : 'uploads/' . $file;
}
function img_tag($file, $alt = '') {
    return $file
        ? '<img src="' . e(media_url($file)) . '" alt="' . e($alt) . '" loading="lazy" onerror="this.onerror=null;this.src=\'assets/paw.svg\'">'
        : '<div class="ph"><svg viewBox="0 0 64 64" width="38%" fill="#E3CFB8" aria-hidden="true"><ellipse cx="18" cy="24" rx="6" ry="8"/><ellipse cx="32" cy="16" rx="6" ry="8"/><ellipse cx="46" cy="24" rx="6" ry="8"/><ellipse cx="10" cy="38" rx="5" ry="7"/><path d="M32 30c-9 0-17 10-17 18 0 6 5 8 9 7 3-1 5-2 8-2s5 1 8 2c4 1 9-1 9-7 0-8-8-18-17-18z"/></svg></div>';
}

function pet_card($p) {
    $sold = $p['status'] === 'sold';
    return '<a class="card pet' . ($sold ? ' sold' : '') . '" href="pet.php?id=' . (int)$p['id'] . '">'
        . '<div class="thumb">' . img_tag($p['image'], $p['name'])
        . ($sold ? '<span class="home-badge">ได้บ้านแล้ว 🏡</span>' : '') . '</div>'
        . '<div class="body"><div class="meta"><span class="tag">' . e($p['cat']) . '</span>'
        . '<span class="muted">' . ($p['gender'] === 'male' ? '♂ ตัวผู้' : '♀ ตัวเมีย') . '</span></div>'
        . '<h3>' . e($p['name']) . '</h3>'
        . '<p class="muted">' . e($p['breed']) . ' · อายุ ' . age_text($p['birth_date']) . '</p>'
        . '<div class="price">' . money($p['price']) . '</div></div></a>';
}

function cart_count() {
    global $pdo;
    if (!user() || is_admin()) return 0;
    $st = $pdo->prepare('SELECT COUNT(*) FROM cart_items WHERE user_id=?');
    $st->execute([user()['id']]);
    return (int)$st->fetchColumn();
}

function track_step($elapsed) {
    if ($elapsed >= 15) return 3;
    if ($elapsed >= 10) return 2;
    if ($elapsed >= 5) return 1;
    return 0;
}
const TRACK_TEXT = [
    0 => 'ชำระเงินเรียบร้อย กำลังเตรียมน้องให้พร้อมเดินทาง',
    1 => 'กำลังจัดส่ง',
    2 => 'พนักงานกำลังพาน้องไปส่งแล้วนะ',
    3 => 'น้องถึงที่หมายแล้ว ออกมารับน้องหน่อย',
];

function pet_input(array $post) {
    $v = array_map(fn($x) => is_string($x) ? trim($x) : $x, $post);
    $err = [];
    if (($v['name'] ?? '') === '') $err[] = 'กรุณากรอกชื่อน้อง';
    if (!in_array($v['gender'] ?? '', ['male', 'female'], true)) $err[] = 'เพศไม่ถูกต้อง';
    $bd = DateTime::createFromFormat('Y-m-d', $v['birth_date'] ?? '');
    if (!$bd || $bd > new DateTime()) $err[] = 'วันเกิดไม่ถูกต้อง';
    if (!is_numeric($v['price'] ?? '') || $v['price'] < 0) $err[] = 'ราคาไม่ถูกต้อง';
    if (isset($v['status']) && !in_array($v['status'], ['available', 'sold'], true)) $err[] = 'สถานะไม่ถูกต้อง';
    return [$v, $err];
}
