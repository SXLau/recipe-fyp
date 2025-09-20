<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'food_recipes');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Helper function to escape strings
function escape_string($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

// Helper function to get user by ID
function get_user_by_id($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $query = "SELECT id, name, email, preferences, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Helper function to get user preferences
function get_user_preferences($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $query = "SELECT preferences FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        return [];
    }

    $rawPreferences = $row['preferences'];
    if ($rawPreferences === null || $rawPreferences === '') {
        return [];
    }

    $preferences = json_decode($rawPreferences, true);
    return is_array($preferences) ? $preferences : [];
}

// Helper function to update user preferences
function update_user_preferences($user_id, $preferences) {
    global $conn;
    $user_id = (int)$user_id;
    $preferences_json = json_encode($preferences);
    $query = "UPDATE users SET preferences = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $preferences_json, $user_id);
    return $stmt->execute();
}
?>
