<?php
/**
 * fetchTaskID.php
 * Fetches a single task by its ID for editing purposes.
 */

require_once '../../config/db.php'; // Ensure the path is correct

// Set the content type to JSON
header('Content-Type: application/json');

// Check if 'id' is provided in GET parameters
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Task ID is required.'
    ]);
    exit();
}

$taskId = intval($_GET['id']); // Sanitize input by converting to integer

try {
    // Instantiate the Database class and establish the connection
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

    // Prepare the SQL query to fetch the task details
    $query = "
        SELECT 
            t.id,
            t.task,
            t.task_code,
            t.subject_id,
            sl.subject,
            t.responsible_agency,
            t.person_in_charge_id,
            pci.person_name,
            t.date_received_legal_office,
            t.date_received_responsible_officer,
            t.date_proposal,
            t.processing_duration_days,
            t.instructions,
            t.remarks
        FROM tasks t
        LEFT JOIN subject_lists sl ON t.subject_id = sl.id
        LEFT JOIN person_in_charge pci ON t.person_in_charge_id = pci.id
        WHERE t.id = :id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $taskId, PDO::PARAM_INT);
    $stmt->execute();
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task) {
        echo json_encode([
            'success' => true,
            'task' => $task
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Task not found.'
        ]);
    }
} catch (PDOException $e) {
    // Handle errors
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching task: ' . $e->getMessage()
    ]);
}
?>
