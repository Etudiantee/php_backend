<?php

header("Access-Control-Allow-Origin: *");

// Check if the request method is GET (to fetch contact details) or DELETE
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Extract matricule from the request query string
    $matricule = $_GET['matricule'];

    // Your database connection code
    $servername = "localhost";
    $username = "root"; // Your MySQL username
    $password = ""; // Your MySQL password
    $dbname = "testingdb"; // Your MySQL database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare a SELECT statement to fetch contact details
    $sql = "SELECT * FROM personnel WHERE matricule = ?";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricule);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if contact exists
    if ($result->num_rows > 0) {
        // Fetch and return contact details
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        // If contact does not exist, return an empty response
        echo json_encode([]);
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Extract matricule from the request query string
    $matricule = $_GET['matricule'];

    // Your database connection code
    $servername = "localhost";
    $username = "root"; // Your MySQL username
    $password = ""; // Your MySQL password
    $dbname = "testingdb"; // Your MySQL database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare a DELETE statement
    $sql = "DELETE FROM personnel WHERE matricule = ?";

    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricule);

    // Execute the statement
    if ($stmt->execute() === TRUE) {
        // If deletion is successful, return a success message
        echo "Contact deleted successfully";
    } else {
        // If deletion fails, return an error message
        echo "Error deleting contact: " . $conn->error;
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
} else {
    // If the request method is not GET or DELETE, return an error message
    echo "Invalid request method";
}
?>
