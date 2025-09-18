<?php
require_once 'session.php';
require_once 'db.php';

// Require login
require_login();

$selected_category = $_GET['category'] ?? null;

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get recipes by category if one is selected
$recipes = [];
$current_category = null;

if ($selected_category) {
    $stmt = $conn->prepare("
        SELECT r.*, c.name as category_name, c.slug as category_slug,
               COALESCE(AVG(rt.rating), 0) as avg_rating,
               COUNT(rt.id) as rating_count
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.id
        LEFT JOIN ratings rt ON r.id = rt.recipe_id
        WHERE c.slug = ?
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
    
    // Get current category info
    $stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $current_category = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - RecipeCraft</title>
    <link rel="stylesheet" href="css/home.css">
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
                    <li><a href="categories.php" class="nav-btn active">Categories</a></li>
                    <li><a href="top_rated.php" class="nav-btn">Ranking <i class="fas fa-crown"></i></a></li>
                    <li><a href="profile.php" class="nav-btn">Profile</a></li>
                    <li><a href="logout.php" class="nav-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <?php if ($selected_category && $current_category): ?>
            <!-- Category Header -->
            <section class="category-header">
                <div class="container">
                    <h1><?php echo htmlspecialchars($current_category['name']); ?></h1>
                    <p><?php echo htmlspecialchars($current_category['description']); ?></p>
                </div>
            </section>

            <!-- Recipes in Category -->
            <section class="recipes-section">
                <div class="container">
                    <div class="recipes-header">
                        <h2>Recipes in <?php echo htmlspecialchars($current_category['name']); ?></h2>
                        <div class="recipes-count"><?php echo count($recipes); ?> recipes found</div>
                    </div>

                    <?php if (!empty($recipes)): ?>
                        <div class="dishes-grid" id="dishes-container">
                            <?php foreach ($recipes as $recipe): ?>
                                <div class="dish-card" data-category="<?php echo htmlspecialchars($recipe['category_slug']); ?>">
                                    <div class="dish-image">
                                        <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
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
                    <?php else: ?>
                        <div class="no-recipes">
                            <h3>No Recipes Found</h3>
                            <p>No recipes found in this category yet.</p>
                            <a href="categories.php" class="back-to-categories">Back to Categories</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <!-- Categories Overview -->
            <section class="category-header">
                <div class="container">
                    <h1>Recipe Categories</h1>
                    <p>Browse recipes by category to discover new favorites</p>
                </div>
            </section>

            <section class="categories-overview">
                <div class="container">
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
        <?php endif; ?>
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

    <script>
        // Add category card click functionality
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function() {
                const category = this.dataset.category;
                window.location.href = `categories.php?category=${category}`;
            });
        });

        // Highlight active category if one is selected
        <?php if ($selected_category): ?>
        document.querySelectorAll('.category-card').forEach(card => {
            if (card.dataset.category === '<?php echo htmlspecialchars($selected_category); ?>') {
                card.classList.add('active');
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
