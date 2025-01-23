<?php
/**
 * deleteTask.php
 * Handles the deletion of a task.
 */

require_once '../../config/database.php'; // Ensure the path is correct

// Instantiate the Database class and establish the connection
$database = new Database();
$pdo = $database->connect();

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Retrieve and sanitize the task ID
$taskId = isset($_POST['id']) ? (int)$_POST['id'] : null;

if (!$taskId) {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่มี ID ของงานที่ต้องการลบ.'
    ]);
    exit();
}

try {
    // Optionally, delete associated files if any
    $stmtFile = $pdo->prepare("SELECT file_name FROM tasks WHERE id = :id");
    $stmtFile->execute([':id' => $taskId]);
    $file = $stmtFile->fetch(PDO::FETCH_ASSOC);

    if ($file && $file['file_name']) {
        $filePath = '../../uploads/' . $file['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file
        }
    }

    // Delete the task
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->execute([':id' => $taskId]);

    echo json_encode([
        'success' => true,
        'message' => 'ลบงานสำเร็จ!'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการลบงาน: ' . $e->getMessage()
    ]);
}
?>
