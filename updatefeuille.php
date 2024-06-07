<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testingdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $error_message = "Connection failed: " . $conn->connect_error;
    http_response_code(500); // Internal Server Error
    echo json_encode(array("error" => $error_message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $dateString = isset($_GET['dayOfOrigin']) ? $_GET['dayOfOrigin'] : '';
    $dayOfOrigin = date("m/d/Y", strtotime($dateString));

    $TLC = isset($_GET['TLC']) ? $_GET['TLC'] : '';

    $data = array(); // Initialize $data as an empty array

    $sql = "
        SELECT
            v.DAY_OF_ORIGIN,
            v.TLC,
            v.FLIGHT_NO,
            v.EXPECTED_DEPARTURE_TIME,
            v.FROM_AIRPORT,
            v.TO_AIRPORT,
            v.OUT_TIME,
            v.IN_TIME,
            v.BLOCK_TIME,
            v.OFF_TIME,
            v.ON_TIME,
            v.FLIGHT_TIME,
            v.PREVIOUS_FUEL,
            v.ADDED_FUEL,
            v.DEPARTURE_FUEL,
            v.FUEL_USED,
            v.REMAINING_FUEL,
            v.DC,
            v.DL,
            f.id_FDL,
            f.day_time,
            f.night_time,
            f.desert_day_time,
            f.desert_night_time,
            f.tot_block_time,
            f.deadhead,
            f.tot_airborne,
            f.crew_name,
            c.CD,
            c.name,
            c.position,
            c.key
        FROM
            vol v
        LEFT JOIN
            feuille f ON v.DAY_OF_ORIGIN = f.DAY_OF_ORIGIN AND v.TLC = f.TLC
        LEFT JOIN
            crew c ON v.DAY_OF_ORIGIN = c.DAY_OF_ORIGIN AND v.TLC = c.TLC
        WHERE
            v.DAY_OF_ORIGIN = ?
            AND v.TLC = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $dayOfOrigin, $TLC);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        $error_message = "Error executing query: " . $conn->error;
        http_response_code(500); // Internal Server Error
        echo json_encode(array("error" => $error_message));
        exit;
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(array("message" => "No results found."));
    }

    // Close statement
    $stmt->close();

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

                $totBlockTime_seconds = strtotime($row['tot_block_time']) + strtotime($blockTime);
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

                // Update vol table
                $sql_vol = "UPDATE vol SET
                    EXPECTED_DEPARTURE_TIME='$expectedDepartureTime',
                    FROM_AIRPORT='$fromAirport',
                    TO_AIRPORT='$toAirport',
                    OUT_TIME='$outTime',
                    IN_TIME='$inTime',
                    BLOCK_TIME='$blockTime',
                    OFF_TIME='$offTime',
                    ON_TIME='$onTime',
                    FLIGHT_TIME='$flightTime',
                    PREVIOUS_FUEL='$previousFuel',
                    ADDED_FUEL='$addedFuel',
                    DEPARTURE_FUEL='$departureFuel',
                    FUEL_USED='$fuelUsed',
                    REMAINING_FUEL='$remainingFuel',
                    DC='$dc',
                    DL='$dl'
                    WHERE DAY_OF_ORIGIN='$dayOfOrigin' AND TLC='$TLC' AND FLIGHT_NO='$flightNo'";

                if (!$conn->query($sql_vol)) {
                    throw new Exception("Error updating vol table: " . $conn->error);
                }

                // Update feuille table
                $sql_feuille = "UPDATE feuille SET
                    day_time='$dayTime',
                    night_time='$nightTime',
                    desert_day_time='$desertDayTime',
                    desert_night_time='$desertNightTime',
                    tot_block_time='$totBlockTime',
                    deadhead='$deadhead',
                    tot_airborne='$totAirborne',
                    crew_name='$crewName'
                    WHERE DAY_OF_ORIGIN='$dayOfOrigin' AND TLC='$TLC' ";

                if (!$conn->query($sql_feuille)) {
                    throw new Exception("Error updating feuille table: " . $conn->error);
                }

                // Update crew table if necessary
                // For this example, we're updating based on the assumption that crew_name is unique and can be used to identify the record
                $sql_crew = "UPDATE crew SET
                    CD='$dc',
                    name='$crewName',
                    position='$row[position]',
                    key='$row[key]'
                    WHERE DAY_OF_ORIGIN='$dayOfOrigin' AND TLC='$TLC'";

                if (!$conn->query($sql_crew)) {
                    throw new Exception("Error updating crew table: " . $conn->error);
                }

                $response[] = array("flightNo" => $flightNo, "status" => "updated");
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $response = array("status" => "error", "message" => $e->getMessage());
        }
    } else {
        $response = array("status" => "error", "message" => "Invalid input format");
    }

    echo json_encode($response);
}

// Close connection
$conn->close();
?>

