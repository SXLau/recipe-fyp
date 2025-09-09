<?php
require_once '../session.php';
require_once '../db.php';
require_admin();

$recipe_id = (int)($_GET['id'] ?? 0);
$view_mode = isset($_GET['view']);

if (!$recipe_id) {
    if ($view_mode) {
        echo '<p>Invalid recipe ID.</p>';
    } else {
        echo json_encode(['error' => 'Invalid recipe ID']);
    }
    exit;
}

// Get recipe details
$stmt = $conn->prepare("
    SELECT r.*, c.name as category_name,
           COALESCE(AVG(rt.rating), 0) as avg_rating,
           COUNT(rt.id) as rating_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN ratings rt ON r.id = rt.recipe_id
    WHERE r.id = ?
    GROUP BY r.id
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();

if (!$recipe) {
    if ($view_mode) {
        echo '<p>Recipe not found.</p>';
    } else {
        echo json_encode(['error' => 'Recipe not found']);
    }
    exit;
}

if ($view_mode) {
    // Return HTML for view modal
    ?>
    <div class="recipe-view">
        <div class="recipe-header">
            <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
            <div class="recipe-meta">
                <span class="category-badge"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                <span class="rating-display">
                    <?php echo number_format($recipe['avg_rating'], 1); ?> 
                    <i class="fas fa-star"></i> 
                    (<?php echo $recipe['rating_count']; ?> ratings)
                </span>
            </div>
        </div>
        
        <?php if ($recipe['image']): ?>
            <div class="recipe-image">
                <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" style="width: 100%; max-width: 300px; height: 200px; object-fit: cover; border-radius: 8px;">
            </div>
        <?php endif; ?>
        
        <div class="recipe-details">
            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Prep Time:</strong> <?php echo htmlspecialchars($recipe['prep_time']); ?>
                </div>
                <div class="detail-item">
                    <strong>Difficulty:</strong> <?php echo htmlspecialchars($recipe['difficulty']); ?>
                </div>
                <div class="detail-item">
                    <strong>Servings:</strong> <?php echo htmlspecialchars($recipe['servings']); ?>
                </div>
                <div class="detail-item">
                    <strong>Created:</strong> <?php echo date('M j, Y', strtotime($recipe['created_at'])); ?>
                </div>
            </div>
            
            <?php if ($recipe['description']): ?>
                <div class="recipe-description">
                    <h4>Description</h4>
                    <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="recipe-ingredients">
                <h4>Ingredients</h4>
                <ul>
                    <?php
                    $ingredients = explode("\n", $recipe['ingredients']);
                    foreach ($ingredients as $ingredient) {
                        if (trim($ingredient)) {
                            echo '<li>' . htmlspecialchars(trim($ingredient)) . '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
            
            <div class="recipe-steps">
                <h4>Instructions</h4>
                <ol>
                    <?php
                    $steps = explode("\n", $recipe['steps']);
                    foreach ($steps as $step) {
                        if (trim($step)) {
                            echo '<li>' . htmlspecialchars(trim($step)) . '</li>';
                        }
                    }
                    ?>
                </ol>
            </div>
        </div>
    </div>
    
    <?php
} else {
    // Return JSON for edit form
    echo json_encode($recipe);
}
?>
