<?php
require_once __DIR__ . '/../config/database.php';

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($status, $data = []) {
    // Clear any previous output buffers to guarantee pure JSON output
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['status' => $status], $data));
    exit();
}

function checkOnboardingOrRedirect($userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT onboarding_completed FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $res = $stmt->fetch();
    if (!$res || (int)$res['onboarding_completed'] === 0) {
        header("Location: onboarding.php");
        exit();
    }
}

function getStudentFullProfile($userId) {
    $pdo = getDBConnection();
    
    // User base
    $stmtUser = $pdo->prepare("SELECT id, name, email, onboarding_completed, created_at FROM users WHERE id = ?");
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch();
    if (!$user) return null;

    // Academic Profile
    $stmtProf = $pdo->prepare("SELECT degree, branch, year, semester, institution, location, academic_interests FROM student_profiles WHERE user_id = ?");
    $stmtProf->execute([$userId]);
    $user['academic'] = $stmtProf->fetch() ?: [];

    // Career Goal
    $stmtGoal = $pdo->prepare("SELECT target_role, custom_goal_text FROM career_goals WHERE user_id = ?");
    $stmtGoal->execute([$userId]);
    $user['career_goal'] = $stmtGoal->fetch() ?: [];

    // Student Thoughts & Free Text Input
    $stmtThoughts = $pdo->prepare("SELECT raw_situation_notes, strengths_text, weaknesses_text, expectations_text FROM student_thoughts WHERE user_id = ?");
    $stmtThoughts->execute([$userId]);
    $thoughtsRow = $stmtThoughts->fetch();
    $user['thoughts'] = $thoughtsRow ? $thoughtsRow['raw_situation_notes'] : '';
    $user['strengths_text'] = $thoughtsRow ? $thoughtsRow['strengths_text'] : '';
    $user['weaknesses_text'] = $thoughtsRow ? $thoughtsRow['weaknesses_text'] : '';
    $user['expectations_text'] = $thoughtsRow ? $thoughtsRow['expectations_text'] : '';

    // Learning Preferences
    $stmtPref = $pdo->prepare("SELECT learning_style, weekly_hours FROM student_preferences WHERE user_id = ?");
    $stmtPref->execute([$userId]);
    $user['preferences'] = $stmtPref->fetch() ?: [];

    // Skills Matrix
    $stmtSkills = $pdo->prepare("SELECT skill_name, skill_level, source FROM user_skills WHERE user_id = ? ORDER BY id ASC");
    $stmtSkills->execute([$userId]);
    $user['skills'] = $stmtSkills->fetchAll() ?: [];

    // Resume Details
    $stmtResume = $pdo->prepare("SELECT file_name, score, analysis, created_at FROM resumes WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmtResume->execute([$userId]);
    $user['resume'] = $stmtResume->fetch() ?: null;

    // Coding Stats
    $stmtCoding = $pdo->prepare("SELECT COUNT(*) as attempted, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved FROM coding_progress WHERE user_id = ?");
    $stmtCoding->execute([$userId]);
    $codingData = $stmtCoding->fetch();
    $user['coding'] = [
        'attempted' => (int)($codingData['attempted'] ?? 0),
        'solved' => (int)($codingData['solved'] ?? 0)
    ];

    // Interview Stats
    $stmtInterview = $pdo->prepare("SELECT COUNT(*) as count, AVG(score) as avg_score FROM interviews WHERE user_id = ?");
    $stmtInterview->execute([$userId]);
    $interviewData = $stmtInterview->fetch();
    $user['interview'] = [
        'count' => (int)($interviewData['count'] ?? 0),
        'avg_score' => $interviewData['avg_score'] !== null ? round((float)$interviewData['avg_score']) : null
    ];

    // Roadmap Progress
    $stmtRoadmap = $pdo->prepare("SELECT rp.progress_percent, r.title FROM roadmap_progress rp JOIN roadmaps r ON rp.roadmap_id = r.id WHERE rp.user_id = ? ORDER BY rp.updated_at DESC LIMIT 1");
    $stmtRoadmap->execute([$userId]);
    $user['roadmap'] = $stmtRoadmap->fetch() ?: null;

    // User Projects Progress
    $stmtProj = $pdo->prepare("SELECT COUNT(*) as count FROM user_projects WHERE user_id = ?");
    $stmtProj->execute([$userId]);
    $user['projects_count'] = (int)($stmtProj->fetch()['count'] ?? 0);

    return $user;
}

// Function Alias for backward compatibility across endpoints
function getUserFullProfile($userId) {
    return getStudentFullProfile($userId);
}
?>