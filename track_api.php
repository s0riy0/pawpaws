<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
if (!user()) { http_response_code(401); exit(json_encode(['error' => 'login'])); }

$st = $pdo->prepare('SELECT id, user_id, status, received_at, TIMESTAMPDIFF(SECOND, paid_at, NOW()) AS elapsed FROM orders WHERE id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$o = $st->fetch();
if (!$o || (!is_admin() && $o['user_id'] != user()['id'])) { http_response_code(404); exit(json_encode(['error' => 'not found'])); }

$step = track_step((int)$o['elapsed']);
echo json_encode([
    'step'     => $o['status'] === 'received' ? 4 : $step,
    'text'     => $o['status'] === 'received' ? 'รับน้องเรียบร้อยแล้ว ขอให้มีความสุขกับสมาชิกใหม่ ' : TRACK_TEXT[$step],
    'elapsed'  => (int)$o['elapsed'],
    'received' => $o['status'] === 'received',
    'can_confirm' => $o['status'] === 'shipping' && $step === 3 && !is_admin(),
], JSON_UNESCAPED_UNICODE);
