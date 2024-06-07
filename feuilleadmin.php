<?php
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
    http_response_code(500); // Internal Server Error
    echo json_encode(array("error" => "Connection failed: " . $conn->connect_error));
    exit;
}

// Sanitize user inputs
$dayOfOrigin = isset($_GET['dayOfOrigin']) ? date("m/d/Y", strtotime($_GET['dayOfOrigin'])) : '';
$TLC = isset($_GET['TLC']) ? $_GET['TLC'] : '';

$data = array(); // Initialize $data as an empty array

$sql = "
SELECT DISTINCT
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
    vol AS v
JOIN
    feuille AS f ON v.TLC = f.TLC AND v.DAY_OF_ORIGIN = f.DAY_OF_ORIGIN
JOIN
    crew AS c ON v.TLC = c.TLC AND v.DAY_OF_ORIGIN = c.DAY_OF_ORIGIN
WHERE
    v.DAY_OF_ORIGIN = ?
    AND v.TLC = ?
GROUP BY
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
    c.key;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $dayOfOrigin, $TLC);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array("error" => "Error executing query: " . $stmt->error));
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

// Close connection
$conn->close();
?>
