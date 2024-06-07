<?php
header("Access-Control-Allow-Origin: *");
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "testingdb";

$con = mysqli_connect($host, $user, $password, $dbname);

if (!$con) {
    http_response_code(500);
    die("Connection failed: " . mysqli_connect_error());
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $matricule = isset($_GET["matricule"]) ? $_GET["matricule"] : null;
        if ($matricule !== null) {
            $sql = "SELECT * FROM personnel WHERE matricule='$matricule'";
        } else {
            $sql = "SELECT * FROM personnel";
        }
        break;

    case 'POST':
        $matricule = isset($_GET["matricule"]) ? $_GET["matricule"] : null;
        if ($matricule !== null) {
            $pass = $_POST["pass"]; 
            $sql = "UPDATE personnel SET pass='$pass' WHERE matricule='$matricule'";
        } else {
            http_response_code(400);
            die("Matricule is missing in the request.");
        }
        break;

    default:
        http_response_code(405);
        die("Method Not Allowed");
}

$result = mysqli_query($con, $sql);

if (!$result) {
    http_response_code(500);
    die(mysqli_error($con));
}

if ($method === 'GET') {
    $response = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
} elseif ($method === 'POST') {
    echo json_encode(["message" => "Password updated successfully."]);
}

mysqli_close($con);
?>
