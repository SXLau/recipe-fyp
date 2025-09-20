-- Database: food_recipes
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS food_recipes;
USE food_recipes;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    preferences TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recipes table
CREATE TABLE recipes (
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
);

-- Ratings table
CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_recipe (user_id, recipe_id)
);

-- Insert default categories
INSERT INTO categories (name, slug, description) VALUES
('Main Dishes', 'main-dishes', 'Primary meal courses including meat, fish, and vegetarian options'),
('Snacks & Street Food', 'snacks-street-food', 'Quick bites and street food favorites'),
('Desserts', 'desserts', 'Sweet treats and dessert recipes'),
('Traditional Food', 'traditional-food', 'Cultural and traditional recipes from around the world');

-- Insert sample admin user (password: admin123)

INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@recipecraft.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin');

-- Insert sample recipes
INSERT INTO recipes (title, description, ingredients, steps, category_id, image, prep_time) VALUES
('Classic Beef Burger', 'A juicy beef burger with fresh vegetables and special sauce', 'Beef patty, burger bun, lettuce, tomato, onion, cheese, ketchup, mustard', '1. Form beef into patty\n2. Grill patty for 5-7 minutes\n3. Toast bun\n4. Assemble with vegetables and sauce', 1, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', '20 mins'),
('Chocolate Chip Cookies', 'Soft and chewy chocolate chip cookies', 'Flour, butter, sugar, eggs, vanilla, chocolate chips, baking soda, salt', '1. Cream butter and sugar\n2. Add eggs and vanilla\n3. Mix in dry ingredients\n4. Add chocolate chips\n5. Bake at 350°F for 10-12 minutes', 3, 'https://images.unsplash.com/photo-1499636136210-6d4c9e2aef6a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', '25 mins'),
('Chicken Stir Fry', 'Quick and healthy chicken stir fry with vegetables', 'Chicken breast, broccoli, carrots, soy sauce, garlic, ginger, oil', '1. Cut chicken into pieces\n2. Stir fry chicken until golden\n3. Add vegetables\n4. Season with soy sauce and spices', 1, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', '30 mins'),
('Pizza Margherita', 'Traditional Italian pizza with tomato and mozzarella', 'Pizza dough, tomato sauce, mozzarella, basil, olive oil, salt', '1. Roll out dough\n2. Add tomato sauce\n3. Top with mozzarella\n4. Bake at 450°F for 15 minutes\n5. Garnish with basil', 1, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', '45 mins'),
('Fruit Smoothie Bowl', 'Healthy and colorful smoothie bowl with fresh fruits', 'Banana, berries, yogurt, granola, honey, coconut flakes', '1. Blend fruits with yogurt\n2. Pour into bowl\n3. Top with granola and coconut\n4. Drizzle with honey', 2, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80', '10 mins');
