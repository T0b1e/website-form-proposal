<?php

/* fetchTasks.php */

require_once(__DIR__ . '/../../config/db.php');

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

header('Content-Type: application/json');

// Retrieve GET parameters for pagination and search
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$searchCriteria = isset($_GET['searchCriteria']) ? $_GET['searchCriteria'] : '';
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';

// Validate search criteria to prevent SQL injection
$validSearchFields = ['task_code', 'task', 'subject', 'agency', 'person_in_charge', 'instructions', 'remarks'];
if ($searchCriteria && !in_array($searchCriteria, $validSearchFields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid search criteria.'
    ]);
    exit();
}

try {
    $query = "
        SELECT 
            t.id,
            t.task,
            t.task_code, -- Included task_code
            t.date_proposal, -- New Field
            sl.subject,
            t.responsible_agency,
            pci.person_name,
            t.instructions,
            t.remarks,
            t.date_received_legal_office,
            t.date_received_responsible_officer,
            t.processing_duration_days
        FROM tasks t
        LEFT JOIN subject_lists sl ON t.subject_id = sl.id
        LEFT JOIN person_in_charge pci ON t.person_in_charge_id = pci.id
    ";

    $params = [];

    // Add WHERE clause if searching
    if ($searchCriteria && $searchTerm) {
        switch ($searchCriteria) {
            case 'task_code':
                $query .= " WHERE t.task_code LIKE :searchTerm";
                $params[':searchTerm'] = "%$searchTerm%";
                break;
            case 'task':
                $query .= " WHERE t.task LIKE :searchTerm";
                $params[':searchTerm'] = "%$searchTerm%";
                break;
            case 'subject':
                $query .= " WHERE sl.subject LIKE :searchTerm";
                $params[':searchTerm'] = "%$searchTerm%";
                break;
            case 'agency':
                $query .= " WHERE t.responsible_agency LIKE :searchTerm";
                $params[':searchTerm'] = "%$searchTerm%";
                break;
            case 'person_in_charge':
                $query .= " WHERE pci.person_name LIKE :searchTerm";
                $params[':searchTerm'] = "%$searchTerm%";
                break;
            case 'instructions':
                $query .= " WHERE t.instructions LIKE :searchTerm";
                $params[':searchTerm'] = "%$searchTerm%";
                break;
            case 'remarks':
                $query .= " WHERE t.remarks LIKE :searchTerm";
                $params[':searchTerm'] = "%$searchTerm%";
                break;
            default:
                break;
        }
    }

    // Add ORDER BY and LIMIT clauses
    $query .= " ORDER BY t.id DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);

    // Bind parameters
    foreach ($params as $key => &$value) {
        // Treat all search parameters as strings
        $stmt->bindParam($key, $value, PDO::PARAM_STR);
    }

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    if ($searchCriteria && $searchTerm) {
        switch ($searchCriteria) {
            case 'task_code':
                $countQuery = "SELECT COUNT(*) as total FROM tasks t 
                               WHERE t.task_code LIKE :searchTerm";
                break;
            case 'task':
                $countQuery = "SELECT COUNT(*) as total FROM tasks t 
                               WHERE t.task LIKE :searchTerm";
                break;
            case 'subject':
                $countQuery = "SELECT COUNT(*) as total FROM tasks t 
                               LEFT JOIN subject_lists sl ON t.subject_id = sl.id
                               WHERE sl.subject LIKE :searchTerm";
                break;
            case 'agency':
                $countQuery = "SELECT COUNT(*) as total FROM tasks t 
                               WHERE t.responsible_agency LIKE :searchTerm";
                break;
            case 'person_in_charge':
                $countQuery = "SELECT COUNT(*) as total FROM tasks t 
                               LEFT JOIN person_in_charge pci ON t.person_in_charge_id = pci.id
                               WHERE pci.person_name LIKE :searchTerm";
                break;
            case 'instructions':
                $countQuery = "SELECT COUNT(*) as total FROM tasks t 
                               WHERE t.instructions LIKE :searchTerm";
                break;
            case 'remarks':
                $countQuery = "SELECT COUNT(*) as total FROM tasks t 
                               WHERE t.remarks LIKE :searchTerm";
                break;
            default:
                $countQuery = "SELECT COUNT(*) as total FROM tasks";
                break;
        }

        $countStmt = $pdo->prepare($countQuery);
        // Bind with wildcards for all search criteria
        $searchTermLike = "%$searchTerm%";
        $countStmt->bindParam(':searchTerm', $searchTermLike, PDO::PARAM_STR);
    } else {
        $countQuery = "SELECT COUNT(*) as total FROM tasks";
        $countStmt = $pdo->prepare($countQuery);
    }

    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $total = $countResult['total'];

    echo json_encode([
        'success' => true,
        'tasks' => $tasks,
        'total' => $total
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching tasks: ' . $e->getMessage()
    ]);
}
?>
