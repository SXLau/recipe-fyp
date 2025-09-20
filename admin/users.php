<?php
require_once '../session.php';
require_once '../db.php';
require_admin();

$message = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if ($user_id && $user_id !== get_current_user_id()) { // Prevent self-deletion
        switch ($action) {
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $message = "User deleted successfully!";
                } else {
                    $error = "Failed to delete user.";
                }
                break;
                
            case 'toggle_status':
                // For now, we'll use a simple approach - you could add an 'active' column
                $message = "User status toggle feature can be implemented with an 'active' column.";
                break;
        }
    } else {
        $error = "Invalid user or cannot delete yourself.";
    }
}

// Get all users with their stats
$users_query = "
    SELECT u.*, 
           COALESCE(rs.recipe_count, 0) AS recipe_count,
           COALESCE(rs.rating_count, 0) AS rating_count,
           COALESCE(rs.avg_rating_given, 0) AS avg_rating_given
    FROM users u
    LEFT JOIN (
        SELECT user_id,
               COUNT(*) AS rating_count,
               COUNT(DISTINCT recipe_id) AS recipe_count,
               AVG(rating) AS avg_rating_given
        FROM ratings
        GROUP BY user_id
    ) rs ON rs.user_id = u.id
    ORDER BY u.created_at DESC
";

$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - RecipeCraft Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <h1>RecipeAdmin</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="active"><a href="users.php"><i class="fas fa-users"></i><span>Users</span></a></li>
                    <li><a href="recipes.php"><i class="fas fa-book"></i><span>Recipes</span></a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i><span>Categories</span></a></li>
                    <li><a href="analytics.php"><i class="fas fa-chart-bar"></i><span>Analytics</span></a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="content-section active">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> User Management</h2>
                    <div class="user-stats">
                        <span class="stat-badge">Total Users: <?php echo $users_result->num_rows; ?></span>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="table-container">
                    <table id="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Recipes</th>
                                <th>Ratings</th>
                                <th>Avg Rating</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="user-info-cell">
                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['recipe_count']; ?></td>
                                    <td><?php echo $user['rating_count']; ?></td>
                                    <td>
                                        <?php if ($user['avg_rating_given'] > 0): ?>
                                            <span class="rating-display">
                                                <?php echo number_format($user['avg_rating_given'], 1); ?>
                                                <i class="fas fa-star"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-rating">No ratings</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-info btn-sm" onclick="viewUserPreferences(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-eye"></i> Preferences
                                            </button>
                                            <?php if ($user['id'] !== get_current_user_id()): ?>
                                                <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- User Preferences Modal -->
    <div id="preferences-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>User Preferences</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="preferences-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="delete-user-name"></strong>?</p>
                <p class="warning-text">This action cannot be undone and will delete all associated data.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('delete-modal')">Cancel</button>
                <button class="btn btn-danger" id="confirm-delete">Delete User</button>
            </div>
        </div>
    </div>

    <script>

        // View user preferences
        function viewUserPreferences(userId) {
            fetch(`get_user_preferences.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('preferences-content').innerHTML = data;
                    document.getElementById('preferences-modal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load user preferences');
                });
        }

        // Delete user
        function deleteUser(userId, userName) {
            document.getElementById('delete-user-name').textContent = userName;
            document.getElementById('confirm-delete').onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            };
            document.getElementById('delete-modal').style.display = 'flex';
        }

        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Close modal with X button
        document.querySelectorAll('.close-modal').forEach(button => {
            button.onclick = function() {
                this.closest('.modal').style.display = 'none';
            };
        });
    </script>

</body>
</html>

