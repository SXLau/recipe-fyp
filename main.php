<?php
require_once 'session.php';
require_once 'db.php';

// Require login
require_login();

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get all recipes with category names and average ratings
$recipes_query = "
    SELECT r.*, c.name as category_name, c.slug as category_slug,
           COALESCE(AVG(rt.rating), 0) as avg_rating,
           COUNT(rt.id) as rating_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    GROUP BY r.id
    ORDER BY r.created_at DESC
";
$recipes_result = $conn->query($recipes_query);

// Get user preferences for highlighting
$user_preferences = get_user_preferences(get_current_user_id());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RecipeCraft - Discover Amazing Recipes</title>
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
                    <li><a href="main.php" class="nav-btn active">Recipes</a></li>
                    <li><a href="personalized.php" class="nav-btn">Personalized</a></li>
                    <li><a href="categories.php" class="nav-btn">Categories</a></li>
                    <li><a href="top_rated.php" class="nav-btn">Ranking <i class="fas fa-crown"></i></a></li>
                    <li><a href="profile.php" class="nav-btn">Profile</a></li>
                    <li><a href="logout.php" class="nav-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Discover Your Next Favorite Meal</h2>
                <p>Thousands of recipes waiting to be explored. Find your perfect match today!</p>
                <div class="search-bar">
                    <input type="text" id="search-input" placeholder="Search for recipes...">
                    <button id="search-btn"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </section>

        <section class="categories">
            <div class="container">
                <h2>Browse by Category</h2>
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

        <section class="category-dishes">
            <div class="container">
                <h2 id="category-title">All Recipes</h2>
                <div class="dishes-grid" id="dishes-container">
                    <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                        <div class="dish-card" data-category="<?php echo htmlspecialchars($recipe['category_slug']); ?>">
                            <div class="dish-image">
                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                                <?php if (in_array($recipe['category_slug'], $user_preferences)): ?>
                                    <div class="preference-badge">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                <?php endif; ?>
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
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <!-- Rating Modal -->
        <div class="rating-modal" id="rating-modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2 id="rating-dish-title">Rate This Dish</h2>
                <div class="stars-container">
                    <i class="far fa-star" data-rating="1"></i>
                    <i class="far fa-star" data-rating="2"></i>
                    <i class="far fa-star" data-rating="3"></i>
                    <i class="far fa-star" data-rating="4"></i>
                    <i class="far fa-star" data-rating="5"></i>
                </div>
                <p id="rating-selected">Select rating (1-5 stars)</p>
                <button id="submit-rating">Submit Rating</button>
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

    <script src="js/home.js"></script>
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
        // Add search functionality
        document.getElementById('search-btn').addEventListener('click', function() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const recipeCards = document.querySelectorAll('.dish-card');
            
            recipeCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const category = card.querySelector('.category').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || category.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Add category filtering
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function() {
                const category = this.dataset.category;
                const recipeCards = document.querySelectorAll('.dish-card');
                const categoryTitle = this.querySelector('h3').textContent;
                
                document.getElementById('category-title').textContent = categoryTitle;
                
                recipeCards.forEach(recipeCard => {
                    if (category === 'all' || recipeCard.dataset.category === category) {
                        recipeCard.style.display = 'block';
                    } else {
                        recipeCard.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
