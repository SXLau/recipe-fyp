<?php
require_once '../session.php';

// Clear user session
clear_user_session();

// Redirect to admin login
redirect('login.php');
?>
