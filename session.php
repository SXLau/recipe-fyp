<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get current user ID
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function get_current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Require login
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

// Require admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        redirect('index.php');
    }
}

// Set user session
function set_user_session($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

// Clear user session
function clear_user_session() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);
    session_destroy();
}
?>
