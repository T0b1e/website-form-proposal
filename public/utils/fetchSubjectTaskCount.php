<?php

/* fetchSubjectTaskCount.php */

require_once '../../config/db.php'; 

// Set the content type to JSON
header('Content-Type: application/json');

// Check if 'subject_id' is provided in GET parameters
if (!isset($_GET['subject_id']) || empty($_GET['subject_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Subject ID is required.'
    ]);
    exit();
}

$subject_id = intval($_GET['subject_id']); // Sanitize input by converting to integer

try {
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

    // Prepare the SQL query to count tasks for the given subject_id
    $countQuery = "SELECT COUNT(*) as total FROM tasks WHERE subject_id = :subject_id";
    $stmt = $pdo->prepare($countQuery);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    $countResult = $stmt->fetch(PDO::FETCH_ASSOC);

    $total = $countResult['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'total' => $total
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching task count: ' . $e->getMessage()
    ]);
}
?>
