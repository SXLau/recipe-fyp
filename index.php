<?php
require_once 'session.php';
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RecipeCraft - Discover Your Next Favorite Recipe</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1 class="logo">RecipeCraft</h1>
            <button class="nav-toggle" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
            <nav>
                <ul>
                    <?php if (is_logged_in()): ?>
                        <li><a href="main.php">Browse Recipes</a></li>
                        <li><a href="personalized.php">Personalized</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="btn">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Discover Recipes Tailored to Your Taste</h2>
                <p>Get personalized recipe recommendations based on your preferences and dietary needs.</p>
                <?php if (is_logged_in()): ?>
                    <a href="main.php" class="cta-button">Start Exploring</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>Why Choose RecipeCraft?</h2>
                <div class="features-grid">
                    <div class="features-container">
                        <div class="feature">
                            <i class="fas fa-book"></i>
                            <h3>Numerous Recipes</h3>
                            <p>Explore our extensive collection of recipes from around the world.</p>
                        </div>
                        <div class="feature">
                            <i class="fas fa-star"></i>
                            <h3>Smart Rating System</h3>
                            <p>Rate recipes and see community ratings to find the best dishes.</p>
                        </div>
                        <div class="feature">
                            <i class="fas fa-trophy"></i>
                            <h3>Interactive Ranking</h3>
                            <p>Discover top-ranked recipes based on real user ratings.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>


    <footer>
        <div class="container">
            <p>&copy; 2025 RecipeCraft. All rights reserved.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-pinterest"></i></a>
            </div>
        </div>
    </footer>

    <script src="js/index.js"></script>
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
