<?php 
session_start();

// Check if matricule is set in the session
if (isset($_SESSION['matricule'])) {
    // Matricule is set, so the user is authenticated
    $matricule = $_SESSION['matricule'];
    echo json_encode(['authenticated' => true, 'matricule' => $matricule]);
} else {
    // Matricule is not set, meaning the user is not authenticated
    echo json_encode(['authenticated' => false]);
}
?>