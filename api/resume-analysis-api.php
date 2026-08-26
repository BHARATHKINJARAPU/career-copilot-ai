<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse('error', ['message' => 'Your session has expired. Please log in again.']);
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['resume'])) {
    jsonResponse('error', ['message' => 'No resume file received. Please select a file to upload.']);
}

$file = $_FILES['resume'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary upload folder on server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
    ];
    $errMsg = $uploadErrors[$file['error']] ?? 'Unknown file upload error.';
    jsonResponse('error', ['message' => $errMsg]);
}

if ($file['size'] > 5 * 1024 * 1024) {
    jsonResponse('error', ['message' => 'File size exceeds 5MB limit. Please upload a smaller file.']);
}

$fileName = basename($file['name']);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

$allowedExts = ['pdf', 'docx', 'txt'];
$allowedMimes = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/zip',
    'application/x-zip-compressed',
    'text/plain',
    'application/octet-stream'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($fileExt, $allowedExts) && !in_array($mimeType, $allowedMimes)) {
    jsonResponse('error', ['message' => 'This file could not be recognized as a valid resume. Please upload a valid PDF or DOCX file.']);
}

$uploadDir = __DIR__ . '/../uploads/resumes/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$safeFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
$targetFilePath = $uploadDir . $safeFileName;

if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
    jsonResponse('error', ['message' => 'Failed to save uploaded resume file on server. Check directory permissions.']);
}

$rawContent = @file_get_contents($targetFilePath);
if (empty($rawContent)) {
    $rawContent = "Uploaded resume document: " . $fileName;
}

// Convert non-UTF8 bytes safely so json_encode never fails
$extractedText = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-8');
$extractedText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', ' ', $extractedText);

if (strlen(trim($extractedText)) < 10) {
    jsonResponse('error', ['message' => 'We could not extract readable text from this resume. Please upload a clearer PDF or DOCX file.']);
}

$userProfile = getStudentFullProfile($userId);
$targetRole = $userProfile['career_goal']['target_role'] ?? 'Full Stack Developer';

$score = 65; // Base score for valid file upload
$detectedSkills = [];

$keywordsMap = [
    'javascript' => 'JavaScript',
    'python'     => 'Python',
    'html'       => 'HTML5',
    'css'        => 'CSS3',
    'sql'        => 'MySQL / SQL',
    'react'      => 'React',
    'node'       => 'Node.js',
    'php'        => 'PHP',
    'java'       => 'Java',
    'git'        => 'Git',
    'docker'     => 'Docker',
    'express'    => 'Express.js',
    'mongodb'    => 'MongoDB',
    'c++'        => 'C++'
];

$lowerContent = strtolower($extractedText);
foreach ($keywordsMap as $kw => $label) {
    if (str_contains($lowerContent, $kw)) {
        if (!in_array($label, $detectedSkills)) {
            $detectedSkills[] = $label;
            $score += 4;
        }
    }
}

$score = min(96, max(55, $score));

try {
    $pdo = getDBConnection();

    $analysisJson = json_encode([
        'score' => $score,
        'skills_extracted' => $detectedSkills,
        'target_role' => $targetRole
    ]);

    // Truncate text preview for clean DB storage
    $contentPreview = mb_substr($extractedText, 0, 2000, 'UTF-8');

    $stmtRes = $pdo->prepare("INSERT INTO resumes (user_id, file_name, extracted_content, score, analysis) VALUES (?, ?, ?, ?, ?)");
    $stmtRes->execute([$userId, $safeFileName, $contentPreview, $score, $analysisJson]);

    // Insert extracted skills into user_skills table with source = 'resume'
    if (!empty($detectedSkills)) {
        $stmtSkill = $pdo->prepare("
            INSERT INTO user_skills (user_id, skill_name, skill_level, source)
            VALUES (?, ?, 'Intermediate', 'resume')
            ON DUPLICATE KEY UPDATE skill_level = 'Intermediate', source = 'resume'
        ");
        foreach ($detectedSkills as $skName) {
            $stmtSkill->execute([$userId, $skName]);
        }
    }

    jsonResponse('success', [
        'message' => 'Resume analyzed successfully!',
        'score' => $score,
        'detected_skills' => $detectedSkills,
        'target_role' => $targetRole
    ]);

} catch (Exception $e) {
    jsonResponse('error', ['message' => 'Database error saving analysis: ' . $e->getMessage()]);
}
?>