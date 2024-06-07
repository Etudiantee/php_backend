<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-type");
if (!isset($_SESSION['matricule'])) {
    echo json_encode(array('success' => false, 'message' => 'Not authenticated'));
    exit();
}

$matricule = $_SESSION['matricule'];

$host = 'localhost';
$db = 'testingdb';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    echo json_encode(array('success' => false, 'message' => 'Failed to connect to the database'));
    exit();
}

$stmt = $mysqli->prepare("SELECT * FROM vols WHERE matricule = ?");
$stmt->bind_param('s', $matricule);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(array('success' => true, 'data' => $row));
} else {
    echo json_encode(array('success' => false, 'message' => 'No data found'));
}

$stmt->close();
$mysqli->close();
?>