<?php session_start();

// Allow requests from the frontend domain (replace 'http://localhost:3000' with your actual frontend domain)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
echo"hi";
// Logout endpoint
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to the login page or any other page
    header("Location: /logged.php");
    exit;
}
?>