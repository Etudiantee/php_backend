<?php
session_start();

// **IMPORTANT:** Disable CORS for production (security risk)
// header("Access-Control-Allow-Origin: *"); // Remove for production
header("Access-Control-Allow-Origin: *"); // Persist for development convenience
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Debugging: Log received POST data
error_log(json_encode($_POST));

// Extract data from the request body (assuming JSON format)
$data = json_decode(file_get_contents('php://input'), true); // Set true for associative array

// Validate data
if (!isset($data['TLC']) || empty($data['TLC'])) {
  $response = array("success" => false, "message" => "TLC is required");
  echo json_encode($response);
  exit();
}

// Sanitize TLC data (consider using a more specific method based on your needs)
$sanitizedTLC = filter_var($data['TLC'], FILTER_SANITIZE_STRING);

// Store sanitized TLC value in session
$_SESSION['TLC'] = $sanitizedTLC;

// Connect to your MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$database = "testingdb";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  $response = array("success" => false, "message" => "Connection failed: " . $conn->connect_error);
  echo json_encode($response);
  exit();
}

// Prepare and execute the SQL statement to insert line data into the database
$stmt = $conn->prepare("INSERT INTO flight (DAY_OF_ORIGIN, TLC, FLIGHT_NO, EXPECTED_DEPARTURE_TIME, FROM_AIRPORT, TO_AIRPORT) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $data['DAY_OF_ORIGIN'], $sanitizedTLC, $data['FLIGHT_NO'], $data['EXPECTED_DEPARTURE_TIME'], $data['FROM_AIRPORT'], $data['TO_AIRPORT']);

if ($stmt->execute() === TRUE) {
  $response = array("success" => true);
  echo json_encode($response);
} else {
  $response = array("success" => false, "message" => "Error inserting data: " . $conn->error);
  echo json_encode($response);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
