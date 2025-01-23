<?php
/* uploadTask.php */

session_start();
require_once '../../config/db.php'; 

header('Content-Type: application/json');

// Instantiate the Database 
$database = new Database();
$pdo = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $taskCode = $_POST['task_code'] ?? null;
    $subjectId = $_POST['subject_id'] ?? null;
    $responsibleAgency = $_POST['responsible_agency'] ?? null;
    $personInCharge = $_POST['person_in_charge_id'] ?? null;
    $dateReceivedLegalOffice = $_POST['date_received_legal_office'] ?? null;
    $dateReceivedResponsibleOfficer = $_POST['date_received_responsible_officer'] ?? null;
    $dateProposal = $_POST['date_proposal'] ?? null; 
    $instructions = trim($_POST['instructions'] ?? '');
    $processingDurationDays = intval($_POST['processing_duration_days'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');

    if (
        empty($title) ||
        empty($taskCode) ||
        empty($subjectId) ||
        empty($responsibleAgency) ||
        empty($personInCharge) ||
        empty($dateReceivedLegalOffice) ||
        empty($dateReceivedResponsibleOfficer) ||
        empty($dateProposal) ||
        empty($instructions) 
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน.'
        ]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO tasks (
                id,
                task,
                task_code,
                subject_id,
                responsible_agency,
                person_in_charge_id,
                date_received_legal_office,
                date_received_responsible_officer,
                date_proposal,
                instructions,
                processing_duration_days,
                remarks
            ) VALUES (
                :id,
                :task,
                :task_code,
                :subject_id,
                :responsible_agency,
                :person_in_charge_id,
                :date_received_legal_office,
                :date_received_responsible_officer,
                :date_proposal,
                :instructions,
                :processing_duration_days,
                :remarks
            )
        ");

        $stmt->execute([
            ':id' => $taskId,
            ':task' => $title,
            ':task_code' => $taskCode,
            ':subject_id' => $subjectId,
            ':responsible_agency' => $responsibleAgency,
            ':person_in_charge_id' => $personInCharge,
            ':date_received_legal_office' => $dateReceivedLegalOffice,
            ':date_received_responsible_officer' => $dateReceivedResponsibleOfficer,
            ':date_proposal' => $dateProposal,
            ':instructions' => $instructions,
            ':processing_duration_days' => $processingDurationDays,
            ':remarks' => $remarks
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully.',
            'task_code' => $taskCode
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to insert task: ' . $e->getMessage()
        ]);
    }
    exit();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}
?>
