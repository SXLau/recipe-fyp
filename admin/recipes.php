<?php
require_once '../session.php';
require_once '../db.php';
require_admin();

$message = '';
$error = '';

// Handle recipe actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $recipe_id = (int)($_POST['recipe_id'] ?? 0);
    
    switch ($action) {
        case 'delete':
            if ($recipe_id) {
                // Delete associated ratings first
                $stmt = $conn->prepare("DELETE FROM ratings WHERE recipe_id = ?");
                $stmt->bind_param("i", $recipe_id);
                $stmt->execute();
                
                // Delete recipe
                $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
                $stmt->bind_param("i", $recipe_id);
                if ($stmt->execute()) {
                    $message = "Recipe deleted successfully!";
                } else {
                    $error = "Failed to delete recipe.";
                }
            }
            break;
            
        case 'add':
        case 'edit':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $ingredients = trim($_POST['ingredients'] ?? '');
            $steps = trim($_POST['steps'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $prep_time = trim($_POST['prep_time'] ?? '');
            $difficulty = trim($_POST['difficulty'] ?? '');
            $servings = trim($_POST['servings'] ?? '');
            $image = trim($_POST['image'] ?? '');
            
            if (empty($title) || empty($ingredients) || empty($steps) || !$category_id) {
                $error = "Please fill in all required fields.";
            } else {
                if ($action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO recipes (title, description, ingredients, steps, category_id, prep_time, difficulty, servings, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssissss", $title, $description, $ingredients, $steps, $category_id, $prep_time, $difficulty, $servings, $image);
                } else {
                    $stmt = $conn->prepare("UPDATE recipes SET title=?, description=?, ingredients=?, steps=?, category_id=?, prep_time=?, difficulty=?, servings=?, image=? WHERE id=?");
                    $stmt->bind_param("ssssissssi", $title, $description, $ingredients, $steps, $category_id, $prep_time, $difficulty, $servings, $image, $recipe_id);
                }
                
                if ($stmt->execute()) {
                    $message = $action === 'add' ? "Recipe added successfully!" : "Recipe updated successfully!";
                } else {
                    $error = "Failed to save recipe.";
                }
            }
            break;
    }
}

// Get all recipes with category and rating info
$recipes_query = "
    SELECT r.*, c.name as category_name,
           COALESCE(AVG(rt.rating), 0) as avg_rating,
           COUNT(rt.id) as rating_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    GROUP BY r.id
    ORDER BY r.created_at DESC
";

$recipes_result = $conn->query($recipes_query);

// Get categories for dropdown
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Management - RecipeCraft Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                    <li class="active"><a href="recipes.php"><i class="fas fa-book"></i><span>Recipes</span></a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i><span>Categories</span></a></li>
                    <li><a href="analytics.php"><i class="fas fa-chart-bar"></i><span>Analytics</span></a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-bar">
                    <input type="text" placeholder="Search recipes..." id="recipe-search">
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
                    <h2><i class="fas fa-book"></i> Recipe Management</h2>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Recipe
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Recipes Table -->
                <div class="table-container">
                    <table id="recipes-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Rating</th>
                                <th>Prep Time</th>
                                <th>Difficulty</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $recipe['id']; ?></td>
                                    <td>
                                        <div class="recipe-image">
                                            <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="recipe-title">
                                            <strong><?php echo htmlspecialchars($recipe['title']); ?></strong>
                                            <?php if ($recipe['description']): ?>
                                                <div class="recipe-description"><?php echo htmlspecialchars(substr($recipe['description'], 0, 100)) . (strlen($recipe['description']) > 100 ? '...' : ''); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
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
                                            <div class="rating-count">(<?php echo $recipe['rating_count']; ?>)</div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($recipe['prep_time']); ?></td>
                                    <td>
                                        <span class="difficulty-badge difficulty-<?php echo strtolower($recipe['difficulty']); ?>">
                                            <?php echo htmlspecialchars($recipe['difficulty']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-info btn-sm" onclick="viewRecipe(<?php echo $recipe['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-secondary btn-sm" onclick="editRecipe(<?php echo $recipe['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteRecipe(<?php echo $recipe['id']; ?>, '<?php echo htmlspecialchars($recipe['title']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Recipe Modal -->
    <div id="recipe-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Add Recipe</h3>
                <span class="close-modal">&times;</span>
            </div>
            <form id="recipe-form" method="POST">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="recipe_id" id="recipe-id" value="">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prep_time">Prep Time</label>
                            <input type="text" id="prep_time" name="prep_time" placeholder="e.g., 30 minutes">
                        </div>
                        <div class="form-group">
                            <label for="difficulty">Difficulty</label>
                            <select id="difficulty" name="difficulty">
                                <option value="">Select Difficulty</option>
                                <option value="Easy">Easy</option>
                                <option value="Medium">Medium</option>
                                <option value="Hard">Hard</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="servings">Servings</label>
                            <input type="text" id="servings" name="servings" placeholder="e.g., 4 people">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image URL</label>
                        <input type="url" id="image" name="image" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="form-group">
                        <label for="ingredients">Ingredients * (one per line)</label>
                        <textarea id="ingredients" name="ingredients" rows="6" required placeholder="2 cups flour&#10;1 cup sugar&#10;3 eggs"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="steps">Instructions * (one step per line)</label>
                        <textarea id="steps" name="steps" rows="8" required placeholder="1. Preheat oven to 350°F&#10;2. Mix dry ingredients&#10;3. Add wet ingredients"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('recipe-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Recipe</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Recipe Modal -->
    <div id="view-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Recipe Details</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="view-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete recipe <strong id="delete-recipe-name"></strong>?</p>
                <p class="warning-text">This action cannot be undone and will delete all associated ratings.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('delete-modal')">Cancel</button>
                <button class="btn btn-danger" id="confirm-delete">Delete Recipe</button>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('recipe-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#recipes-table tbody tr');
            
            tableRows.forEach(row => {
                const title = row.cells[2].textContent.toLowerCase();
                const category = row.cells[3].textContent.toLowerCase();
                
                if (title.includes(searchTerm) || category.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Open add modal
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Add Recipe';
            document.getElementById('form-action').value = 'add';
            document.getElementById('recipe-id').value = '';
            document.getElementById('recipe-form').reset();
            document.getElementById('recipe-modal').style.display = 'flex';
        }

        // Edit recipe
        function editRecipe(recipeId) {
            fetch(`get_recipe.php?id=${recipeId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-title').textContent = 'Edit Recipe';
                    document.getElementById('form-action').value = 'edit';
                    document.getElementById('recipe-id').value = recipeId;
                    document.getElementById('title').value = data.title;
                    document.getElementById('description').value = data.description;
                    document.getElementById('category_id').value = data.category_id;
                    document.getElementById('prep_time').value = data.prep_time;
                    document.getElementById('difficulty').value = data.difficulty;
                    document.getElementById('servings').value = data.servings;
                    document.getElementById('image').value = data.image;
                    document.getElementById('ingredients').value = data.ingredients;
                    document.getElementById('steps').value = data.steps;
                    document.getElementById('recipe-modal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load recipe data');
                });
        }

        // View recipe
        function viewRecipe(recipeId) {
            fetch(`get_recipe.php?id=${recipeId}&view=true`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('view-content').innerHTML = data;
                    document.getElementById('view-modal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load recipe details');
                });
        }

        // Delete recipe
        function deleteRecipe(recipeId, recipeName) {
            document.getElementById('delete-recipe-name').textContent = recipeName;
            document.getElementById('confirm-delete').onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="recipe_id" value="${recipeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            };
            document.getElementById('delete-modal').style.display = 'flex';
        }

        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Close modal with X button
        document.querySelectorAll('.close-modal').forEach(button => {
            button.onclick = function() {
                this.closest('.modal').style.display = 'none';
            };
        });
    </script>

</body>
</html>
