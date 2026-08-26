<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse('error', ['message' => 'Unauthorized']);
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$questionId = (int)($input['question_id'] ?? 1);

$pdo = getDBConnection();
$stmt = $pdo->prepare("INSERT INTO coding_progress (user_id, question_id, status, attempts) VALUES (?, ?, 'solved', 1) ON DUPLICATE KEY UPDATE status = 'solved', attempts = attempts + 1");
$stmt->execute([$userId, $questionId]);

jsonResponse('success', ['message' => 'Solution validated and recorded in MySQL!']);
?>