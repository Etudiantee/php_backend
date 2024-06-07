<?php
header("Access-Control-Allow-Origin: *");

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
        $id = isset($_GET["id"]) ? $_GET["id"] : null;
        $matricule = isset($_GET["TLC"]) ? $_GET["TLC"] : null;

        if ($matricule) {
            $sql = "SELECT * FROM vols WHERE TLC='$TLC'";
        } else {
            $sql = "SELECT * FROM vol" . ($id ? " WHERE id=$id" : '');
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
    if (!$id) {
        echo '[';
    }

    for ($i = 0; $i < mysqli_num_rows($result); $i++) {
        echo ($i > 0 ? ',' : '') . json_encode(mysqli_fetch_object($result));
    }

    if (!$id) {
        echo ']';
    }
} elseif ($method === 'POST') {
    echo json_encode($result);
} elseif ($method === 'DELETE') {
    echo mysqli_affected_rows($con);
}

$con->close();
?>

