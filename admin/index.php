<?php
require_once '../session.php';
require_once '../db.php';

// Require admin access
require_admin();

// Get dashboard statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['users'] = $result->fetch_assoc()['count'];

// Total recipes
$result = $conn->query("SELECT COUNT(*) as count FROM recipes");
$stats['recipes'] = $result->fetch_assoc()['count'];

// Total ratings
$result = $conn->query("SELECT COUNT(*) as count FROM ratings");
$stats['ratings'] = $result->fetch_assoc()['count'];

// Average rating
$result = $conn->query("SELECT AVG(rating) as avg FROM ratings");
$stats['avg_rating'] = number_format($result->fetch_assoc()['avg'], 1);

// Top rated recipe
$result = $conn->query("
    SELECT r.title, c.name as category, AVG(rt.rating) as avg_rating, COUNT(rt.id) as rating_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    GROUP BY r.id
    HAVING avg_rating > 0
    ORDER BY avg_rating DESC, rating_count DESC
    LIMIT 1
");
$top_recipe = $result->fetch_assoc();

// Recent activity
$recent_activity_query = "
    SELECT u.name as user_name, r.title as recipe_title, rt.created_at
    FROM ratings rt
    JOIN users u ON rt.user_id = u.id
    JOIN recipes r ON rt.recipe_id = r.id
    ORDER BY rt.created_at DESC
    LIMIT 10
";
$recent_activity = $conn->query($recent_activity_query);

// Category distribution
$category_stats_query = "
    SELECT c.name, COUNT(r.id) as recipe_count
    FROM categories c
    LEFT JOIN recipes r ON c.id = r.category_id
    GROUP BY c.id
    ORDER BY recipe_count DESC
";
$category_stats = $conn->query($category_stats_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RecipeCraft</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <h1>RecipeAdmin</h1>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="recipes.php"><i class="fas fa-book"></i> Recipes</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="top-bar">
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <!-- Dashboard Overview -->
            <section class="content-section active">
                <h2>Dashboard Overview</h2>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Recipes</h3>
                            <p class="stat-value"><?php echo $stats['recipes']; ?></p>
                            <p class="stat-change">Active recipes</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Users</h3>
                            <p class="stat-value"><?php echo $stats['users']; ?></p>
                            <p class="stat-change">Registered users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Ratings</h3>
                            <p class="stat-value"><?php echo $stats['ratings']; ?></p>
                            <p class="stat-change">User ratings</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Avg. Rating</h3>
                            <p class="stat-value"><?php echo $stats['avg_rating']; ?></p>
                            <p class="stat-change">Overall rating</p>
                        </div>
                    </div>
                </div>

                <!-- Top Recipe Highlight -->
                <?php if ($top_recipe): ?>
                <div class="top-recipe-highlight">
                    <h3>Top Rated Recipe</h3>
                    <div class="top-recipe-card">
                        <div class="top-recipe-info">
                            <h4><?php echo htmlspecialchars($top_recipe['title']); ?></h4>
                            <p class="category"><?php echo htmlspecialchars($top_recipe['category']); ?></p>
                            <div class="rating">
                                <span class="rating-score"><?php echo number_format($top_recipe['avg_rating'], 1); ?></span>
                                <span class="rating-stars">
                                    <?php
                                    $rating = round($top_recipe['avg_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </span>
                                <span class="rating-count">(<?php echo $top_recipe['rating_count']; ?> ratings)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Dashboard Charts and Tables -->
                <div class="dashboard-charts">
                    <!-- Category Distribution Chart -->
                    <div class="chart-card">
                        <h3>Recipes by Category</h3>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Activity Table -->
                    <div class="chart-card">
                        <h3>Recent Activity</h3>
                        <table class="recent-activity">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Recipe</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['recipe_title']); ?></td>
                                    <td><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Category Distribution Chart
        const categoryData = {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        };

        <?php
        $category_stats->data_seek(0);
        while ($cat = $category_stats->fetch_assoc()) {
            echo "categoryData.labels.push('" . addslashes($cat['name']) . "');";
            echo "categoryData.datasets[0].data.push(" . $cat['recipe_count'] . ");";
        }
        ?>

        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: categoryData,
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
