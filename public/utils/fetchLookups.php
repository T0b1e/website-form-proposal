<?php

/* fetchLookups.php */

require_once '../../config/db.php'; 

// Instantiate the Database 
$database = new Database();
$pdo = $database->connect();

// Check if the connection was successful
if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
    exit();
}

header('Content-Type: application/json');

try {
    // Fetch Subjects from `subject_lists` table
    $stmtSubjects = $pdo->query("SELECT id, subject FROM subject_lists ORDER BY subject ASC");
    $subjects = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Persons in Charge
    $stmtPersons = $pdo->query("SELECT id, person_name FROM person_in_charge ORDER BY person_name ASC");
    $personsInCharge = $stmtPersons->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'subjects' => $subjects,
        'personsInCharge' => $personsInCharge
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching lookup data: ' . $e->getMessage()
    ]);
}
?>
