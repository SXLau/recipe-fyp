document.addEventListener('DOMContentLoaded', function() {
    // Features animation
    const features = document.querySelectorAll('.feature');
    
    function checkFeatures() {
        features.forEach(feature => {
            const featurePosition = feature.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (featurePosition < screenPosition) {
                feature.style.opacity = '1';
                feature.style.transform = 'translateY(0)';
            }
        });
    }
    
    features.forEach(feature => {
        feature.style.opacity = '0';
        feature.style.transform = 'translateY(20px)';
        feature.style.transition = 'all 0.5s ease';
    });
    
    checkFeatures();
    window.addEventListener('scroll', checkFeatures);
});