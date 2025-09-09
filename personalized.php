<?php
require_once 'session.php';
require_once 'db.php';

// Require login
require_login();

$user_id = get_current_user_id();
$user_preferences = get_user_preferences($user_id);

// Get personalized recipes based on user preferences
$personalized_recipes = [];
if (!empty($user_preferences)) {
    $placeholders = str_repeat('?,', count($user_preferences) - 1) . '?';
    $query = "
        SELECT r.*, c.name as category_name, c.slug as category_slug,
               COALESCE(AVG(rt.rating), 0) as avg_rating,
               COUNT(rt.id) as rating_count
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.id
        LEFT JOIN ratings rt ON r.id = rt.recipe_id
        WHERE c.slug IN ($placeholders)
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($user_preferences)), ...$user_preferences);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $personalized_recipes[] = $row;
    }
}

// Get all categories for reference
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalized Recipes - RecipeCraft</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1 class="logo">RecipeCraft</h1>
            <nav>
                <ul>
                    <li><a href="main.php" class="nav-btn">Recipes</a></li>
                    <li><a href="personalized.php" class="nav-btn active">Personalized</a></li>
                    <li><a href="categories.php" class="nav-btn">Categories</a></li>
                    <li><a href="top_rated.php" class="nav-btn">Ranking <i class="fas fa-crown"></i></a></li>
                    <li><a href="profile.php" class="nav-btn">Profile</a></li>
                    <li><a href="logout.php" class="nav-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="preferences-summary">
            <div class="container">
                <h2>Your Personalized Recipe Hub</h2>
                <p>Recipes tailored to your taste preferences</p>
                <?php if (!empty($user_preferences)): ?>
                    <div class="preferences-tags">
                        <?php foreach ($user_preferences as $pref): ?>
                            <span class="preference-tag"><?php echo ucfirst(str_replace('-', ' ', $pref)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($user_preferences)): ?>
            <section class="personalized-header">
                <div class="container">
                    <h2>Recipes Matching Your Preferences</h2>
                    <p>We found <?php echo count($personalized_recipes); ?> recipes that match your taste!</p>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($personalized_recipes); ?></div>
                            <div class="stat-label">Matching Recipes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($user_preferences); ?></div>
                            <div class="stat-label">Your Preferences</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count(array_unique(array_column($personalized_recipes, 'category_name'))); ?></div>
                            <div class="stat-label">Categories</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="category-dishes">
                <div class="container">
                    <div class="dishes-grid" id="dishes-container">
                        <?php foreach ($personalized_recipes as $recipe): ?>
                            <div class="dish-card" data-category="<?php echo htmlspecialchars($recipe['category_slug']); ?>">
                                <div class="dish-image">
                                    <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                                    <div class="preference-badge">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                </div>
                                <div class="dish-info">
                                    <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                    <p class="category"><?php echo htmlspecialchars($recipe['category_name']); ?></p>
                                    <div class="rating">
                                        <?php
                                        $rating = round($recipe['avg_rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                        <span class="rating-text">(<?php echo $recipe['rating_count']; ?> ratings)</span>
                                    </div>
                                    <p class="prep-time"><?php echo htmlspecialchars($recipe['prep_time']); ?></p>
                                    <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="view-recipe-btn">View Recipe</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="no-preferences">
                <div class="container">
                    <h3>No Preferences Set Yet</h3>
                    <p>To get personalized recipe recommendations, please set your food preferences in your profile.</p>
                    <a href="profile.php" class="cta-button">Set Preferences</a>
                </div>
            </section>
        <?php endif; ?>

        <section class="categories">
            <div class="container">
                <h2>Explore All Categories</h2>
                <p>Discover more recipes by browsing categories</p>
                <div class="categories-grid">
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <div class="category-card" data-category="<?php echo htmlspecialchars($category['slug']); ?>">
                            <img src="<?php echo htmlspecialchars($category['image_url'] ?: 'https://images.unsplash.com/photo-1544025162-d76694265947?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
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
        // Add category filtering
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function() {
                const category = this.dataset.category;
                window.location.href = `categories.php?category=${category}`;
            });
        });
    </script>
</body>
</html>
