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
        // Check for presence of dayOfOrigin in GET request
        if(isset($_GET["dayOfOrigin"]) && isset($_GET["TLC"])) {
            // Safely retrieve values
            $dayOfOrigin = $_GET['dayOfOrigin'];
            $tlc=$_GET["TLC"];
            // Convert the date to the desired format
            $dateString = date("m/d/Y", strtotime($dayOfOrigin));
            // Prepare and execute the SQL query
            $sql = "SELECT DAY_OF_ORIGIN, FLIGHT_NO, EXPECTED_DEPARTURE_TIME, FROM_AIRPORT, TO_AIRPORT FROM vols WHERE DAY_OF_ORIGIN = '$dateString' and TLC='$tlc'";
        } else {
            // Handle case where parameter is not provided
            $sql = "SELECT DAY_OF_ORIGIN, FLIGHT_NO, EXPECTED_DEPARTURE_TIME, FROM_AIRPORT, TO_AIRPORT FROM vols";
        }
        break;

    case 'POST':
        $id = isset($_GET["id"]) ? $_GET["id"] : null;

        // Update existing flight
        $day_of_origin = $_POST["DAY_OF_ORIGIN"];
        $tlc = $_POST["TLC"];
        $flight_no = $_POST["FLIGHT_NO"];
        $expected_departure_time = $_POST["EXPECTED_DEPARTURE_TIME"];
        $from_airport = $_POST["FROM_AIRPORT"];
        $to_airport = $_POST["TO_AIRPORT"];
        $out_time = $_POST["OUT_TIME"];
        $in_time = $_POST["IN_TIME"];
        $block_time = $_POST["BLOCK_TIME"];
        $off_time = $_POST["OFF_TIME"];
        $on_time = $_POST["ON_TIME"];
        $flight_time = $_POST["FLIGHT_TIME"];
        $previous_fuel = $_POST["PREVIOUS_FUEL"];
        $added_fuel = $_POST["ADDED_FUEL"];
        $departure_fuel = $_POST["DEPARTURE_FUEL"];
        $fuel_used = $_POST["FUEL_USED"];
        $remaining_fuel = $_POST["REMAINING_FUEL"];

        if ($id) {
            $sql = "UPDATE vol SET DAY_OF_ORIGIN='$day_of_origin', TLC='$tlc', FLIGHT_NO='$flight_no', EXPECTED_DEPARTURE_TIME='$expected_departure_time', FROM_AIRPORT='$from_airport', TO_AIRPORT='$to_airport', OUT_TIME='$out_time', IN_TIME='$in_time', BLOCK_TIME='$block_time', OFF_TIME='$off_time', ON_TIME='$on_time', FLIGHT_TIME='$flight_time', PREVIOUS_FUEL='$previous_fuel', ADDED_FUEL='$added_fuel', DEPARTURE_FUEL='$departure_fuel', FUEL_USED='$fuel_used', REMAINING_FUEL='$remaining_fuel' WHERE id=$id";
        } elseif (isset($_GET["delete"])){
            $delete = $_GET['delete'];
            $sql = "DELETE FROM vol WHERE id = $delete";
        } else {
            // Insert new flight
            $sql = "INSERT INTO vol (DAY_OF_ORIGIN, TLC, FLIGHT_NO, EXPECTED_DEPARTURE_TIME, FROM_AIRPORT, TO_AIRPORT, OUT_TIME, IN_TIME, BLOCK_TIME, OFF_TIME, ON_TIME, FLIGHT_TIME, PREVIOUS_FUEL, ADDED_FUEL, DEPARTURE_FUEL, FUEL_USED, REMAINING_FUEL) VALUES ('$day_of_origin', '$tlc', '$flight_no', '$expected_departure_time', '$from_airport', '$to_airport', '$out_time', '$in_time', '$block_time', '$off_time', '$on_time', '$flight_time', '$previous_fuel', '$added_fuel', '$departure_fuel', '$fuel_used', '$remaining_fuel')";
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
    $output = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $output[] = $row;
    }
    echo json_encode($output);
} elseif ($method === 'POST') {
    echo json_encode($result);
} elseif ($method === 'DELETE') {
    echo mysqli_affected_rows($con);
}

$con->close();
?>