<?php
require_once 'session.php';
require_once 'db.php';

// Check if user has temp session
if (!isset($_SESSION['temp_user_id'])) {
    redirect('register.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preferences = $_POST['preferences'] ?? [];
    $user_id = $_SESSION['temp_user_id'];
    
    // Update user preferences
    $preferences_json = json_encode($preferences);
    $stmt = $conn->prepare("UPDATE users SET preferences = ? WHERE id = ?");
    $stmt->bind_param("si", $preferences_json, $user_id);
    
    if ($stmt->execute()) {
        // Get user data and set session
        $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Set user session
        set_user_session($user);
        
        // Clear temp session
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_user_name']);
        
        // Redirect to main page
        redirect('main.php');
    } else {
        $error = "Failed to save preferences. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Preferences - RecipeCraft</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1 class="logo">RecipeCraft</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Welcome to RecipeCraft, <?php echo htmlspecialchars($_SESSION['temp_user_name']); ?>!</h2>
                <p>Let's personalize your experience by selecting your food preferences</p>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <div class="preferences-form">
                    <h3>Select Your Food Preferences</h3>
                    <p>Choose the types of food you enjoy. You can always update these later in your profile.</p>
                    
                    <?php if (isset($error)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="preferences-grid">
                            <div class="preference-item">
                                <input type="checkbox" id="main-dishes" name="preferences[]" value="main-dishes">
                                <div>
                                    <label for="main-dishes">Main Dishes</label>
                                    <div class="preference-description">Primary meal courses</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="snacks-street-food" name="preferences[]" value="snacks-street-food">
                                <div>
                                    <label for="snacks-street-food">Snacks & Street Food</label>
                                    <div class="preference-description">Quick bites and street food</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="desserts" name="preferences[]" value="desserts">
                                <div>
                                    <label for="desserts">Desserts</label>
                                    <div class="preference-description">Sweet treats and cakes</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="traditional-food" name="preferences[]" value="traditional-food">
                                <div>
                                    <label for="traditional-food">Traditional Food</label>
                                    <div class="preference-description">Cultural and heritage recipes</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="vegetarian" name="preferences[]" value="vegetarian">
                                <div>
                                    <label for="vegetarian">Vegetarian</label>
                                    <div class="preference-description">Plant-based meals</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="vegan" name="preferences[]" value="vegan">
                                <div>
                                    <label for="vegan">Vegan</label>
                                    <div class="preference-description">No animal products</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="gluten-free" name="preferences[]" value="gluten-free">
                                <div>
                                    <label for="gluten-free">Gluten-Free</label>
                                    <div class="preference-description">No wheat or gluten</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="spicy" name="preferences[]" value="spicy">
                                <div>
                                    <label for="spicy">Spicy Food</label>
                                    <div class="preference-description">Hot and spicy dishes</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="seafood" name="preferences[]" value="seafood">
                                <div>
                                    <label for="seafood">Seafood</label>
                                    <div class="preference-description">Fish and shellfish dishes</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="breakfast" name="preferences[]" value="breakfast">
                                <div>
                                    <label for="breakfast">Breakfast</label>
                                    <div class="preference-description">Morning meals and brunch</div>
                                </div>
                            </div>
                            
                            <div class="preference-item">
                                <input type="checkbox" id="soup" name="preferences[]" value="soup">
                                <div>
                                    <label for="soup">Soup & Stew</label>
                                    <div class="preference-description">Warm and comforting</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" style="text-align: center;">
                            <button type="submit" class="cta-button">
                                <i class="fas fa-arrow-right"></i> Continue to RecipeCraft
                            </button>
                        </div>
                    </form>
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
</body>
</html>
