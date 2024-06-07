<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-type");
session_start();
// Database connection parameters
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

// Retrieve matricule from GET request
$matricule = $_GET['matricule'];

// Check if matricule is provided
if(empty($matricule)) {
    echo json_encode(array("error" => "Matricule is required"));
    exit; // Stop further execution
}

// Prepare SQL statement to fetch user data and related vols from tables
$sql = "SELECT p.*, v.*
FROM personnel p
INNER JOIN vols v ON p.matricule = v.matricule
WHERE p.matricule = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricule);
$stmt->execute();
$result = $stmt->get_result();

// Check if user data is found
if ($result->num_rows > 0) {
    // Fetch user data as an associative array
    $userData = $result->fetch_assoc();
    
    // Encode user data as JSON and return
    echo json_encode($userData);
} else {
    // No user found with the provided matricule
    echo json_encode(array("error" => "No user found with the provided matricule"));
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
