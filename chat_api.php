<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
if (!user()) { http_response_code(401); exit(json_encode(['error' => 'login'])); }

$me = user()['id'];
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

$st = $pdo->prepare('SELECT m.id, m.body, m.sender_id, m.created_at, u.full_name, u.role, p.name AS pet_name, p.id AS pet_id
                     FROM messages m JOIN users u ON u.id = m.sender_id LEFT JOIN pets p ON p.id = m.pet_id
                     WHERE m.customer_id = ? AND m.id > ? ORDER BY m.id');
$st->execute([$cid, (int)($_REQUEST['after'] ?? 0)]);

echo json_encode(array_map(fn($m) => [
    'id'   => (int)$m['id'],
    'body' => $m['body'],
    'mine' => $m['sender_id'] == $me,
    'name' => $m['role'] === 'admin' ? 'PawPaws' : $m['full_name'],
    'pet'  => $m['pet_name'],
    'pet_id' => $m['pet_id'],
    'time' => date('d/m H:i', strtotime($m['created_at'])),
], $st->fetchAll()), JSON_UNESCAPED_UNICODE);
