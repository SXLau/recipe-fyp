document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const rankingContainer = document.getElementById('ranking-container');
    const filterButtons = document.querySelectorAll('.filter-btn');

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