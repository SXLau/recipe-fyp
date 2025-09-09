document.addEventListener('DOMContentLoaded', function() {
    // Sample data for demonstration
    const sampleRecipes = [
        {
            id: 1,
            name: "Beef Steak",
            category: "Main Dishes",
            rating: 4.7,
            prepTime: "30 mins",
            image: "https://images.unsplash.com/photo-1600891964599-f61ba0e24092?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            description: "Juicy steak with herbs and butter",
            ingredients: ["1 lb beef steak", "2 tbsp olive oil", "1 tsp salt", "1/2 tsp black pepper", "2 cloves garlic", "2 tbsp butter"],
            steps: [
                "Season steak with salt and pepper",
                "Heat oil in pan over high heat",
                "Sear steak for 3-4 minutes per side",
                "Add butter and garlic, baste steak",
                "Rest for 5 minutes before serving"
            ]
        },
        {
            id: 2,
            name: "Chocolate Cake",
            category: "Desserts",
            rating: 4.9,
            prepTime: "1 hour",
            image: "https://images.unsplash.com/photo-1565800080240-32b6b3b7ba1b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            description: "Rich chocolate cake with frosting",
            ingredients: [
                "2 cups all-purpose flour",
                "2 cups sugar",
                "3/4 cup cocoa powder",
                "2 tsp baking soda",
                "1 tsp baking powder",
                "1 tsp salt",
                "2 eggs",
                "1 cup buttermilk",
                "1 cup vegetable oil",
                "2 tsp vanilla extract",
                "1 cup boiling water"
            ],
            steps: [
                "Preheat oven to 350°F (175°C)",
                "Grease and flour two 9-inch cake pans",
                "Mix dry ingredients in large bowl",
                "Add eggs, buttermilk, oil and vanilla",
                "Beat for 2 minutes on medium speed",
                "Stir in boiling water (batter will be thin)",
                "Pour into prepared pans",
                "Bake for 30-35 minutes",
                "Cool completely before frosting"
            ]
        },
        {
            id: 3,
            name: "Vegetable Curry",
            category: "Main Dishes",
            rating: 4.3,
            prepTime: "45 mins",
            image: "https://images.unsplash.com/photo-1585937421612-70a008356fbe?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            description: "Spicy vegetable curry with coconut milk",
            ingredients: [
                "2 tbsp vegetable oil",
                "1 onion, chopped",
                "3 cloves garlic, minced",
                "1 tbsp ginger, grated",
                "2 tbsp curry powder",
                "1 can coconut milk",
                "2 cups mixed vegetables",
                "1 cup vegetable broth",
                "Salt to taste"
            ],
            steps: [
                "Heat oil in large pot over medium heat",
                "Add onion, garlic and ginger, cook until soft",
                "Add curry powder, stir for 1 minute",
                "Add vegetables, stir to coat with spices",
                "Pour in coconut milk and broth, bring to simmer",
                "Cook for 20-25 minutes until vegetables are tender",
                "Season with salt to taste",
                "Serve with rice or naan bread"
            ]
        }
    ];

    const sampleCategories = [
        { id: 1, name: "Main Dishes", recipeCount: 124 },
        { id: 2, name: "Snacks", recipeCount: 56 },
        { id: 3, name: "Desserts", recipeCount: 42 },
        { id: 4, name: "Breakfast", recipeCount: 38 },
        { id: 5, name: "Salads", recipeCount: 29 },
        { id: 6, name: "Beverages", recipeCount: 27 }
    ];

    const topRecipes = [
    { name: "Chocolate Cake", rating: 4.9 },
    { name: "Beef Steak", rating: 4.7 },
    { name: "Vegetable Curry", rating: 4.3 },
    { name: "Pasta Carbonara", rating: 4.2 },
    { name: "Apple Pie", rating: 4.1 }
];

    // DOM Elements
    const sidebarLinks = document.querySelectorAll('.sidebar nav ul li a');
    const contentSections = document.querySelectorAll('.content-section');
    const addRecipeBtn = document.getElementById('add-recipe-btn');
    const addRecipeModal = document.getElementById('add-recipe-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelRecipeBtn = document.getElementById('cancel-recipe');
    const recipeForm = document.getElementById('recipe-form');
    const recipesTable = document.querySelector('#recipes table tbody');
    const categoriesGrid = document.querySelector('.categories-grid');

    // Navigation functionality
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links and sections
            sidebarLinks.forEach(l => {
                l.parentElement.classList.remove('active');
            });
            contentSections.forEach(section => {
                section.classList.remove('active');
            });
            
            // Add active class to clicked link and corresponding section
            this.parentElement.classList.add('active');
            const sectionId = this.getAttribute('href');
            document.querySelector(sectionId).classList.add('active');
            
            // Load content if needed
            if (sectionId === '#recipes') {
                loadRecipes();
            } else if (sectionId === '#categories') {
                loadCategories();
            }
        });
    });

    // Then add this function after your loadCategories function:
function initRankingChart() {
    const ctx = document.getElementById('rankingChart').getContext('2d');
    
    // Sort recipes by rating in descending order
    const sortedRecipes = [...sampleRecipes].sort((a, b) => b.rating - a.rating).slice(0, 5);
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedRecipes.map(recipe => recipe.name),
            datasets: [{
                label: 'Rating',
                data: sortedRecipes.map(recipe => recipe.rating),
                backgroundColor: [
                    'rgba(74, 111, 165, 0.7)',
                    'rgba(74, 111, 165, 0.6)',
                    'rgba(74, 111, 165, 0.5)',
                    'rgba(74, 111, 165, 0.4)',
                    'rgba(74, 111, 165, 0.3)'
                ],
                borderColor: [
                    'rgba(74, 111, 165, 1)',
                    'rgba(74, 111, 165, 1)',
                    'rgba(74, 111, 165, 1)',
                    'rgba(74, 111, 165, 1)',
                    'rgba(74, 111, 165, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 0.5
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Rating: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}

    // Modal functionality
    addRecipeBtn.addEventListener('click', function() {
        addRecipeModal.style.display = 'flex';
    });

    closeModalBtn.addEventListener('click', function() {
        addRecipeModal.style.display = 'none';
    });

    cancelRecipeBtn.addEventListener('click', function() {
        addRecipeModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === addRecipeModal) {
            addRecipeModal.style.display = 'none';
        }
    });

    // Form submission
    recipeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const name = document.getElementById('recipe-name').value;
        const category = document.getElementById('recipe-category').value;
        const time = document.getElementById('recipe-time').value;
        const image = document.getElementById('recipe-image').value;
        const description = document.getElementById('recipe-description').value;
        const ingredients = document.getElementById('recipe-ingredients').value.split('\n');
        const steps = document.getElementById('recipe-steps').value.split('\n');
        
        // Create new recipe object
        const newRecipe = {
            id: sampleRecipes.length + 1,
            name,
            category: document.getElementById('recipe-category').options[document.getElementById('recipe-category').selectedIndex].text,
            rating: 0,
            prepTime: time,
            image,
            description,
            ingredients: ingredients.filter(i => i.trim() !== ''),
            steps: steps.filter(s => s.trim() !== '')
        };
        
        // Add to sample data (in a real app, this would be an API call)
        sampleRecipes.push(newRecipe);
        
        // Update UI
        loadRecipes();
        
        // Close modal and reset form
        addRecipeModal.style.display = 'none';
        recipeForm.reset();
        
        // Show success message
        alert('Recipe added successfully!');
    });

    // Load recipes into table
    function loadRecipes() {
        recipesTable.innerHTML = '';
        
        sampleRecipes.forEach(recipe => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${recipe.id}</td>
                <td>
                    <div class="recipe-info">
                        <img src="${recipe.image}" alt="${recipe.name}" width="50">
                        <div>
                            <strong>${recipe.name}</strong>
                            <p class="recipe-description">${recipe.description}</p>
                        </div>
                    </div>
                </td>
                <td>${recipe.category}</td>
                <td>
                    <div class="rating-display">
                        <i class="fas fa-star"></i>
                        <span>${recipe.rating}</span>
                    </div>
                </td>
                <td>${recipe.prepTime}</td>
                <td>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </td>
            `;
            recipesTable.appendChild(row);
        });
    }

    // Load categories into grid
    function loadCategories() {
        categoriesGrid.innerHTML = '';
        
        sampleCategories.forEach(category => {
            const card = document.createElement('div');
            card.className = 'category-card';
            card.innerHTML = `
                <h3>${category.name}</h3>
                <p>${category.recipeCount} recipes</p>
                <div class="category-actions">
                    <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </div>
            `;
            categoriesGrid.appendChild(card);
        });
    }

    // Initialize the page
    loadRecipes();
    initRankingChart();
});