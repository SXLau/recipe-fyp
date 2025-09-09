# RecipeCraft - Food Recipe Recommendation System

A full-stack PHP-based food recipe recommendation system with user authentication, personalized recommendations, and admin management.

## 🚀 Features

### User Features
- **User Authentication**: Secure registration and login system
- **Preference Selection**: Choose food categories and dietary preferences
- **Personalized Recommendations**: Get recipes based on your preferences
- **Recipe Browsing**: Explore recipes by category or view all recipes
- **Rating System**: Rate recipes (1-5 stars) and see community ratings
- **Top Rated Recipes**: Discover the most loved recipes
- **Recipe Details**: View ingredients, instructions, and related recipes
- **Profile Management**: Update preferences and view activity

### Admin Features
- **Dashboard**: Overview of system statistics
- **User Management**: View and manage user accounts
- **Recipe Management**: Add, edit, and delete recipes
- **Category Management**: Manage recipe categories
- **Analytics**: View rating distributions and popular categories

### Recommendation Systems
1. **Preference-based**: Recipes matching user's selected food categories
2. **Ingredient-based**: Related recipes from the same category
3. **Rating-based**: Top-rated recipes sorted by community ratings

## 🛠️ Technical Stack

- **Backend**: PHP 8+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Database**: MySQL with prepared statements
- **Security**: Password hashing, SQL injection prevention, session management
- **Server**: XAMPP (Apache, PHP, MySQL)

## 📋 System Requirements

- XAMPP (Apache, PHP 8+, MySQL)
- Web browser with JavaScript enabled
- Minimum 512MB RAM
- 100MB disk space

## 🚀 Installation

### 1. Setup XAMPP
1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Ensure PHP version is 8.0 or higher

### 2. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `food_recipes`
3. Import the `database_schema.sql` file
4. Verify all tables are created successfully

### 3. Project Setup
1. Clone or download this project to `htdocs` folder
2. Navigate to project directory: `C:\xampp\htdocs\recipe_fyp\`
3. Ensure all PHP files are in place
4. Verify CSS and JS folders contain styling files

### 4. Configuration
1. Check database connection in `db.php`
2. Default credentials:
   - Host: localhost
   - User: root
   - Password: (empty)
   - Database: food_recipes

## 🔐 Default Accounts

### Admin Account
- **Email**: admin@recipecraft.com
- **Password**: admin123
- **Access**: http://localhost/recipe_fyp/admin/login.php

### User Registration
- Users can register at: http://localhost/recipe_fyp/register.php
- After registration, users must select food preferences
- Users are then redirected to the main recipe hub

## 📁 File Structure

```
recipe_fyp/
├── admin/                  # Admin dashboard
│   ├── index.php          # Admin home
│   ├── login.php          # Admin login
│   └── logout.php         # Admin logout
├── css/                   # Stylesheets (unchanged)
├── js/                    # JavaScript files (unchanged)
├── db.php                 # Database connection
├── session.php            # Session management
├── index.php              # Landing page
├── register.php           # User registration
├── login.php              # User login
├── logout.php             # User logout
├── preferences.php        # Preference selection
├── main.php               # Main recipes page
├── personalized.php       # Personalized recommendations
├── categories.php         # Category browsing
├── top_rated.php          # Top rated recipes
├── recipe.php             # Individual recipe page
├── profile.php            # User profile
├── database_schema.sql    # Database structure
└── README.md              # This file
```

## 🗄️ Database Schema

### Tables
- **users**: User accounts and preferences
- **categories**: Recipe categories
- **recipes**: Recipe information
- **ratings**: User ratings for recipes

### Key Features
- Foreign key relationships
- JSON storage for user preferences
- Timestamp tracking
- Unique constraints for ratings

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Management**: Secure PHP sessions
- **Input Validation**: Server-side validation for all forms
- **XSS Prevention**: HTML escaping for user input

## 🎯 Recommendation Algorithms

### 1. Preference-based Recommendation
- Matches user's selected food categories
- Updates dynamically when preferences change
- Highlights matching recipes with preference badges

### 2. Ingredient-based Recommendation
- Shows recipes from the same category
- Displays related recipes on individual recipe pages
- Sorted by rating for better relevance

### 3. Rating-based Recommendation
- Calculates average ratings from all users
- Sorts recipes by rating and rating count
- Provides category filtering for rankings

## 🎨 UI/UX Features

- **Responsive Design**: Works on desktop and mobile
- **Modern Interface**: Clean, intuitive design
- **Interactive Elements**: Hover effects, smooth transitions
- **Visual Feedback**: Loading states, success/error messages
- **Accessibility**: Clear navigation, readable fonts

## 📱 User Journey

1. **Landing Page**: Learn about RecipeCraft
2. **Registration**: Create account with email/password
3. **Preference Selection**: Choose food categories
4. **Main Hub**: Browse all recipes with search/filter
5. **Personalized**: View preference-matched recipes
6. **Recipe Details**: View ingredients, instructions, rate
7. **Profile**: Update preferences, view activity

## 🚀 Getting Started

1. **Start XAMPP**: Ensure Apache and MySQL are running
2. **Access System**: Navigate to http://localhost/recipe_fyp/
3. **Admin Access**: Use admin credentials to access dashboard
4. **User Testing**: Register new accounts and test features

## 🔧 Troubleshooting

### Common Issues
1. **Database Connection Error**: Check XAMPP services and database credentials
2. **Page Not Found**: Verify file paths and Apache configuration
3. **Session Issues**: Check PHP session configuration
4. **Permission Errors**: Ensure proper file permissions

### Debug Mode
- Check XAMPP error logs
- Verify PHP error reporting settings
- Test database queries in phpMyAdmin

## 📈 Future Enhancements

- **Advanced Search**: Ingredient-based search
- **Social Features**: Recipe sharing, comments
- **Mobile App**: Native mobile application
- **AI Recommendations**: Machine learning algorithms
- **Recipe Import**: Bulk recipe import functionality
- **Analytics Dashboard**: Advanced user behavior insights

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is created for educational purposes as a Final Year Project.

**RecipeCraft** - Discover recipes tailored to your taste! 🍽️✨
