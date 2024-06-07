<?php
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$user = "root";
$password = "";
$dbname = "testingdb";

// Establish database connection
$con = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if (!$con) {
    http_response_code(500); // Internal Server Error
    die("Connection failed: " . mysqli_connect_error());
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetRequest($con);
        break;
    case 'POST':
        handlePostRequest($con);
        break;
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("message" => "Method not allowed"));
}

function handleGetRequest($con) {
    $id = isset($_GET["id"]) ? $_GET['id'] : null;
    $matricule = isset($_GET["matricule"]) ? $_GET['matricule'] : null;
    $sql = "SELECT id, nom, prenom, base, matricule, secteur, college FROM crew";
    if ($id !== null) {
        $sql .= " WHERE id = $id";
    } elseif ($matricule !== null) {
        $matricule = mysqli_real_escape_string($con, $matricule);
        $sql .= " WHERE matricule = '$matricule'";
    }
    $result = mysqli_query($con, $sql);
    if (!$result) {
        http_response_code(500); // Internal Server Error
        die(mysqli_error($con));
    }
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function handlePostRequest($con) {
    $data = json_decode(file_get_contents("php://input"), true);
    // Validate input data here...
    $nom = mysqli_real_escape_string($con, $data["nom"]);
    $prenom = mysqli_real_escape_string($con, $data["prenom"]);
    // Validate and sanitize other fields...

    $sql = "INSERT INTO crew (nom, prenom, ...) VALUES ('$nom', '$prenom', ...)";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        http_response_code(500); // Internal Server Error
        die(mysqli_error($con));
    }
    echo json_encode(array("message" => "Record inserted successfully"));
}

$con->close();
?>

