<?php
// Define your database connection details
header("Access-Control-Allow-Origin: *"); //add this CORS header to enable any domain to send HTTP requests to these endpoints:
$host = "localhost"; 
$user = "root"; 
$password = ""; 
$dbname = "login"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<?php
// Database connection
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-type");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "react";
$conn = new mysqli($servername, $username, $password, $dbname);
if(mysqli_connect_error()){
    echo mysqli_connect_error();
    exit();
}
else{
    $eData = file_get_contents("php://input");
    $dData = json_decode($eData, true);

    $user = $dData['user'];
    $pass = $dData['pass'];
    if($user != "" and $pass != ""){
        // Using prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE user=? AND pass=?");
        $stmt->bind_param("ss", $user, $pass);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows != 0){
            $result = "logged in successfully";
        }
        else{
            $result = "Invalid credentials";
        }
        $stmt->close();
    }
}

$response[] = array("result" => $result);
echo json_encode($response);
?>
