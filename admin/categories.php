<?php
require_once '../session.php';
require_once '../db.php';
require_admin();

$message = '';
$error = '';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name) || empty($slug)) {
                $error = "Name and slug are required.";
            } else {
                // Check if slug already exists
                $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
                $stmt->bind_param("s", $slug);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $error = "Slug already exists. Please choose a different one.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $name, $slug, $description);
                    if ($stmt->execute()) {
                        $message = "Category added successfully!";
                    } else {
                        $error = "Failed to add category.";
                    }
                }
            }
            break;
            
        case 'edit':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (!$id || empty($name) || empty($slug)) {
                $error = "Invalid data provided.";
            } else {
                // Check if slug already exists (excluding current category)
                $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
                $stmt->bind_param("si", $slug, $id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $error = "Slug already exists. Please choose a different one.";
                } else {
                    $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?");
                    $stmt->bind_param("sssi", $name, $slug, $description, $id);
                    if ($stmt->execute()) {
                        $message = "Category updated successfully!";
                    } else {
                        $error = "Failed to update category.";
                    }
                }
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                // Check if category has recipes
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM recipes WHERE category_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                
                if ($result['count'] > 0) {
                    $error = "Cannot delete category with existing recipes. Please move or delete the recipes first.";
                } else {
                    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $message = "Category deleted successfully!";
                    } else {
                        $error = "Failed to delete category.";
                    }
                }
            }
            break;
    }
}

// Get all categories with recipe count
$categories_query = "
    SELECT c.*, COUNT(r.id) as recipe_count
    FROM categories c
    LEFT JOIN recipes r ON c.id = r.category_id
    GROUP BY c.id
    ORDER BY c.name
";

$categories_result = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - RecipeCraft Admin</title>
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
                    <li><a href="recipes.php"><i class="fas fa-book"></i><span>Recipes</span></a></li>
                    <li class="active"><a href="categories.php"><i class="fas fa-tags"></i><span>Categories</span></a></li>
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
                    <input type="text" placeholder="Search categories..." id="category-search">
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
                    <h2><i class="fas fa-tags"></i> Category Management</h2>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Category
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

                <!-- Categories Grid -->
                <div class="categories-grid">
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <div class="category-card">
                            <div class="category-header">
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                <div class="category-actions">
                                    <button class="btn btn-info btn-sm" onclick="editCategory(<?php echo $category['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="category-info">
                                <div class="category-slug">
                                    <strong>Slug:</strong> <?php echo htmlspecialchars($category['slug']); ?>
                                </div>
                                
                                <?php if ($category['description']): ?>
                                    <div class="category-description">
                                        <strong>Description:</strong> <?php echo htmlspecialchars($category['description']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="category-stats">
                                    <span class="recipe-count">
                                        <i class="fas fa-book"></i> <?php echo $category['recipe_count']; ?> recipes
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="category-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Add Category</h3>
                <span class="close-modal">&times;</span>
            </div>
            <form id="category-form" method="POST">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="category-id" value="">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Category Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="slug">Slug *</label>
                        <input type="text" id="slug" name="slug" required placeholder="e.g., main-dishes">
                        <small class="form-help">URL-friendly version of the name (lowercase, hyphens instead of spaces)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Brief description of this category"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('category-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
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
                <p>Are you sure you want to delete category <strong id="delete-category-name"></strong>?</p>
                <p class="warning-text">This action cannot be undone. Make sure there are no recipes in this category.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('delete-modal')">Cancel</button>
                <button class="btn btn-danger" id="confirm-delete">Delete Category</button>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('category-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const categoryCards = document.querySelectorAll('.category-card');
            
            categoryCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const slug = card.querySelector('.category-slug').textContent.toLowerCase();
                const description = card.querySelector('.category-description')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || slug.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            document.getElementById('slug').value = slug;
        });

        // Open add modal
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Add Category';
            document.getElementById('form-action').value = 'add';
            document.getElementById('category-id').value = '';
            document.getElementById('category-form').reset();
            document.getElementById('category-modal').style.display = 'flex';
        }

        // Edit category
        function editCategory(categoryId) {
            fetch(`get_category.php?id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-title').textContent = 'Edit Category';
                    document.getElementById('form-action').value = 'edit';
                    document.getElementById('category-id').value = categoryId;
                    document.getElementById('name').value = data.name;
                    document.getElementById('slug').value = data.slug;
                    document.getElementById('description').value = data.description;
                    document.getElementById('category-modal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load category data');
                });
        }

        // Delete category
        function deleteCategory(categoryId, categoryName) {
            document.getElementById('delete-category-name').textContent = categoryName;
            document.getElementById('confirm-delete').onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${categoryId}">
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
