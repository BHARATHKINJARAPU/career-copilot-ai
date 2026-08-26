<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ai-config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse('error', ['message' => 'Unauthorized']);
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$transcript = sanitizeInput($input['transcript'] ?? '');

if (empty($transcript)) {
    jsonResponse('error', ['message' => 'Empty voice transcript']);
}

$userProfile = getStudentFullProfile($userId);
$aiHtml = generateAIReasoning($userProfile, $transcript);

// 1. Strip all HTML tags
$plainText = strip_tags($aiHtml);

// 2. Remove markdown symbols (*, #, _, `, ~, >, emojis, bullets)
$plainText = preg_replace('/[*_#`~>]/', '', $plainText);
$plainText = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $plainText);

// 3. Clean up multiple whitespaces and newlines for smooth speech flow
$plainText = preg_replace('/\s+/', ' ', $plainText);
$plainText = trim($plainText);

jsonResponse('success', [
    'replyHtml' => $aiHtml,
    'spokenText' => $plainText
]);
?>