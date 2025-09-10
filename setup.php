<?php
/**
 * RecipeCraft Database Setup Script
 * Run this script to set up the database and create an admin user
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'food_recipes');

echo "<h1>RecipeCraft Database Setup</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .step { margin: 20px 0; padding: 15px; border-radius: 5px; }
    .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    .btn:hover { background: #0056b3; }
    .credentials { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f1f1f1; padding: 10px; border-radius: 3px; font-family: monospace; }
</style>";

echo "<div class='container'>";

// Step 1: Check if database exists
echo "<div class='step info'>";
echo "<h2>Step 1: Checking Database Connection</h2>";

try {
    // First, connect without specifying database to check if we can connect to MySQL
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Successfully connected to MySQL server</p>";
    
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($result->num_rows > 0) {
        echo "<p>⚠️ Database '" . DB_NAME . "' already exists</p>";
        echo "<p>Do you want to recreate it? This will delete all existing data!</p>";
        echo "<a href='?recreate=1' class='btn' onclick='return confirm(\"Are you sure? This will delete all data!\")'>Recreate Database</a>";
        echo "<a href='?continue=1' class='btn'>Continue with Existing Database</a>";
        
        if (isset($_GET['continue'])) {
            $conn->select_db(DB_NAME);
            echo "<p>✅ Using existing database</p>";
        } elseif (isset($_GET['recreate'])) {
            $conn->query("DROP DATABASE IF EXISTS " . DB_NAME);
            $conn->query("CREATE DATABASE " . DB_NAME);
            $conn->select_db(DB_NAME);
            echo "<p>✅ Database recreated successfully</p>";
        } else {
            echo "</div></div>";
            exit;
        }
    } else {
        // Create database
        if ($conn->query("CREATE DATABASE " . DB_NAME)) {
            echo "<p>✅ Database '" . DB_NAME . "' created successfully</p>";
            $conn->select_db(DB_NAME);
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>";
    echo "<h2>❌ Database Connection Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please check your XAMPP configuration:</p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running</li>";
    echo "<li>Check that MySQL service is started</li>";
    echo "<li>Verify database credentials in setup.php</li>";
    echo "</ul>";
    echo "</div></div>";
    exit;
}

echo "</div>";

// Step 2: Create tables
echo "<div class='step info'>";
echo "<h2>Step 2: Creating Database Tables</h2>";

$tables_created = 0;
$tables_total = 4;

// Users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    preferences TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "<p>✅ Users table created</p>";
    $tables_created++;
} else {
    echo "<p>❌ Error creating users table: " . $conn->error . "</p>";
}

// Categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "<p>✅ Categories table created</p>";
    $tables_created++;
} else {
    echo "<p>❌ Error creating categories table: " . $conn->error . "</p>";
}

// Recipes table
$sql = "CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    ingredients TEXT NOT NULL,
    steps TEXT NOT NULL,
    category_id INT NOT NULL,
    image VARCHAR(500),
    prep_time VARCHAR(50),
    difficulty ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Medium',
    servings INT DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "<p>✅ Recipes table created</p>";
    $tables_created++;
} else {
    echo "<p>❌ Error creating recipes table: " . $conn->error . "</p>";
}

// Ratings table
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_recipe (user_id, recipe_id)
)";

if ($conn->query($sql)) {
    echo "<p>✅ Ratings table created</p>";
    $tables_created++;
} else {
    echo "<p>❌ Error creating ratings table: " . $conn->error . "</p>";
}

if ($tables_created === $tables_total) {
    echo "<p><strong>✅ All tables created successfully!</strong></p>";
} else {
    echo "<p><strong>⚠️ Only $tables_created out of $tables_total tables created</strong></p>";
}

echo "</div>";

// Step 3: Insert default data
echo "<div class='step info'>";
echo "<h2>Step 3: Inserting Default Data</h2>";

// Insert default categories
$categories = [
    ['Main Dishes', 'main-dishes', 'Primary meal courses including meat, fish, and vegetarian options'],
    ['Snacks & Street Food', 'snacks-street-food', 'Quick bites and street food favorites'],
    ['Desserts', 'desserts', 'Sweet treats and dessert recipes'],
    ['Traditional Food', 'traditional-food', 'Cultural and traditional recipes from around the world']
];

$categories_inserted = 0;
foreach ($categories as $category) {
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $category[0], $category[1], $category[2]);
    if ($stmt->execute()) {
        $categories_inserted++;
    }
}

echo "<p>✅ Inserted $categories_inserted default categories</p>";

// Insert sample recipes
$recipes = [
    [
        'Classic Beef Burger',
        'A juicy beef burger with fresh vegetables and special sauce',
        "Beef patty\nBurger bun\nLettuce\nTomato\nOnion\nCheese\nKetchup\nMustard",
        "1. Form beef into patty\n2. Grill patty for 5-7 minutes\n3. Toast bun\n4. Assemble with vegetables and sauce",
        1,
        'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
        '20 mins',
        'Easy',
        4
    ],
    [
        'Chocolate Chip Cookies',
        'Soft and chewy chocolate chip cookies',
        "Flour\nButter\nSugar\nEggs\nVanilla\nChocolate chips\nBaking soda\nSalt",
        "1. Cream butter and sugar\n2. Add eggs and vanilla\n3. Mix in dry ingredients\n4. Add chocolate chips\n5. Bake at 350°F for 10-12 minutes",
        3,
        'https://images.unsplash.com/photo-1499636136210-6d4c9e2aef6a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
        '25 mins',
        'Easy',
        12
    ],
    [
        'Chicken Stir Fry',
        'Quick and healthy chicken stir fry with vegetables',
        "Chicken breast\nBroccoli\nCarrots\nSoy sauce\nGarlic\nGinger\nOil",
        "1. Cut chicken into pieces\n2. Stir fry chicken until golden\n3. Add vegetables\n4. Season with soy sauce and spices",
        1,
        'https://images.unsplash.com/photo-1603133872878-684f208fb84b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
        '30 mins',
        'Medium',
        4
    ],
    [
        'Pizza Margherita',
        'Traditional Italian pizza with tomato and mozzarella',
        "Pizza dough\nTomato sauce\nMozzarella\nBasil\nOlive oil\nSalt",
        "1. Roll out dough\n2. Add tomato sauce\n3. Top with mozzarella\n4. Bake at 450°F for 15 minutes\n5. Garnish with basil",
        1,
        'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
        '45 mins',
        'Medium',
        4
    ],
    [
        'Fruit Smoothie Bowl',
        'Healthy and colorful smoothie bowl with fresh fruits',
        "Banana\nBerries\nYogurt\nGranola\nHoney\nCoconut flakes",
        "1. Blend fruits with yogurt\n2. Pour into bowl\n3. Top with granola and coconut\n4. Drizzle with honey",
        2,
        'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
        '10 mins',
        'Easy',
        2
    ]
];

$recipes_inserted = 0;
foreach ($recipes as $recipe) {
    $stmt = $conn->prepare("INSERT IGNORE INTO recipes (title, description, ingredients, steps, category_id, image, prep_time, difficulty, servings) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisssi", $recipe[0], $recipe[1], $recipe[2], $recipe[3], $recipe[4], $recipe[5], $recipe[6], $recipe[7], $recipe[8]);
    if ($stmt->execute()) {
        $recipes_inserted++;
    }
}

echo "<p>✅ Inserted $recipes_inserted sample recipes</p>";

echo "</div>";

// Step 4: Create admin user
echo "<div class='step info'>";
echo "<h2>Step 4: Creating Admin User</h2>";

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin'");
$admin_email = 'admin@recipecraft.com';
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p>⚠️ Admin user already exists</p>";
    echo "<p>Do you want to update the admin password?</p>";
    echo "<a href='?update_admin=1' class='btn'>Update Admin Password</a>";
    
    if (isset($_GET['update_admin'])) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
        $stmt->bind_param("ss", $hashed_password, $admin_email);
        if ($stmt->execute()) {
            echo "<p>✅ Admin password updated successfully</p>";
        } else {
            echo "<p>❌ Error updating admin password: " . $conn->error . "</p>";
        }
    }
} else {
    // Create admin user
    $admin_name = 'Admin User';
    $admin_password = 'admin123';
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $admin_name, $admin_email, $hashed_password);
    
    if ($stmt->execute()) {
        echo "<p>✅ Admin user created successfully</p>";
    } else {
        echo "<p>❌ Error creating admin user: " . $conn->error . "</p>";
    }
}

echo "</div>";

// Step 5: Setup complete
echo "<div class='step success'>";
echo "<h2>🎉 Setup Complete!</h2>";
echo "<p>Your RecipeCraft database has been set up successfully!</p>";

echo "<div class='credentials'>";
echo "<h3>Admin Login Credentials:</h3>";
echo "<p><strong>Email:</strong> admin@recipecraft.com</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "<p><strong>Admin URL:</strong> <a href='admin/login.php'>admin/login.php</a></p>";
echo "</div>";

echo "<div class='credentials'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Delete this setup.php file for security</li>";
echo "<li>Access your application at: <a href='index.php'>index.php</a></li>";
echo "<li>Register new user accounts to test the system</li>";
echo "<li>Login as admin to manage the system</li>";
echo "</ol>";
echo "</div>";

echo "<h3>Quick Links:</h3>";
echo "<a href='index.php' class='btn'>🏠 Go to Homepage</a>";
echo "<a href='admin/login.php' class='btn'>👨‍💼 Admin Login</a>";
echo "<a href='register.php' class='btn'>👤 User Registration</a>";

echo "</div>";

echo "</div>";

$conn->close();
?>
