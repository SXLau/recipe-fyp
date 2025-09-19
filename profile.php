<?php
require_once 'session.php';
require_once 'db.php';

// Require login
require_login();

$user_id = get_current_user_id();
$user = get_user_by_id($user_id);
$user_preferences = get_user_preferences($user_id);

$success_message = '';
$error_message = '';

// Handle preference updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preferences = $_POST['preferences'] ?? [];
    
    if (update_user_preferences($user_id, $preferences)) {
        $success_message = "Preferences updated successfully!";
        $user_preferences = $preferences; // Refresh local preferences
    } else {
        $error_message = "Failed to update preferences. Please try again.";
    }
}

// Get user's rating statistics
$rating_stats_query = "
    SELECT COUNT(*) as total_ratings, AVG(rating) as avg_rating
    FROM ratings 
    WHERE user_id = ?
";
$stmt = $conn->prepare($rating_stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rating_stats = $stmt->get_result()->fetch_assoc();

// Get user's recent ratings
$recent_ratings_query = "
    SELECT r.rating, r.created_at, rec.title, rec.image, c.name as category_name
    FROM ratings r
    JOIN recipes rec ON r.recipe_id = rec.id
    JOIN categories c ON rec.category_id = c.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
";
$stmt = $conn->prepare($recent_ratings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_ratings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - RecipeCraft</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/recipe.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1 class="logo">RecipeCraft</h1>
            <button class="nav-toggle" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
            <nav>
                <ul>
                    <li><a href="main.php" class="nav-btn">Recipes</a></li>
                    <li><a href="personalized.php" class="nav-btn">Personalized</a></li>
                    <li><a href="categories.php" class="nav-btn">Categories</a></li>
                    <li><a href="top_rated.php" class="nav-btn">Ranking <i class="fas fa-crown"></i></a></li>
                    <li><a href="profile.php" class="nav-btn active">Profile</a></li>
                    <li><a href="logout.php" class="nav-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="profile-hero">
            <div class="container">
                <h1>Your Profile</h1>
                <p>Manage your preferences and view your activity</p>
            </div>
        </section>

        <div class="profile-container">
            <div class="profile-grid">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $rating_stats['total_ratings']; ?></div>
                            <div class="stat-label">Ratings Given</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($rating_stats['avg_rating'], 1); ?></div>
                            <div class="stat-label">Avg Rating</div>
                        </div>
                    </div>
                </div>

                <!-- Profile Main Content -->
                <div class="profile-main">
                    <?php if ($success_message): ?>
                        <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <h2 class="section-title">Food Preferences</h2>
                    <p>Select the types of food you enjoy to get personalized recipe recommendations.</p>

                    <form method="POST" action="">
                        <div class="preferences-grid">
                            <div class="preference-item">
                                <input type="checkbox" id="main-dishes" name="preferences[]" value="main-dishes" <?php echo in_array('main-dishes', $user_preferences) ? 'checked' : ''; ?>>
                                <label for="main-dishes">Main Dishes</label>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="snacks-street-food" name="preferences[]" value="snacks-street-food" <?php echo in_array('snacks-street-food', $user_preferences) ? 'checked' : ''; ?>>
                                <label for="snacks-street-food">Snacks & Street Food</label>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="desserts" name="preferences[]" value="desserts" <?php echo in_array('desserts', $user_preferences) ? 'checked' : ''; ?>>
                                <label for="desserts">Desserts</label>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="traditional-food" name="preferences[]" value="traditional-food" <?php echo in_array('traditional-food', $user_preferences) ? 'checked' : ''; ?>>
                                <label for="traditional-food">Traditional Food</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="save-preferences">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                    </form>

                    <!-- Recent Ratings -->
                    <?php if ($recent_ratings->num_rows > 0): ?>
                        <div class="recent-ratings">
                            <h2 class="section-title">Recent Ratings</h2>
                            <?php while ($rating = $recent_ratings->fetch_assoc()): ?>
                                <div class="rating-item">
                                    <img src="<?php echo htmlspecialchars($rating['image']); ?>" alt="<?php echo htmlspecialchars($rating['title']); ?>">
                                    <div class="rating-item-info">
                                        <div class="rating-item-title"><?php echo htmlspecialchars($rating['title']); ?></div>
                                        <div class="rating-item-category"><?php echo htmlspecialchars($rating['category_name']); ?></div>
                                        <div class="rating-item-rating">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating['rating']) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="rating-item-date">
                                        <?php echo date('M j, Y', strtotime($rating['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-pinterest"></i></a>
            </div>
            <div class="social-links">
                <p>RecipeCraft</p>
                <p>Get personalized recipe recommendations based on your preferences and dietary needs.</p>
            </div>
            <div class="social-links">
                <p>Contact Us</p>
                <p>UMSKAL, Labuan 87000, Malaysia</p>
                <p>Phone: +6019 857 8167</p>
                <p>Email: wilbert220602@gmail.com</p>
            </div>
        </div>
    </footer>
    <script>
        // Mobile nav toggle
        (function(){
            var toggle = document.querySelector('.nav-toggle');
            var header = document.querySelector('header');
            if (toggle && header) {
                toggle.addEventListener('click', function(){
                    header.classList.toggle('open');
                });
            }
        })();
    </script>
</body>
</html>
