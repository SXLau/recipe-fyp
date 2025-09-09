<?php
require_once '../session.php';
require_once '../db.php';
require_admin();

// Get most rated recipes
$most_rated_query = "
    SELECT r.id, r.title, r.image, c.name as category_name,
           COUNT(rt.id) as rating_count,
           COALESCE(AVG(rt.rating), 0) as avg_rating
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    GROUP BY r.id
    HAVING rating_count > 0
    ORDER BY rating_count DESC, avg_rating DESC
    LIMIT 10
";
$most_rated_result = $conn->query($most_rated_query);

// Get rating distribution
$rating_distribution_query = "
    SELECT rating, COUNT(*) as count
    FROM ratings
    GROUP BY rating
    ORDER BY rating
";
$rating_distribution_result = $conn->query($rating_distribution_query);

// Get most popular categories
$popular_categories_query = "
    SELECT c.name, c.slug,
           COUNT(r.id) as recipe_count,
           COUNT(rt.id) as rating_count,
           COALESCE(AVG(rt.rating), 0) as avg_rating
    FROM categories c
    LEFT JOIN recipes r ON c.id = r.category_id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    GROUP BY c.id
    ORDER BY recipe_count DESC, rating_count DESC
";
$popular_categories_result = $conn->query($popular_categories_query);

// Get overall statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM recipes) as total_recipes,
        (SELECT COUNT(*) FROM ratings) as total_ratings,
        (SELECT COALESCE(AVG(rating), 0) FROM ratings) as overall_avg_rating,
        (SELECT COUNT(*) FROM categories) as total_categories
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get recent activity
$recent_activity_query = "
    SELECT 'rating' as type, rt.created_at, u.name as user_name, r.title as recipe_title, rt.rating
    FROM ratings rt
    JOIN users u ON rt.user_id = u.id
    JOIN recipes r ON rt.recipe_id = r.id
    ORDER BY rt.created_at DESC
    LIMIT 20
";
$recent_activity_result = $conn->query($recent_activity_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - RecipeCraft Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <h1>RecipeAdmin</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i><span>Users</span></a></li>
                    <li><a href="recipes.php"><i class="fas fa-book"></i><span>Recipes</span></a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i><span>Categories</span></a></li>
                    <li class="active"><a href="analytics.php"><i class="fas fa-chart-bar"></i><span>Analytics</span></a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-bar">
                    <input type="text" placeholder="Search analytics..." id="analytics-search">
                    <button><i class="fas fa-search"></i></button>
                </div>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="content-section active">
                <div class="section-header">
                    <h2><i class="fas fa-chart-bar"></i> Analytics Dashboard</h2>
                </div>

                <!-- Overview Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Users</h3>
                            <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Recipes</h3>
                            <div class="stat-value"><?php echo number_format($stats['total_recipes']); ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Ratings</h3>
                            <div class="stat-value"><?php echo number_format($stats['total_ratings']); ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Avg Rating</h3>
                            <div class="stat-value"><?php echo number_format($stats['overall_avg_rating'], 1); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="dashboard-charts">
                    <!-- Rating Distribution Chart -->
                    <div class="chart-card">
                        <h3>Rating Distribution</h3>
                        <div class="chart-container">
                            <canvas id="ratingChart"></canvas>
                        </div>
                    </div>

                    <!-- Popular Categories Chart -->
                    <div class="chart-card">
                        <h3>Most Popular Categories</h3>
                        <div class="chart-container">
                            <canvas id="categoriesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Most Rated Recipes -->
                <div class="analytics-section">
                    <h3><i class="fas fa-trophy"></i> Most Rated Recipes</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Recipe</th>
                                    <th>Category</th>
                                    <th>Ratings</th>
                                    <th>Avg Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                while ($recipe = $most_rated_result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td>
                                            <span class="rank-badge rank-<?php echo $rank <= 3 ? $rank : 'other'; ?>">
                                                <?php echo $rank; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="recipe-info">
                                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="recipe-thumb">
                                                <div class="recipe-details">
                                                    <strong><?php echo htmlspecialchars($recipe['title']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="category-badge"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                                        </td>
                                        <td>
                                            <span class="rating-count"><?php echo $recipe['rating_count']; ?></span>
                                        </td>
                                        <td>
                                            <div class="rating-display">
                                                <span class="rating-score"><?php echo number_format($recipe['avg_rating'], 1); ?></span>
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
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                $rank++;
                                endwhile; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Popular Categories -->
                <div class="analytics-section">
                    <h3><i class="fas fa-tags"></i> Category Performance</h3>
                    <div class="categories-performance">
                        <?php while ($category = $popular_categories_result->fetch_assoc()): ?>
                            <div class="category-performance-card">
                                <div class="category-header">
                                    <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                                    <span class="category-slug"><?php echo htmlspecialchars($category['slug']); ?></span>
                                </div>
                                <div class="category-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $category['recipe_count']; ?></span>
                                        <span class="stat-label">Recipes</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $category['rating_count']; ?></span>
                                        <span class="stat-label">Ratings</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo number_format($category['avg_rating'], 1); ?></span>
                                        <span class="stat-label">Avg Rating</span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="analytics-section">
                    <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Recipe</th>
                                    <th>Rating</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($activity = $recent_activity_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="activity-type">
                                                <i class="fas fa-star"></i> Rating
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['recipe_title']); ?></td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $activity['rating']) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('M j, Y H:i', strtotime($activity['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Rating Distribution Chart
        const ratingData = <?php 
            $rating_data = [];
            while ($row = $rating_distribution_result->fetch_assoc()) {
                $rating_data[] = ['rating' => $row['rating'], 'count' => $row['count']];
            }
            echo json_encode($rating_data);
        ?>;

        const ratingCtx = document.getElementById('ratingChart').getContext('2d');
        new Chart(ratingCtx, {
            type: 'bar',
            data: {
                labels: ratingData.map(item => item.rating + ' Star' + (item.rating > 1 ? 's' : '')),
                datasets: [{
                    label: 'Number of Ratings',
                    data: ratingData.map(item => item.count),
                    backgroundColor: [
                        '#ff6b6b',
                        '#ffa726',
                        '#ffeb3b',
                        '#66bb6a',
                        '#42a5f5'
                    ],
                    borderColor: [
                        '#ff5252',
                        '#ff9800',
                        '#fbc02d',
                        '#4caf50',
                        '#2196f3'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Categories Chart
        const categoriesData = <?php 
            $categories_data = [];
            $popular_categories_result->data_seek(0); // Reset result pointer
            while ($row = $popular_categories_result->fetch_assoc()) {
                $categories_data[] = ['name' => $row['name'], 'recipes' => $row['recipe_count']];
            }
            echo json_encode($categories_data);
        ?>;

        const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
        new Chart(categoriesCtx, {
            type: 'doughnut',
            data: {
                labels: categoriesData.map(item => item.name),
                datasets: [{
                    data: categoriesData.map(item => item.recipes),
                    backgroundColor: [
                        '#ff6b6b',
                        '#4ecdc4',
                        '#45b7d1',
                        '#96ceb4',
                        '#feca57',
                        '#ff9ff3',
                        '#54a0ff',
                        '#5f27cd'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

</body>
</html>
