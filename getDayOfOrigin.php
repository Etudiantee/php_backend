<?php
session_start();

header("Access-Control-Allow-Origin: *"); // Persist for development convenience
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
// Connect to your MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$database = "testingdb";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$tlc = $_GET['tlc'];

$sql = "SELECT Distinct DAY_OF_ORIGIN FROM vols
        WHERE TLC = ? 
        AND NOT EXISTS (
            SELECT DAY_OF_ORIGIN FROM vol
            WHERE vols.DAY_OF_ORIGIN = vol.DAY_OF_ORIGIN 
            AND vols.TLC = vol.TLC
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tlc);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
