document.addEventListener('DOMContentLoaded', function() {
    // Sample ranking data (without reviews)
    const rankingData = [
        {
            id: 1,
            title: "Chocolate Cake",
            image: "https://tse3.mm.bing.net/th?id=OIP.QxIqDCGpPMLUKynCbG_8zwHaLH&pid=Api",
            time: "1 hour",
            rating: "4.9",
            description: "Rich chocolate cake with frosting that melts in your mouth",
            category: "desserts"
        },
        {
            id: 2,
            title: "Sushi",
            image: "https://images.unsplash.com/photo-1579871494447-9811cf80d66c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            time: "1 hour",
            rating: "4.8",
            description: "Traditional Japanese sushi rolls with fresh ingredients",
            category: "traditional-food"
        },
        {
            id: 3,
            title: "Beef Steak",
            image: "https://images.unsplash.com/photo-1600891964599-f61ba0e24092?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            time: "30 mins",
            rating: "4.7",
            description: "Juicy steak with herbs and butter cooked to perfection",
            category: "main-dishes"
        },
        {
            id: 4,
            title: "Tacos",
            image: "https://images.unsplash.com/photo-1565299585323-38d6b0865b47?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            time: "20 mins",
            rating: "4.6",
            description: "Authentic Mexican street tacos with flavorful fillings",
            category: "snacks-street-food"
        },
        {
            id: 5,
            title: "Grilled Salmon",
            image: "https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            time: "25 mins",
            rating: "4.5",
            description: "Fresh salmon with lemon and dill, perfectly grilled",
            category: "main-dishes"
        },
        {
            id: 6,
            title: "Ice Cream",
            image: "https://images.unsplash.com/photo-1497034825429-c343d7c6a68f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80",
            time: "15 mins",
            rating: "4.4",
            description: "Homemade vanilla ice cream with rich creamy texture",
            category: "desserts"
        }
    ];

    // DOM Elements
    const rankingContainer = document.getElementById('ranking-container');
    const filterButtons = document.querySelectorAll('.filter-btn');

    // Load ranking items
    function loadRankingItems(filter = 'all') {
        rankingContainer.innerHTML = '';
        
        let filteredItems = rankingData;
        if (filter !== 'all') {
            filteredItems = rankingData.filter(item => item.category === filter);
        }
        
        filteredItems.forEach((item, index) => {
            const rankingItem = document.createElement('div');
            rankingItem.className = 'ranking-item';
            rankingItem.innerHTML = `
                <div class="ranking-badge">${index + 1}</div>
                <img src="${item.image}" alt="${item.title}">
                <div class="ranking-info">
                    <h3>${item.title}</h3>
                    <div class="ranking-meta">
                        <span><i class="far fa-clock"></i> ${item.time}</span>
                        <span class="ranking-rating">
                            <i class="fas fa-star"></i> ${item.rating}
                        </span>
                    </div>
                    <p class="ranking-description">${item.description}</p>
                    <span class="ranking-category">${formatCategory(item.category)}</span>
                </div>
            `;
            rankingContainer.appendChild(rankingItem);
        });
    }

    // Format category name for display
    function formatCategory(category) {
        return category.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    // Filter button functionality
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter');
            loadRankingItems(filter);
        });
    });

    // Initialize page
    loadRankingItems();

    // Add animation to ranking items
    function setupAnimations() {
        const rankingItems = document.querySelectorAll('.ranking-item');
        
        function checkAnimation() {
            rankingItems.forEach(item => {
                const itemPosition = item.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (itemPosition < screenPosition) {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }
            });
        }
        
        // Set initial state
        rankingItems.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'all 0.5s ease';
        });
        
        // Check on load and scroll
        checkAnimation();
        window.addEventListener('scroll', checkAnimation);
    }

    // Setup animations after items are loaded
    setTimeout(setupAnimations, 100);
});