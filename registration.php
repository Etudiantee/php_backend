<?php
// Database connection
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-type");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "register";

$conn = new mysqli($servername, $username, $password, $dbname);
if (mysqli_connect_error()) {
    echo mysqli_connect_error();
    exit();
} else {
    $eData = file_get_contents("php://input");
    $dData = json_decode($eData, true);

    $matricule = $dData['matricule'];
    $nom = $dData['nom'];
    $prenom = $dData['prenom'];
    $base = $dData['base'];
    $secteur = $dData['secteur'];
    $email = $dData['email'];
    $pass = $dData['pass'];

    if ($matricule != "" &&  $nom != "" && $prenom != "" && $base != "" && $secteur != "" && $email != "" && $pass != "") {
        // Hash the password
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        // Using prepared statements to prevent SQL injection
        $status = $conn->prepare("INSERT INTO user (matricule, nom, prenom, base, secteur, email, pass) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $status->bind_param("sssssss", $matricule, $nom, $prenom, $base, $secteur, $email, $hashedPass);

        if ($status->execute()) {
            $result = "registered successfully";
        } else {
            $result = "";
        }
        $status->close();
    } else {
        $result = "";
    }

    $conn->close();
    echo json_encode(["result" => $result]); // Directly encode the result to JSON
}
?>
