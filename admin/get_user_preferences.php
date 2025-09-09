<?php
require_once '../session.php';
require_once '../db.php';
require_admin();

$user_id = (int)($_GET['id'] ?? 0);

if (!$user_id) {
    echo '<p>Invalid user ID.</p>';
    exit;
}

// Get user info
$stmt = $conn->prepare("SELECT name, email, preferences FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo '<p>User not found.</p>';
    exit;
}

$preferences = json_decode($user['preferences'] ?? '[]', true);
?>

<div class="user-details">
    <h4><?php echo htmlspecialchars($user['name']); ?></h4>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    
    <h5>Food Preferences:</h5>
    <?php if (!empty($preferences)): ?>
        <div class="preferences-list">
            <?php foreach ($preferences as $preference): ?>
                <span class="preference-tag"><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $preference))); ?></span>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-preferences">No preferences set</p>
    <?php endif; ?>
</div>

