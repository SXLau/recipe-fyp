document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const loginBtn = document.getElementById('login-btn');
    const signupBtn = document.getElementById('signup-btn');
    const loginModal = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');
    const loginForm = document.getElementById('login');
    const registerForm = document.getElementById('register');
    const exploreBtn = document.getElementById('explore-btn');

    // Open Login Modal
    loginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        loginModal.style.display = 'block';
        registerModal.style.display = 'none';
    });

    // Open Register Modal
    signupBtn.addEventListener('click', function(e) {
        e.preventDefault();
        registerModal.style.display = 'block';
        loginModal.style.display = 'none';
    });

    // Close Modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            loginModal.style.display = 'none';
            registerModal.style.display = 'none';
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === loginModal) {
            loginModal.style.display = 'none';
        }
        if (e.target === registerModal) {
            registerModal.style.display = 'none';
        }
    });

    // Toggle between login and register forms in modals
    showRegister.addEventListener('click', function(e) {
        e.preventDefault();
        loginModal.style.display = 'none';
        registerModal.style.display = 'block';
    });

    showLogin.addEventListener('click', function(e) {
        e.preventDefault();
        registerModal.style.display = 'none';
        loginModal.style.display = 'block';
    });

    // Login form submission
    loginForm.addEventListener('submit', function(e) {
        // Let the form submit naturally to login.php
        // No preventDefault() needed
    });

    // Register form submission
    registerForm.addEventListener('submit', function(e) {
        // Let the form submit naturally to register.php
        // No preventDefault() needed
    });

    // Features animation (keep this from previous version)
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