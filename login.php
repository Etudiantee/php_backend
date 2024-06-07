<?php
session_start();


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-type");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testingdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["result" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$eData = file_get_contents("php://input");
$dData = json_decode($eData, true);

$matricule = isset($dData['matricule']) ? $dData['matricule'] : '';
$pass = isset($dData['pass']) ? $dData['pass'] : '';

$response = [];

if (!empty($matricule) && !empty($pass)) {
    $stmt = $conn->prepare("SELECT * FROM personnel WHERE matricule=?");
    $stmt->bind_param("s", $matricule);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows != 0) {
        $row = $res->fetch_assoc();
        if ($pass === $row['pass']) { // Compare plain text password
            $_SESSION['matricule'] = $matricule; // Store matricule in session
            $secteur = $row['secteur'];
            $result = "login in successfully";
        } else {
            $result = "Invalid matricule or password";
            $secteur = null;
        }
    } else {
        $result = "Invalid matricule or password";
        $secteur = null;
    }
    $stmt->close();
} else {
    $result = "Matricule and password cannot be empty";
    $secteur = null;
}

$response[] = ["result" => $result, "secteur" => $secteur];
echo json_encode($response);

$conn->close();
?>
