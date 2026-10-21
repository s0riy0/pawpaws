<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
if (!user()) { http_response_code(401); exit(json_encode(['error' => 'login'])); }
chat_schema();

$me = user()['id'];

// สถานะแชทที่ยังไม่ได้อ่าน (ใช้กับจุดแดงบนเมนู + รายชื่อห้องแชท)
if (isset($_GET['status'])) {
    if (is_admin()) {
        $threads = $pdo->query('SELECT u.id, u.full_name, m.body,
                (SELECT COUNT(*) FROM messages x WHERE x.customer_id = u.id AND x.sender_id = u.id AND x.is_read = 0) AS unread
            FROM users u JOIN messages m ON m.id = (SELECT MAX(id) FROM messages WHERE customer_id = u.id)
            ORDER BY m.id DESC')->fetchAll();
        $threads = array_map(fn($t) => ['id' => (int)$t['id'], 'name' => $t['full_name'], 'body' => $t['body'], 'unread' => (int)$t['unread']], $threads);
        $total = array_sum(array_column($threads, 'unread'));
    } else {
        $st = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE customer_id = ? AND sender_id <> ? AND is_read = 0');
        $st->execute([$me, $me]);
        $total = (int)$st->fetchColumn();
        $threads = [];
    }
    $mx = $pdo->prepare(is_admin()
        ? 'SELECT COALESCE(MAX(id),0) FROM messages WHERE sender_id <> ?'
        : 'SELECT COALESCE(MAX(id),0) FROM messages WHERE customer_id = ? AND sender_id <> ?');
    $mx->execute(is_admin() ? [$me] : [$me, $me]);
    exit(json_encode(['total' => $total, 'last' => (int)$mx->fetchColumn(), 'threads' => $threads], JSON_UNESCAPED_UNICODE));
}

$cid = is_admin() ? (int)($_REQUEST['c'] ?? 0) : $me;
if (!$cid) { http_response_code(400); exit(json_encode(['error' => 'customer'])); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $body = trim($_POST['body'] ?? '');
    if ($body !== '' && mb_strlen($body) <= 2000) {
        $pet = (int)($_POST['pet'] ?? 0) ?: null;
        $pdo->prepare('INSERT INTO messages (customer_id, sender_id, pet_id, body) VALUES (?,?,?,?)')->execute([$cid, $me, $pet, $body]);
    }
}

// เปิดห้องแชทอยู่ = อ่านข้อความของอีกฝ่ายแล้ว
if (is_admin()) {
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE customer_id = ? AND sender_id = ? AND is_read = 0')->execute([$cid, $cid]);
} else {
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE customer_id = ? AND sender_id <> ? AND is_read = 0')->execute([$cid, $me]);
}

$st = $pdo->prepare('SELECT m.id, m.body, m.sender_id, m.is_read, m.created_at, u.full_name, u.role, p.name AS pet_name, p.id AS pet_id
                     FROM messages m JOIN users u ON u.id = m.sender_id LEFT JOIN pets p ON p.id = m.pet_id
                     WHERE m.customer_id = ? AND m.id > ? ORDER BY m.id');
$st->execute([$cid, (int)($_REQUEST['after'] ?? 0)]);
$list = $st->fetchAll();

// สถานะอ่านแล้วของข้อความที่เราส่ง
$rd = $pdo->prepare('SELECT COALESCE(MAX(id),0) FROM messages WHERE customer_id = ? AND sender_id = ? AND is_read = 1');
$rd->execute([$cid, $me]);

echo json_encode([
    'read_upto' => (int)$rd->fetchColumn(),
    'msgs' => array_map(fn($m) => [
        'id'   => (int)$m['id'],
        'body' => $m['body'],
        'mine' => $m['sender_id'] == $me,
        'read' => (bool)$m['is_read'],
        'name' => $m['role'] === 'admin' ? 'PawPaws' : $m['full_name'],
        'pet'  => $m['pet_name'],
        'pet_id' => $m['pet_id'],
        'time' => date('d/m H:i', strtotime($m['created_at'])),
    ], $list),
], JSON_UNESCAPED_UNICODE);
