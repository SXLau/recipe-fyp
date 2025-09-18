<?php
require_once 'session.php';
require_once 'db.php';

// Require login
require_login();

$recipe_id = (int)($_GET['id'] ?? 0);
if (!$recipe_id) {
    redirect('main.php');
}

// Get recipe details
$stmt = $conn->prepare("
    SELECT r.*, c.name as category_name, c.slug as category_slug,
           COALESCE(AVG(rt.rating), 0) as avg_rating,
           COUNT(rt.id) as rating_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    WHERE r.id = ?
    GROUP BY r.id
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();

if (!$recipe) {
    redirect('main.php');
}

// Check if user has already rated this recipe
$user_id = get_current_user_id();
$user_rating = null;
$stmt = $conn->prepare("SELECT rating FROM ratings WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_rating = $result->fetch_assoc()['rating'];
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && !$user_rating) {
    $rating = (int)$_POST['rating'];
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO ratings (user_id, recipe_id, rating) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $recipe_id, $rating);
        if ($stmt->execute()) {
            // Refresh page to show updated rating
            header("Location: recipe.php?id=$recipe_id");
            exit();
        }
    }
}

// Get related recipes (same category or similar ingredients)
$related_recipes = [];
$stmt = $conn->prepare("
    SELECT r.*, c.name as category_name, c.slug as category_slug,
           COALESCE(AVG(rt.rating), 0) as avg_rating,
           COUNT(rt.id) as rating_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    WHERE r.id != ? AND r.category_id = ?
    GROUP BY r.id
    ORDER BY avg_rating DESC
    LIMIT 6
");
$stmt->bind_param("ii", $recipe_id, $recipe['category_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $related_recipes[] = $row;
}

// Get user preferences for highlighting
$user_preferences = get_user_preferences($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - RecipeCraft</title>
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
                    <li><a href="profile.php" class="nav-btn">Profile</a></li>
                    <li><a href="logout.php" class="nav-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="recipe-hero">
            <div class="container">
                <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                <p>Discover this amazing recipe</p>
            </div>
        </section>

        <div class="recipe-container">
            <div class="recipe-main">
                <div class="recipe-image">
                    <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    <?php if (in_array($recipe['category_slug'], $user_preferences)): ?>
                        <div class="preference-badge">
                            <i class="fas fa-heart"></i> Your Preference
                        </div>
                    <?php endif; ?>
                </div>

                <div class="recipe-details">
                    <h1 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h1>
                    <span class="recipe-category"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                    
                    <div class="recipe-rating">
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
                        <div class="rating-score"><?php echo number_format($recipe['avg_rating'], 1); ?></div>
                        <div class="rating-count">(<?php echo $recipe['rating_count']; ?> ratings)</div>
                    </div>

                    <div class="recipe-meta">
                        <div class="meta-item">
                            <div class="meta-label">Prep Time</div>
                            <div class="meta-value"><?php echo htmlspecialchars($recipe['prep_time']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Difficulty</div>
                            <div class="meta-value"><?php echo htmlspecialchars($recipe['difficulty']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Servings</div>
                            <div class="meta-value"><?php echo htmlspecialchars($recipe['servings']); ?></div>
                        </div>
                    </div>

                    <?php if ($recipe['description']): ?>
                        <div class="recipe-description">
                            <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rating Section -->
            <div class="rating-section">
                <h3>Rate This Recipe</h3>
                <?php if ($user_rating): ?>
                    <div class="already-rated">
                        <i class="fas fa-check-circle"></i> You rated this recipe <?php echo $user_rating; ?> stars
                    </div>
                <?php else: ?>
                    <form method="POST" class="rating-form">
                        <div class="stars-container" id="stars-container">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="selected-rating" value="">
                        <button type="submit" class="submit-rating" id="submit-rating" disabled>Submit Rating</button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Recipe Content -->
            <div class="recipe-content">
                <div class="recipe-section">
                    <h3>Ingredients</h3>
                    <ul class="ingredients-list">
                        <?php
                        $ingredients = explode("\n", $recipe['ingredients']);
                        foreach ($ingredients as $ingredient) {
                            if (trim($ingredient)) {
                                echo '<li>' . htmlspecialchars(trim($ingredient)) . '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>

                <div class="recipe-section">
                    <h3>Instructions</h3>
                    <ol class="steps-list">
                        <?php
                        $steps = explode("\n", $recipe['steps']);
                        foreach ($steps as $index => $step) {
                            if (trim($step)) {
                                echo '<li>';
                                echo '<div class="step-number">' . ($index + 1) . '</div>';
                                echo '<div>' . htmlspecialchars(trim($step)) . '</div>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ol>
                </div>
            </div>

            <!-- Related Recipes -->
            <?php if (!empty($related_recipes)): ?>
                <section class="related-recipes">
                    <div class="related-header">
                        <h2>More Like This</h2>
                        <p>Discover similar recipes you might enjoy</p>
                    </div>
                    <div class="related-grid">
                        <?php foreach ($related_recipes as $related): ?>
                            <div class="related-item">
                                <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                <div class="related-info">
                                    <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                                    <p class="related-category"><?php echo htmlspecialchars($related['category_name']); ?></p>
                                    <div class="related-rating">
                                        <?php
                                        $rating = round($related['avg_rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                        <span style="margin-left: 10px; color: #6c757d;"><?php echo number_format($related['avg_rating'], 1); ?></span>
                                    </div>
                                    <a href="recipe.php?id=<?php echo $related['id']; ?>" class="view-related-btn">View Recipe</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
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

    <script>
        // Star rating functionality
        const stars = document.querySelectorAll('#stars-container i');
        const selectedRatingInput = document.getElementById('selected-rating');
        const submitButton = document.getElementById('submit-rating');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                selectedRatingInput.value = rating;
                submitButton.disabled = false;
                
                // Update star display
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.className = 'fas fa-star';
                        s.style.color = '#ffc107';
                    } else {
                        s.className = 'far fa-star';
                        s.style.color = '#ddd';
                    }
                });
            });

            star.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#ffc107';
                    }
                });
            });

            star.addEventListener('mouseleave', function() {
                const selectedRating = selectedRatingInput.value;
                stars.forEach((s, index) => {
                    if (index < selectedRating) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });
    </script>
</body>
</html>
