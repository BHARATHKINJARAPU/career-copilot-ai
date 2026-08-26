<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $msg = "Thank you! Your message has been saved.";
    }
}

$extraCss = 'contact.css';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="contact-container" style="max-width:800px; margin:40px auto; padding:0 24px;">
  <div class="glass-card" style="padding: 35px;">
    <h2>Contact Hackathon Team</h2>
    <?php if ($msg): ?>
      <div style="background:rgba(16,185,129,0.15); border:1px solid var(--accent-emerald); color:var(--accent-emerald); padding:10px; border-radius:8px; margin:16px 0;">
        <?php echo $msg; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="contact.php" style="margin-top:20px;">
      <div style="margin-bottom:16px;">
        <label style="display:block; margin-bottom:6px; font-size:0.85rem; color:var(--text-muted);">Name</label>
        <input type="text" name="name" class="form-control" required style="width:100%;">
      </div>
      <div style="margin-bottom:16px;">
        <label style="display:block; margin-bottom:6px; font-size:0.85rem; color:var(--text-muted);">Email</label>
        <input type="email" name="email" class="form-control" required style="width:100%;">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block; margin-bottom:6px; font-size:0.85rem; color:var(--text-muted);">Message</label>
        <textarea name="message" class="form-control" rows="4" required style="width:100%;"></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Send Message 🚀</button>
    </form>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>