<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-type");

// Your database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testingdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if TLC and pass are set in $_POST
if(isset($_POST['TLC']) && isset($_POST['pass'])) {
    $TLC = $_POST['TLC'];
    $password = $_POST['pass'];

    // Protect against SQL injection
    $TLC = mysqli_real_escape_string($conn, $TLC);
    $password = mysqli_real_escape_string($conn, $password);

    $sql = "SELECT * FROM personnel WHERE TLC = '$TLC' AND pass = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['secteur'] == 'admin') {
            $_SESSION['TLC'] = $TLC;
            $_SESSION['role'] = 'admin';
            $response = array(
                'success' => true,
                'role' => 'admin',
                'message' => 'Login successful'
            );
        } else {
            $_SESSION['TLC'] = $TLC;
            $_SESSION['role'] = 'user';
            $response = array(
                'success' => true,
                'role' => 'user',
                'message' => 'Login successful'
            );
        }
    } else {
        // Login failed
        $response = array(
            'success' => false,
            'message' => 'Invalid TLC or password'
        );
    }
} else {
    // TLC or pass not set in $_POST
    $response = array(
        'success' => false,
        'message' => 'TLC or password not provided'
    );
}

echo json_encode($response);

$conn->close();
?>
