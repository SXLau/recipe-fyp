<?php
require_once 'session.php';
require_once 'db.php';

// Require login
require_login();

$selected_filter = $_GET['filter'] ?? 'all';

// Get top rated recipes with category filtering
$recipes = [];
if ($selected_filter === 'all') {
    $query = "
        SELECT r.*, c.name as category_name, c.slug as category_slug,
               COALESCE(AVG(rt.rating), 0) as avg_rating,
               COUNT(rt.id) as rating_count
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.id
        LEFT JOIN ratings rt ON r.id = rt.recipe_id
        GROUP BY r.id
        HAVING avg_rating > 0
        ORDER BY avg_rating DESC, rating_count DESC
    ";
    $result = $conn->query($query);
} else {
    $stmt = $conn->prepare("
        SELECT r.*, c.name as category_name, c.slug as category_slug,
               COALESCE(AVG(rt.rating), 0) as avg_rating,
               COUNT(rt.id) as rating_count
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.id
        LEFT JOIN ratings rt ON r.id = rt.recipe_id
        WHERE c.slug = ?
        GROUP BY r.id
        HAVING avg_rating > 0
        ORDER BY avg_rating DESC, rating_count DESC
    ");
    $stmt->bind_param("s", $selected_filter);
    $stmt->execute();
    $result = $stmt->get_result();
}

while ($row = $result->fetch_assoc()) {
    $recipes[] = $row;
}

// Get all categories for filter buttons
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RecipeCraft - Top Recipes</title>
    <link rel="stylesheet" href="css/ranking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1 class="logo">RecipeCraft</h1>
            <nav>
                <ul>
                    <li><a href="main.php" class="nav-btn">Recipes</a></li>
                    <li><a href="personalized.php" class="nav-btn">Personalized</a></li>
                    <li><a href="categories.php" class="nav-btn">Categories</a></li>
                    <li><a href="top_rated.php" class="nav-btn active">Ranking <i class="fas fa-crown"></i></a></li>
                    <li><a href="profile.php" class="nav-btn">Profile</a></li>
                    <li><a href="logout.php" class="nav-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="ranking-hero">
            <div class="container">
                <h2>Top Rated Recipes</h2>
                <p>Discover the most loved recipes by our community</p>
            </div>
        </section>

        <section class="ranking-filters">
            <div class="container">
                <div class="ranking-filters">
                    <button class="filter-btn <?php echo $selected_filter === 'all' ? 'active' : ''; ?>" data-filter="all">All Categories</button>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <button class="filter-btn <?php echo $selected_filter === $category['slug'] ? 'active' : ''; ?>" data-filter="<?php echo htmlspecialchars($category['slug']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </button>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <section class="ranking-section">
            <div class="container">
                <div class="ranking-grid" id="ranking-container">
                    <?php if (!empty($recipes)): ?>
                        <?php foreach ($recipes as $index => $recipe): ?>
                            <div class="ranking-item" data-category="<?php echo htmlspecialchars($recipe['category_slug']); ?>">
                                <div class="rank-badge"><?php echo $index + 1; ?></div>
                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                                <div class="ranking-info">
                                    <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                    <p class="ranking-category"><?php echo htmlspecialchars($recipe['category_name']); ?></p>
                                    <div class="ranking-rating">
                                        <div class="rating-stars">
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
                                        </div>
                                        <div class="rating-details">
                                            <span class="rating-score"><?php echo number_format($recipe['avg_rating'], 1); ?></span>
                                            <span class="rating-count">(<?php echo $recipe['rating_count']; ?> ratings)</span>
                                        </div>
                                    </div>
                                    <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="view-recipe-btn">View Recipe</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-recipes">
                            <h3>No Top Rated Recipes Found</h3>
                            <p>No recipes have been rated yet in this category.</p>
                        </div>
                    <?php endif; ?>
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

    <script src="js/ranking.js"></script>
    <script>
        // Add filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;
                window.location.href = `top_rated.php?filter=${filter}`;
            });
        });
    </script>
</body>
</html>
