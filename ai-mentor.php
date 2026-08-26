<?php
require_once 'includes/auth-check.php';
require_once 'includes/functions.php';

$userId = $_SESSION['user_id'];
checkOnboardingOrRedirect($userId);

$userProfile = getStudentFullProfile($userId);

$pdo = getDBConnection();
$stmtMsg = $pdo->prepare("SELECT sender, message, created_at FROM mentor_messages WHERE user_id = ? ORDER BY id ASC LIMIT 50");
$stmtMsg->execute([$userId]);
$messages = $stmtMsg->fetchAll();

$extraCss = 'ai-mentor.css';
$extraJs = 'ai-mentor.js';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$skills = $userProfile['skills'] ?? [];
$skillNames = [];
if (is_array($skills)) {
    foreach ($skills as $s) {
        if (is_array($s) && isset($s['skill_name'])) {
            $skillNames[] = $s['skill_name'];
        } elseif (is_string($s)) {
            $skillNames[] = $s;
        }
    }
}
$skillText = !empty($skillNames) ? implode(', ', $skillNames) : 'None added yet';

// Safe Target Role Extraction
$targetRoleRaw = 'Not selected yet';
if (isset($userProfile['career_goal']) && is_array($userProfile['career_goal'])) {
    $targetRoleRaw = $userProfile['career_goal']['target_role'] ?? 'Not selected yet';
} elseif (isset($userProfile['career_goal']) && is_string($userProfile['career_goal'])) {
    $targetRoleRaw = $userProfile['career_goal'];
}
$targetRole = htmlspecialchars((string)$targetRoleRaw, ENT_QUOTES, 'UTF-8');

// Safe Academic Details Extraction
$academic = $userProfile['academic'] ?? [];
$branch = is_array($academic) ? ($academic['branch'] ?? 'Not specified') : 'Not specified';
$year = is_array($academic) ? ($academic['year'] ?? 'Not specified') : 'Not specified';
$studentName = htmlspecialchars((string)($userProfile['name'] ?? 'Student'), ENT_QUOTES, 'UTF-8');
?>

<main class="mentor-container">
  <aside class="mentor-sidebar glass-card">
    <h3 style="font-size: 1.05rem;">My Stored Profile</h3>
    
    <div>
      <div class="text-dim" style="font-size: 0.75rem; text-transform: uppercase;">Active Goal</div>
      <div style="font-weight: 700; color: var(--accent-cyan); font-size: 0.95rem;"><?php echo $targetRole; ?></div>
    </div>

    <div>
      <div class="text-dim" style="font-size: 0.75rem; text-transform: uppercase;">Entered Skills</div>
      <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;"><?php echo htmlspecialchars($skillText, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>

    <div>
      <div class="text-dim" style="font-size: 0.75rem; text-transform: uppercase;">Academic Details</div>
      <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;"><?php echo htmlspecialchars((string)$year, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string)$branch, ENT_QUOTES, 'UTF-8'); ?>)</div>
    </div>

    <hr style="border: 0; border-top: 1px solid var(--border-glass);">

    <div style="display:flex; flex-direction:column; gap:8px;">
      <button class="btn btn-outline voice-toggle-btn" id="startVoiceBtn" style="padding:8px 12px; font-size:0.85rem;">🎤 Start Voice Mentor</button>
      <button class="btn btn-outline" id="stopVoiceBtn" style="padding:8px 12px; font-size:0.85rem; display:none;">🔊 Stop Audio</button>
    </div>
  </aside>

  <section class="chat-center glass-card">
    <div class="chat-header">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--accent-emerald);"></div>
        <strong>Copilot AI Mentor</strong>
      </div>
      <span class="glow-pill" style="font-size: 0.72rem;">Model: Context Engine v2.6</span>
    </div>

    <div class="chat-messages" id="chatMessages">
      <div class="message ai">
        <div class="ai-structured-response">
          <div class="glow-pill" style="margin-bottom: 4px;">Context Synchronized</div>
          <p>Hello <?php echo $studentName; ?>! 👋 I have synchronized your stored profile (Target Goal: <strong><?php echo $targetRole; ?></strong>).</p>
        </div>
      </div>

      <?php foreach ($messages as $msg): ?>
        <div class="message <?php echo $msg['sender'] === 'user' ? 'user' : 'ai'; ?>">
          <?php echo $msg['message']; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="typing-indicator" id="typingIndicator">
      Copilot AI is querying your profile and building response...
    </div>

    <div class="chat-prompts-bar">
      <span class="prompt-chip" data-prompt="Analyze my skills.">"Analyze my skills"</span>
      <span class="prompt-chip" data-prompt="What should I learn next?">"What should I learn next?"</span>
      <span class="prompt-chip" data-prompt="Suggest a project based on my skills.">"Suggest a project"</span>
      <span class="prompt-chip" data-prompt="Help me prepare for an interview.">"Prep for interview"</span>
    </div>

    <div class="chat-input-box">
      <input type="text" id="chatInput" class="chat-input" placeholder="Ask your AI mentor anything about your career progression...">
      <button id="sendChatBtn" class="btn btn-primary" style="padding: 10px 20px;">Send 🚀</button>
    </div>
  </section>

  <aside class="mentor-sidebar glass-card">
    <h3 style="font-size: 1.05rem;">My Activity Stats</h3>

    <div>
      <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
        <span>Skills Logged</span>
        <span style="color:var(--accent-cyan); font-weight:700;"><?php echo is_array($skills) ? count($skills) : 0; ?></span>
      </div>
    </div>

    <div>
      <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
        <span>Coding Solved</span>
        <span style="color:var(--accent-emerald); font-weight:700;"><?php echo (int)($userProfile['coding']['solved'] ?? 0); ?></span>
      </div>
    </div>
  </aside>
</main>

<script src="js/voice-mentor.js"></script>
<?php require_once 'includes/footer.php'; ?>