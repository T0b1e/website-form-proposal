<?php

/* editTask.php */

require_once '../../config/db.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Retrieve and sanitize POST data
$taskId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$task = isset($_POST['task']) ? trim($_POST['task']) : '';
$subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : null;
$responsible_agency = isset($_POST['responsible_agency']) ? trim($_POST['responsible_agency']) : '';
$person_in_charge_id = isset($_POST['person_in_charge_id']) ? intval($_POST['person_in_charge_id']) : null;
$date_received_legal_office = isset($_POST['date_received_legal_office']) ? $_POST['date_received_legal_office'] : null;
$date_received_responsible_officer = isset($_POST['date_received_responsible_officer']) ? $_POST['date_received_responsible_officer'] : null;
$date_proposal = isset($_POST['date_proposal']) ? $_POST['date_proposal'] : null; // New Field
$instructions = isset($_POST['instructions']) ? trim($_POST['instructions']) : '';
$processing_duration_days = isset($_POST['processing_duration_days']) ? intval($_POST['processing_duration_days']) : 0;
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

// Validate required fields
$errors = [];

if ($taskId <= 0) {
    $errors[] = 'Invalid task ID.';
}

if (empty($task)) {
    $errors[] = 'Task title is required.';
}

if (is_null($subject_id) || $subject_id <= 0) {
    $errors[] = 'Valid subject is required.';
}

if (empty($responsible_agency)) {
    $errors[] = 'Responsible agency is required.';
}

if (is_null($person_in_charge_id) || $person_in_charge_id <= 0) {
    $errors[] = 'Valid person in charge is required.';
}

if (empty($date_received_legal_office)) {
    $errors[] = 'Date received from legal office is required.';
}

if (empty($date_received_responsible_officer)) {
    $errors[] = 'Date received from responsible officer is required.';
}

if (empty($instructions)) {
    $errors[] = 'Instructions are required.';
}

if ($processing_duration_days < 0) {
    $errors[] = 'Processing duration days cannot be negative.';
}

if (empty($date_proposal)) { // Validate the new field
    $errors[] = 'Date of proposal is required.';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit();
}

try {
    // Instantiate the Database 
    $database = new Database();
    $pdo = $database->connect();

    if (!$pdo) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed.'
        ]);
        exit();
    }

    // Prepare the SQL UPDATE statement (including date_proposal)
    $updateQuery = "
        UPDATE tasks SET
            task = :task,
            subject_id = :subject_id,
            responsible_agency = :responsible_agency,
            person_in_charge_id = :person_in_charge_id,
            date_received_legal_office = :date_received_legal_office,
            date_received_responsible_officer = :date_received_responsible_officer,
            date_proposal = :date_proposal, -- New Field
            instructions = :instructions,
            processing_duration_days = :processing_duration_days,
            remarks = :remarks
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':task', $task, PDO::PARAM_STR);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->bindParam(':responsible_agency', $responsible_agency, PDO::PARAM_STR);
    $stmt->bindParam(':person_in_charge_id', $person_in_charge_id, PDO::PARAM_INT);
    $stmt->bindParam(':date_received_legal_office', $date_received_legal_office, PDO::PARAM_STR);
    $stmt->bindParam(':date_received_responsible_officer', $date_received_responsible_officer, PDO::PARAM_STR);
    $stmt->bindParam(':date_proposal', $date_proposal, PDO::PARAM_STR); // New Field
    $stmt->bindParam(':instructions', $instructions, PDO::PARAM_STR);
    $stmt->bindParam(':processing_duration_days', $processing_duration_days, PDO::PARAM_INT);
    $stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
    $stmt->bindParam(':id', $taskId, PDO::PARAM_INT);

    $result = $stmt->execute();

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update the task.'
        ]);
    }
} catch (PDOException $e) {
    // Handle errors
    echo json_encode([
        'success' => false,
        'message' => 'Error updating task: ' . $e->getMessage()
    ]);
}
?>
