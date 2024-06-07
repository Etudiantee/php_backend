<?php
session_start();
header('Content-Type: application/json');

header("Access-Control-Allow-Origin: *"); // Allow access from any origin for development purposes
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$database = "testingdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$tlc = isset($_GET['tlc']) ? $_GET['tlc'] : '';
$dayOfOrigin = isset($_GET['dayOfOrigin']) ? $_GET['dayOfOrigin'] : '';

if ($tlc && $dayOfOrigin) {
    $stmt = $conn->prepare("SELECT FLIGHT_NO, EXPECTED_DEPARTURE_TIME, FROM_AIRPORT, TO_AIRPORT 
                           FROM vols 
                           WHERE TLC = ? AND DAY_OF_ORIGIN = ?");
    $stmt->bind_param('ss', $tlc, $dayOfOrigin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $flightDetails = $result->fetch_assoc();
        echo json_encode($flightDetails);
    } else {
        echo json_encode(['error' => 'No flight details found']);
    }
} else {
    echo json_encode(['error' => 'Missing parameters']);
}

$stmt->close();
$conn->close();
?>
