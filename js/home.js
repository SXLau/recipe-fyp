document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const recipesBtn = document.getElementById('recipes-btn');
    const rankingBtn = document.getElementById('ranking-btn');
    const searchInput = document.querySelector('.search-bar input');
    const searchBtn = document.querySelector('.search-bar button');
    const ratingModal = document.getElementById('rating-modal');
    const ratingDishTitle = document.getElementById('rating-dish-title');
    const stars = document.querySelectorAll('.stars-container i');
    const ratingSelected = document.getElementById('rating-selected');
    const submitRatingBtn = document.getElementById('submit-rating');
    const closeModalBtn = ratingModal.querySelector('.close-modal');

    // Current dish being rated
    let currentDish = null;
    let selectedRating = 0;

    // Sample dish data with ratings
    const dishesData = {
        'main-dishes': [
            {
                id: 'steak',
                title: "Beef Steak",
                image: "https://images.unsplash.com/photo-1600891964599-f61ba0e24092?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
                time: "30 mins",
                rating: 4.7,
                totalRatings: 42,
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
                id: 'salmon',
                title: "Grilled Salmon",
                image: "https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
                time: "25 mins",
                rating: 4.8,
                totalRatings: 38,
                description: "Fresh salmon with lemon and dill",
                ingredients: ["1 lb salmon fillet", "1 lemon", "2 tbsp olive oil", "1 tsp dill", "1/2 tsp salt"],
                steps: [
                    "Preheat grill to medium-high",
                    "Rub salmon with olive oil and seasonings",
                    "Grill for 4-5 minutes per side",
                    "Squeeze lemon juice over before serving"
                ]
            }
        ],
        'snacks-street-food': [
            {
                id: 'tacos',
                title: "Tacos",
                image: "https://images.unsplash.com/photo-1565299585323-38d6b0865b47?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
                time: "20 mins",
                rating: 4.5,
                totalRatings: 31,
                description: "Authentic Mexican street tacos",
                ingredients: ["8 small corn tortillas", "1 lb grilled chicken", "1 onion, diced", "1/2 cup cilantro", "2 limes", "Salsa of choice"],
                steps: [
                    "Warm tortillas on a dry skillet",
                    "Chop cooked chicken into small pieces",
                    "Dice onion and chop cilantro",
                    "Assemble tacos with chicken, onion, and cilantro",
                    "Serve with lime wedges and salsa"
                ]
            }
        ],
        'desserts': [
            {
                id: 'cake',
                title: "Chocolate Cake",
                image: "https://tse3.mm.bing.net/th?id=OIP.QxIqDCGpPMLUKynCbG_8zwHaLH&pid=Api",
                time: "1 hour",
                rating: 4.9,
                totalRatings: 56,
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
            }
        ],
        'traditional-food': [
            {
                id: 'sushi',
                title: "Sushi",
                image: "https://images.unsplash.com/photo-1579871494447-9811cf80d66c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
                time: "1 hour",
                rating: 4.8,
                totalRatings: 47,
                description: "Traditional Japanese sushi rolls",
                ingredients: [
                    "2 cups sushi rice",
                    "2 1/4 cups water",
                    "1/4 cup rice vinegar",
                    "4 sheets nori (seaweed)",
                    "1/2 lb fresh salmon or tuna",
                    "1 avocado",
                    "1 cucumber",
                    "Soy sauce and wasabi for serving"
                ],
                steps: [
                    "Rinse rice until water runs clear",
                    "Cook rice with water in rice cooker",
                    "Mix vinegar with sugar and salt, fold into cooked rice",
                    "Slice fish and vegetables into thin strips",
                    "Place nori on bamboo mat, spread rice evenly",
                    "Add fillings and roll tightly",
                    "Slice into pieces with sharp knife",
                    "Serve with soy sauce and wasabi"
                ]
            }
        ]
    };

    // Navigation buttons functionality
    recipesBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'home.html';
    });

    rankingBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'ranking.html';
    });

    // Search functionality
    searchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            filterDishes(searchTerm);
        }
    });

    // Search when pressing Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                filterDishes(searchTerm);
            }
        }
    });

    // Filter dishes based on search term
    function filterDishes(searchTerm) {
        const searchLower = searchTerm.toLowerCase();
        const allDishes = Object.values(dishesData).flat();
        const filteredDishes = allDishes.filter(dish => 
            dish.title.toLowerCase().includes(searchLower) ||
            dish.description.toLowerCase().includes(searchLower) ||
            dish.ingredients.some(ing => ing.toLowerCase().includes(searchLower))
        );
        
        const dishesContainer = document.getElementById('dishes-container');
        dishesContainer.innerHTML = '';
        
        if (filteredDishes.length === 0) {
            dishesContainer.innerHTML = '<div class="no-results">No dishes found matching your search</div>';
            document.getElementById('category-title').textContent = `No results for "${searchTerm}"`;
            return;
        }
        
        filteredDishes.forEach(dish => {
            dishesContainer.appendChild(createDishCard(dish));
        });
        
        document.getElementById('category-title').textContent = `Search Results for "${searchTerm}"`;
        setupDishClick();
    }

    // Category click functionality
    function setupCategoryClick() {
        const categoryCards = document.querySelectorAll('.category-card');
        const dishesContainer = document.getElementById('dishes-container');
        const categoryTitle = document.getElementById('category-title');

        categoryCards.forEach(card => {
            card.addEventListener('click', function() {
                categoryCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.getAttribute('data-category');
                const categoryName = this.querySelector('h3').textContent;
                categoryTitle.textContent = `${categoryName}`;
                
                dishesContainer.innerHTML = '<div class="loading">Loading dishes...</div>';
                
                setTimeout(() => {
                    showDishes(category);
                }, 500);
            });
        });
    }

    // Show dishes for selected category
    function showDishes(category) {
        const dishesContainer = document.getElementById('dishes-container');
        const dishes = dishesData[category];
        
        dishesContainer.innerHTML = '';
        
        dishes.forEach(dish => {
            dishesContainer.appendChild(createDishCard(dish));
        });
        
        setupDishClick();
    }

    // Create dish card element
    function createDishCard(dish) {
        const dishCard = document.createElement('div');
        dishCard.className = 'dish-card';
        dishCard.dataset.id = dish.id;
        dishCard.innerHTML = `
            <img src="${dish.image}" alt="${dish.title}">
            <div class="dish-info">
                <h3>${dish.title}</h3>
                <p class="dish-description">${dish.description}</p>
                <div class="dish-meta">
                    <span><i class="far fa-clock"></i> ${dish.time}</span>
                    <span class="dish-rating">
                        <i class="fas fa-star"></i> ${dish.rating.toFixed(1)} (${dish.totalRatings})
                    </span>
                </div>
                <button class="rate-btn">Rate This Dish</button>
            </div>
        `;
        return dishCard;
    }

    // Dish click functionality
    function setupDishClick() {
        const dishesContainer = document.getElementById('dishes-container');
        
        dishesContainer.addEventListener('click', function(e) {
            const dishCard = e.target.closest('.dish-card');
            const rateBtn = e.target.closest('.rate-btn');
            
            if (rateBtn) {
                const dishId = dishCard.dataset.id;
                currentDish = findDishById(dishId);
                if (currentDish) {
                    openRatingModal(currentDish);
                }
                return;
            }
            
            if (dishCard && !rateBtn) {
                const dishTitle = dishCard.querySelector('h3').textContent;
                const dishData = findDishData(dishTitle);
                
                if (dishData) {
                    showDishModal(dishData);
                }
            }
        });
    }

    // Find dish by ID
    function findDishById(id) {
        for (const category in dishesData) {
            const foundDish = dishesData[category].find(dish => dish.id === id);
            if (foundDish) return foundDish;
        }
        return null;
    }

    // Find dish data by title
    function findDishData(title) {
        for (const category in dishesData) {
            const foundDish = dishesData[category].find(dish => dish.title === title);
            if (foundDish) return foundDish;
        }
        return null;
    }

    // Rating modal functionality
    function openRatingModal(dish) {
        ratingDishTitle.textContent = `Rate ${dish.title}`;
        ratingSelected.textContent = "Select rating (1-5 stars)";
        selectedRating = 0;
        
        // Reset stars
        stars.forEach(star => {
            star.classList.remove('active');
            star.classList.remove('hover');
            star.classList.add('far');
            star.classList.remove('fas');
        });
        
        ratingModal.style.display = 'flex';
    }

    // Star rating interaction
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = parseInt(this.dataset.rating);
            highlightStars(rating);
        });
        
        star.addEventListener('mouseout', function() {
            if (selectedRating === 0) {
                resetStars();
            } else {
                highlightStars(selectedRating);
            }
        });
        
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            ratingSelected.textContent = `You selected: ${selectedRating} star${selectedRating > 1 ? 's' : ''}`;
            highlightStars(selectedRating);
        });
    });

    function highlightStars(rating) {
        stars.forEach(star => {
            star.classList.remove('hover');
            star.classList.remove('active');
            star.classList.add('far');
            star.classList.remove('fas');
            
            if (parseInt(star.dataset.rating) <= rating) {
                star.classList.add('fas');
                star.classList.remove('far');
                star.classList.add('hover');
            }
        });
    }

    function resetStars() {
        stars.forEach(star => {
            star.classList.remove('hover');
            star.classList.remove('active');
            star.classList.add('far');
            star.classList.remove('fas');
        });
    }

    // Submit rating
    submitRatingBtn.addEventListener('click', function() {
        if (selectedRating === 0) {
            ratingSelected.textContent = "Please select a rating first!";
            return;
        }
        
        if (currentDish) {
            // Update rating (average calculation)
            const newTotalRatings = currentDish.totalRatings + 1;
            const newRating = ((currentDish.rating * currentDish.totalRatings) + selectedRating) / newTotalRatings;
            
            currentDish.rating = newRating;
            currentDish.totalRatings = newTotalRatings;
            
            // Update the dish card
            updateDishRating(currentDish.id, newRating, newTotalRatings);
            
            // Close modal
            ratingModal.style.display = 'none';
        }
    });

    // Update dish rating display
    function updateDishRating(dishId, newRating, newTotalRatings) {
        const dishCard = document.querySelector(`.dish-card[data-id="${dishId}"]`);
        if (dishCard) {
            const ratingElement = dishCard.querySelector('.dish-rating');
            if (ratingElement) {
                ratingElement.innerHTML = `<i class="fas fa-star"></i> ${newRating.toFixed(1)} (${newTotalRatings})`;
            }
        }
    }

    // Close modal
    closeModalBtn.addEventListener('click', function() {
        ratingModal.style.display = 'none';
    });

    // Close when clicking outside modal
    ratingModal.addEventListener('click', function(e) {
        if (e.target === ratingModal) {
            ratingModal.style.display = 'none';
        }
    });

    // Show dish modal with details
    function showDishModal(dish) {
        const modal = document.createElement('div');
        modal.className = 'dish-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="modal-header">
                    <h2>${dish.title}</h2>
                    <div class="dish-rating">
                        <i class="fas fa-star"></i> ${dish.rating.toFixed(1)} (${dish.totalRatings})
                    </div>
                </div>
                <div class="modal-body">
                    <div class="modal-column">
                        <h3>Ingredients</h3>
                        <ul class="ingredients-list">
                            ${dish.ingredients.map(ing => `<li>${ing}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="modal-column">
                        <h3>Preparation Steps</h3>
                        <ol class="steps-list">
                            ${dish.steps.map(step => `<li>${step}</li>`).join('')}
                        </ol>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const closeBtn = modal.querySelector('.close-modal');
        closeBtn.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }

    // Add animation to elements when they come into view
    function setupAnimations() {
        const animateElements = document.querySelectorAll('.category-card, .dish-card');
        
        function checkAnimation() {
            animateElements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        }
        
        animateElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'all 0.5s ease';
        });
        
        checkAnimation();
        window.addEventListener('scroll', checkAnimation);
    }

    // Initialize page
    setupCategoryClick();
    setupAnimations();

    // Make first category active by default and show its dishes
    const firstCategory = document.querySelector('.category-card');
    if (firstCategory) {
        firstCategory.classList.add('active');
        const defaultCategory = firstCategory.getAttribute('data-category');
        const defaultCategoryName = firstCategory.querySelector('h3').textContent;
        document.getElementById('category-title').textContent = `${defaultCategoryName}`;
        showDishes(defaultCategory);
    }
});