
<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testingdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method === 'OPTIONS') {
    exit();
}

switch ($request_method) {
    case 'GET':
        $dayOfOrigin = isset($_GET['dayOfOrigin']) ? $_GET['dayOfOrigin'] : null;
        
        $TLC = isset($_GET['TLC']) ? $_GET['TLC'] : null;
        $crew_id = isset($_GET['crew_id']) ? $_GET['crew_id'] : null;
        selectItem($dayOfOrigin, $TLC, $crew_id);
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['crew_id'])) {
            updateItem($data);
        } else {
            addItem($data);
        }
        break;
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        deleteItem($data['crew_id']);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
function selectItem($dayOfOrigin, $tlc) {
    global $conn;
    $dayOfOrigin = isset($_GET['dayOfOrigin']) ? $_GET['dayOfOrigin'] : '';
    $dateString = date("m/d/Y", strtotime($dayOfOrigin));
    $TLC = isset($_GET['TLC']) ? $_GET['TLC'] : '';

    $sql = "SELECT * FROM crew WHERE DAY_OF_ORIGIN=? AND TLC=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $dateString, $tlc); // Assuming both DAY_OF_ORIGIN and TLC are strings
    $stmt->execute();
    $result = $stmt->get_result();

    $items = array();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
    $stmt->close();
}


function addItem($data) {
    global $conn;
    $dateString = $data['dayOfOrigin']; // Assuming 'dayOfOrigin' is the name of the input field where the user inserts the date

    // Convert the user input date to the desired format
    $dayOfOrigin= date("m/d/Y", strtotime($dateString));
    $TLC = $data['TLC'];
    $CD = $data['CD'];
    $name = $data['name'];
    $position = $data['position'];
    $key = $data['key'];

    $sql_check = "SELECT * FROM crew WHERE DAY_OF_ORIGIN=? AND TLC=? AND crew_id=?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ssi", $dayOfOrigin, $TLC, $data['crew_id']);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo json_encode(["message" => "Record with given DAY_OF_ORIGIN, TLC, and id already exists"]);
        $stmt_check->close();
        return;
    }
    $stmt_check->close();

    $sql = "INSERT INTO crew (DAY_OF_ORIGIN, TLC, CD, name, position, `key`)
    VALUES ('$dayOfOrigin', '$TLC', '$CD', '$name', '$position', '$key')";

if ($conn->query($sql) === TRUE) {
echo json_encode(["message" => "Item added successfully"]);
} else {
echo json_encode(["message" => "Error: " . $conn->error]);
}

$conn->close();

   
}

function updateItem($data) {
    global $conn;
    $crew_id = $data['crew_id'];
    $CD = $data['CD'];
    $name = $data['name'];
    $position = $data['position'];
    $key = $data['key'];

    $sql = "UPDATE crew SET CD=?, name=?, position=?, `key`=? WHERE crew_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $CD, $name, $position, $key, $crew_id);

    if ($stmt->execute() === TRUE) {
        echo json_encode(["message" => "Item updated successfully"]);
    } else {
        echo json_encode(["message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
}

function deleteItem($crew_id) {
    global $conn;
    $sql = "DELETE FROM crew WHERE crew_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $crew_id);

    if ($stmt->execute() === TRUE) {
        echo json_encode(["message" => "Item deleted successfully"]);
    } else {
        echo json_encode(["message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
}
?>

