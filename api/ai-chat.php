<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ai-config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse('error', ['message' => 'Unauthorized user session']);
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$prompt = sanitizeInput($input['prompt'] ?? '');

if (empty($prompt)) {
    jsonResponse('error', ['message' => 'Empty query parameter']);
}

$userProfile = getUserFullProfile($userId);
$pdo = getDBConnection();

// Save User Prompt
$stmtUser = $pdo->prepare("INSERT INTO mentor_messages (user_id, sender, message) VALUES (?, 'user', ?)");
$stmtUser->execute([$userId, $prompt]);

// Generate AI Response using Database Profile Context
$aiResponse = generateAIReasoning($userProfile, $prompt);

// Save AI Response
$stmtAi = $pdo->prepare("INSERT INTO mentor_messages (user_id, sender, message) VALUES (?, 'ai', ?)");
$stmtAi->execute([$userId, $aiResponse]);

jsonResponse('success', ['reply' => $aiResponse]);
?>