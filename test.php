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

// Check if matricule is set in the session
if(isset($_SESSION['matricule'])) {
    // Retrieve matricule from session
    $TLC = $_SESSION['matricule'];

    // Now you can use $matricule to fetch additional data or perform other operations
    // For example, you can use it to query the database and fetch additional user data
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
                    $TLC = $_SESSION['matricule'];
                    $flightNo = $row['FLIGHT_NO'];
                    $expectedDepartureTime = $row['EXPECTED_DEPARTURE_TIME'];
                    $fromAirport = $row['FROM_AIRPORT'];
                    $toAirport = $row['TO_AIRPORT'];
                    $outTime = $row['OUT_TIME'];
                    $inTime = $row['IN_TIME'];
                    $offTime = $row['OFF_TIME'];
                    $onTime = $row['ON_TIME'];
               
                    $out = new DateTime($row['OUT_TIME']);
                    $in = new DateTime($row['IN_TIME']);
                    $off = new DateTime($row['OFF_TIME']);
                    $on = new DateTime($row['ON_TIME']);
               
                    $blockTime = $in->diff($out)->format('%H:%I:%S');
                    $flightTime = $on->diff($off)->format('%H:%I:%S');
    
                    $totBlockTime_seconds = strtotime($totBlockTime) + strtotime($blockTime);
                    $totBlockTime = gmdate('H:i:s', $totBlockTime_seconds);
               
                    $previousFuel = $row['PREVIOUS_FUEL'];
                    $addedFuel = $row['ADDED_FUEL'];
                    $departureFuel = $row['DEPARTURE_FUEL'];
                    $fuelUsed = $row['FUEL_USED'];
                    $remainingFuel = $row['REMAINING_FUEL'];
               
                    $dayTime = $row['day_time'];
                    $nightTime = $row['night_time'];
                    $desertDayTime = $row['desert_day_time'];
                    $desertNightTime = $row['desert_night_time'];
                    $deadhead = $row['deadhead'];
                    $totAirborne = $row['tot_airborne'];
               
                    $crewName = $row['crew_name'];
                    $dc = isset($row['DC']) ? $row['DC'] : null;
                    $dl = isset($row['DL']) ? $row['DL'] : null;
    
                    // Check if the combination of DAY_OF_ORIGIN, TLC, FLIGHT_NO already exists in vol table
                    $sql_check_vol = "SELECT * FROM vol WHERE DAY_OF_ORIGIN = '$dayOfOrigin' AND TLC = '$TLC' AND FLIGHT_NO = '$flightNo'";
                    $result_vol = $conn->query($sql_check_vol);
    
                    // Check if the combination of FLIGHT_NO, DAY_OF_ORIGIN, id_FDL already exists in feuille table
                    $sql_check_feuille = "SELECT * FROM feuille WHERE TLC = '$TLC' AND DAY_OF_ORIGIN = '$dayOfOrigin' AND id_FDL = '$id_FDL'";
                    $result_feuille = $conn->query($sql_check_feuille);
    
                    // If the entries do not exist, proceed with insertion
                    if ($result_vol->num_rows == 0 && $result_feuille->num_rows == 0) {
                        // Insert into vol table
                        $sql_vol = "INSERT INTO vol (DAY_OF_ORIGIN, TLC, FLIGHT_NO, EXPECTED_DEPARTURE_TIME, FROM_AIRPORT, TO_AIRPORT, OUT_TIME, IN_TIME, BLOCK_TIME, OFF_TIME, ON_TIME, FLIGHT_TIME, PREVIOUS_FUEL, ADDED_FUEL, DEPARTURE_FUEL, FUEL_USED, REMAINING_FUEL, DC, DL)
                                    VALUES ('$dayOfOrigin', '$TLC', '$flightNo', '$expectedDepartureTime', '$fromAirport', '$toAirport', '$outTime', '$inTime', '$blockTime', '$offTime', '$onTime', '$flightTime', '$previousFuel', '$addedFuel', '$departureFuel', '$fuelUsed', '$remainingFuel', '$dc', '$dl')";
       
                        // Execute the SQL query for vol
                        if (!$conn->query($sql_vol)) {
                            throw new Exception("Error inserting into vol table: " . $conn->error);
                        }
    
                        // Insert into feuille table
                        $sql_feuille = "INSERT INTO feuille (DAY_OF_ORIGIN, TLC, day_time, night_time, desert_day_time, desert_night_time, tot_block_time, deadhead, tot_airborne, crew_name)
                                        VALUES ('$dayOfOrigin', '$TLC', '$dayTime', '$nightTime', '$desertDayTime', '$desertNightTime', '$totBlockTime', '$deadhead', '$totAirborne', '$crewName')";
       
                        // Execute the SQL query for feuille
                        $result_feuille = $conn->query($sql_feuille);
    
                        // Check if insertion was successful and no rows were affected previously
                        if ($result_feuille && $conn->affected_rows == 1) {
                            // Insertion successful
                            $response[] = array("flightNo" => $flightNo, "status" => "success");
                        } else {
                            // Insertion failed or row already exists
                            $response[] = array("flightNo" => $flightNo, "status" => "failed");
                        }
                    } else {
                        // If the entries already exist
                        $response[] = array("flightNo" => $flightNo, "status" => "skipped");
                    }
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
        $dateString = date("m/d/Y", strtotime($dayOfOrigin));
        $TLC = isset($_SESSION['matricule']) ? $_SESSION['matricule'] : '';
    
        $sql = "SELECT * FROM vols WHERE DAY_OF_ORIGIN = '$dateString' AND TLC = '$TLC'";
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

} else {
    session_destroy();

    // Handle case when matricule is not set in the session
}
?>

