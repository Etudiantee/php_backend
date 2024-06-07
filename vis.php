<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testingdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array();
$totBlockTime = "00:00:00"; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (is_array($data)) {
        $conn->begin_transaction(); // Start a transaction

        try {
            foreach ($data as $row) {
                // Extract data from the row
                $dayOfOrigin = $row['DAY_OF_ORIGIN'];
                $TLC = $row['TLC'];
                $flightNo = $row['FLIGHT_NO'];
                
           

               // Check if the combination of DAY_OF_ORIGIN, TLC, FLIGHT_NO already exists in vol table
$sql_check_vol = "SELECT * FROM vol WHERE DAY_OF_ORIGIN = '$dayOfOrigin' AND TLC = '$TLC' AND FLIGHT_NO = '$flightNo'AND  EXPECTED_DEPARTURE_TIME='$expectedDepartureTime'";
$result_vol = $conn->query($sql_check_vol);

// Check if the combination of FLIGHT_NO, DAY_OF_ORIGIN, id_FDL already exists in feuille table
$sql_check_feuille = "SELECT * FROM feuille WHERE TLC = '$TLC' AND DAY_OF_ORIGIN = '$dayOfOrigin' AND id_FDL = '$id_FDL' ";
$result_feuille = $conn->query($sql_check_feuille);
            }

            // Commit transaction if all queries are successful
            $conn->commit();
        } catch (Exception $e) {
            // Rollback transaction if any query fails
            $conn->rollback();
            $response = array("status" => "error", "message" => $e->getMessage());
        }
    } else {
        $response = array("status" => "error", "message" => "Invalid input format");
    }

    echo json_encode($response);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $dayOfOrigin = isset($_GET['dayOfOrigin']) ? $_GET['dayOfOrigin'] : '';
    $TLC = isset($_GET['TLC']) ? $_GET['TLC'] : '';
    
    $sql = "SELECT 
    vol.*, 
    feuille.*, 
    crew.*,
    GROUP_CONCAT('crew' SEPARATOR ', ') AS crew_members
FROM vol
LEFT JOIN feuille 
  ON vol.TLC = feuille.TLC AND vol.DAY_OF_ORIGIN = feuille.DAY_OF_ORIGIN
LEFT JOIN crew
  ON vol.TLC = crew.TLC AND vol.DAY_OF_ORIGIN = crew.DAY_OF_ORIGIN
WHERE vol.DAY_OF_ORIGIN = '$dayOfOrigin' AND vol.TLC = '$TLC'
GROUP BY vol.TLC, vol.DAY_OF_ORIGIN;";
   
    $result = $conn->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}

$conn->close();
?>

