<?php
require_once 'config.php';
require_once 'auth.php';

$pdo = getDBConnection();
$auth = new Auth($pdo);

// Logout user
$result = $auth->logout();

// Redirect to login page
header('Location: login.php?message=logged_out');
exit();
?> 