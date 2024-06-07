<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
// Check if data is received through POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve the selected date from the POST data
    $selectedDate = $_POST['selectedDate'];
   
    // You can perform any necessary processing with the received data here
   
    // For demonstration purposes, let's simply echo the received date
    echo "Received date: " . $selectedDate;
} else {
    // If the request method is not POST, return an error message
    http_response_code(405);
    echo "Method Not Allowed";
}
?>