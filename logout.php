<?php
require_once 'includes/init.php';

// Log activity before destroying session
if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'logout');
}

// Destroy session
session_destroy();

// Redirect to home page
redirect('/index.php');
