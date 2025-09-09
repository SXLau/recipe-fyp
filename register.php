<?php
require_once 'session.php';
require_once 'db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }

    // If no errors, create user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $_SESSION['temp_user_id'] = $user_id;
            $_SESSION['temp_user_name'] = $name;
            $success = true;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RecipeCraft</title>
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
        <?php if (!$success): ?>
            <section class="hero">
                <div class="container">
                    <h2>Create Your Account</h2>
                    <p>Join RecipeCraft and discover personalized recipes</p>
                </div>
            </section>

            <section class="features">
                <div class="container">
                    <div class="preferences-form">
                        <?php if (!empty($errors)): ?>
                            <div class="error-messages">
                                <?php foreach ($errors as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit">Create Account</button>
                        </form>
                        
                        <p class="form-toggle">
                            Already have an account? <a href="login.php">Login here</a>
                        </p>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="hero">
                <div class="container">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['temp_user_name']); ?>!</h2>
                    <p>Let's personalize your experience by selecting your food preferences</p>
                </div>
            </section>

            <section class="features">
                <div class="container">
                    <div class="preferences-form">
                        <h3>Select Your Food Preferences</h3>
                        <p>Choose the types of food you enjoy. You can always update these later in your profile.</p>
                        
                        <form action="preferences.php" method="POST">
                            <div class="preferences-grid">
                                <div class="preference-item">
                                    <input type="checkbox" id="main-dishes" name="preferences[]" value="main-dishes">
                                    <label for="main-dishes">Main Dishes</label>
                                </div>
                                <div class="preference-item">
                                    <input type="checkbox" id="snacks-street-food" name="preferences[]" value="snacks-street-food">
                                    <label for="snacks-street-food">Snacks & Street Food</label>
                                </div>
                                <div class="preference-item">
                                    <input type="checkbox" id="desserts" name="preferences[]" value="desserts">
                                    <label for="desserts">Desserts</label>
                                </div>
                                <div class="preference-item">
                                    <input type="checkbox" id="traditional-food" name="preferences[]" value="traditional-food">
                                    <label for="traditional-food">Traditional Food</label>
                                </div>
                                <div class="preference-item">
                                    <input type="checkbox" id="vegetarian" name="preferences[]" value="vegetarian">
                                    <label for="vegetarian">Vegetarian</label>
                                </div>
                                <div class="preference-item">
                                    <input type="checkbox" id="vegan" name="preferences[]" value="vegan">
                                    <label for="vegan">Vegan</label>
                                </div>
                                <div class="preference-item">
                                    <input type="checkbox" id="gluten-free" name="preferences[]" value="gluten-free">
                                    <label for="gluten-free">Gluten-Free</label>
                                </div>
                                <div class="preference-item">
                                    <input type="checkbox" id="spicy" name="preferences[]" value="spicy">
                                    <label for="spicy">Spicy Food</label>
                                </div>
                            </div>
                            
                            <div class="form-group" style="text-align: center;">
                                <button type="submit" class="cta-button">Continue to RecipeCraft</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        <?php endif; ?>
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
