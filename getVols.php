<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$database = "testingdb";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
  $response = array("success" => false, "message" => "Connection failed: " . $conn->connect_error);
  echo json_encode($response);
  exit();
}

$TLC = isset($_GET['TLC']) ? $_GET['TLC'] : '';

if (empty($TLC)) {
  $response = array("success" => false, "message" => "TLC is required");
  echo json_encode($response);
  exit();
}

$sanitizedTLC = filter_var($TLC, FILTER_SANITIZE_STRING);

$sql = "SELECT DAY_OF_ORIGIN, FLIGHT_NO, EXPECTED_DEPARTURE_TIME, FROM_AIRPORT, TO_AIRPORT FROM vols WHERE TLC = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sanitizedTLC);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $volsData = $result->fetch_assoc();
  $response = array("success" => true, "vols" => $volsData);
} else {
  $response = array("success" => false, "message" => "No data found for the given TLC");
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>
