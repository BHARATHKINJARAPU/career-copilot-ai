<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../onboarding.php");
    exit();
}

$userId = $_SESSION['user_id'];
$pdo = getDBConnection();

try {
    $pdo->beginTransaction();

    // 1. Update Name & Mark Onboarding Completed
    $name = sanitizeInput($_POST['name'] ?? 'Student');
    $stmtUser = $pdo->prepare("UPDATE users SET name = ?, onboarding_completed = 1 WHERE id = ?");
    $stmtUser->execute([$name, $userId]);
    $_SESSION['user_name'] = $name;

    // 2. Academic Profile
    $degree = sanitizeInput($_POST['degree'] ?? 'B.Tech');
    $branch = sanitizeInput($_POST['branch'] ?? 'Computer Science and Engineering');
    $year = sanitizeInput($_POST['year'] ?? '3rd Year');
    $semester = sanitizeInput($_POST['semester'] ?? 'Semester 5');
    $institution = sanitizeInput($_POST['institution'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $academicInterests = sanitizeInput($_POST['academic_interests'] ?? '');

    $stmtProf = $pdo->prepare("
        INSERT INTO student_profiles (user_id, degree, branch, year, semester, institution, location, academic_interests)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            degree = VALUES(degree), branch = VALUES(branch), year = VALUES(year),
            semester = VALUES(semester), institution = VALUES(institution), location = VALUES(location), academic_interests = VALUES(academic_interests)
    ");
    $stmtProf->execute([$userId, $degree, $branch, $year, $semester, $institution, $location, $academicInterests]);

    // 3. Career Goal
    $targetRole = sanitizeInput($_POST['target_role'] ?? 'Full Stack Developer');
    $customGoalText = sanitizeInput($_POST['custom_goal_text'] ?? '');

    $stmtGoal = $pdo->prepare("
        INSERT INTO career_goals (user_id, target_role, custom_goal_text)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE target_role = VALUES(target_role), custom_goal_text = VALUES(custom_goal_text)
    ");
    $stmtGoal->execute([$userId, $targetRole, $customGoalText]);

    // 4. Skills Processing (Source = student_input)
    $skillsJson = $_POST['skills_json'] ?? '[]';
    $skillsList = json_decode($skillsJson, true) ?: [];

    // Clear old manual skills
    $stmtDelSkills = $pdo->prepare("DELETE FROM user_skills WHERE user_id = ? AND source = 'student_input'");
    $stmtDelSkills->execute([$userId]);

    if (!empty($skillsList)) {
        $stmtInsSkill = $pdo->prepare("INSERT INTO user_skills (user_id, skill_name, skill_level, source) VALUES (?, ?, ?, 'student_input')");
        foreach ($skillsList as $sk) {
            $sName = sanitizeInput($sk['name'] ?? '');
            $sLvl = sanitizeInput($sk['level'] ?? 'Beginner');
            if (!empty($sName)) {
                $stmtInsSkill->execute([$userId, $sName, $sLvl]);
            }
        }
    }

    // 5. Thoughts, Strengths & Weaknesses
    $strengths = sanitizeInput($_POST['strengths_text'] ?? '');
    $weaknesses = sanitizeInput($_POST['weaknesses_text'] ?? '');
    $expectations = sanitizeInput($_POST['expectations_text'] ?? '');
    $rawNotes = sanitizeInput($_POST['raw_situation_notes'] ?? '');

    $stmtThoughts = $pdo->prepare("
        INSERT INTO student_thoughts (user_id, raw_situation_notes, strengths_text, weaknesses_text, expectations_text)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            raw_situation_notes = VALUES(raw_situation_notes),
            strengths_text = VALUES(strengths_text),
            weaknesses_text = VALUES(weaknesses_text),
            expectations_text = VALUES(expectations_text)
    ");
    $stmtThoughts->execute([$userId, $rawNotes, $strengths, $weaknesses, $expectations]);

    $pdo->commit();

    header("Location: ../dashboard.php");
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error saving onboarding details: " . htmlspecialchars($e->getMessage()));
}
?>
```

---

### Step 4: Test Again
1. Go back to **[http://localhost/career-copilot-ai/onboarding.php](http://localhost/career-copilot-ai/onboarding.php)** in your browser.
2. Complete the steps and click **Finish & Launch Dashboard 🚀**.
3. It will now process your profile smoothly and launch your student dashboard (`dashboard.php`)!