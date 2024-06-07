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
        $id = isset($_GET["id"]) ? $_GET["id"] : null;
        $matricule = isset($_GET["matricule"]) ? $_GET["matricule"] : null;

        if ($matricule) {
            $sql = "SELECT * FROM pnt WHERE matricule='$matricule'";
        } else {
            $sql = "SELECT * FROM personnel" . ($id ? " WHERE id=$id AND secteur != 'admin'" : " WHERE secteur != 'admin'");
        }
        break;

    case 'POST':
        $id = isset($_GET["id"]) ? $_GET["id"] : null;

        if ($id) {
            // Update existing contact
            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $base = $_POST["base"];
            $college = $_POST["college"];
            $secteur = $_POST["secteur"];
            $pass = $_POST["pass"]; // Hash the password
            $matricule = $_POST["matricule"];
            $sql = "UPDATE personnel SET pass='$pass' WHERE id=$id";
        } elseif (isset($_GET["delete"])){
            $delete = $_GET['delete'];
            $sql = "DELETE FROM personnel WHERE id = $delete";
        }
        else {
            // Insert new contact
            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $base = $_POST["base"];
            $college = $_POST["college"];
            $secteur = $_POST["secteur"];
            $pass = $_POST["pass"]; // Hash the password
            $matricule = $_POST["matricule"];

            // Check if matricule already exists
            $checkMatriculeQuery = "SELECT * FROM personnel WHERE TLC='$matricule'";
            $checkMatriculeResult = mysqli_query($con, $checkMatriculeQuery);

            if (mysqli_num_rows($checkMatriculeResult) > 0) {
                // Matricule already exists, handle the case accordingly
                http_response_code(400); // Bad Request
                echo "Matricule already exists.";
                exit();
            }

            // Matricule is unique, proceed with the insertion
            $sql = "INSERT INTO personnel (TLC, nom, prenom, base, college, secteur, pass) VALUES ('$matricule', '$nom', '$prenom', '$base', '$college', '$secteur', '$pass')";
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

