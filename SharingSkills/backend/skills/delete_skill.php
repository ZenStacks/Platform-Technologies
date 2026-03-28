<?php
session_start();
include __DIR__ . '/../conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error', 'message'=>'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$skill_id = isset($_POST['skill_id']) ? (int)$_POST['skill_id'] : 0;

if ($skill_id <= 0) {
    echo json_encode(['status'=>'error', 'message'=>'Invalid skill ID']);
    exit;
}

// Optional: Check if the skill belongs to this user
$stmtCheck = $conn->prepare("SELECT pdf FROM skills WHERE id = ? AND user_id = ?");
$stmtCheck->bind_param("ii", $skill_id, $user_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows === 0) {
    echo json_encode(['status'=>'error', 'message'=>'Skill not found or not owned by you']);
    exit;
}

$skill = $resultCheck->fetch_assoc();

// Delete the PDF file if it exists
if (!empty($skill['pdf']) && file_exists(__DIR__ . '/../../assets/pdfs/' . $skill['pdf'])) {
    unlink(__DIR__ . '/../../assets/pdfs/' . $skill['pdf']);
}

// Delete the skill from database
$stmtDelete = $conn->prepare("DELETE FROM skills WHERE id = ? AND user_id = ?");
$stmtDelete->bind_param("ii", $skill_id, $user_id);

if ($stmtDelete->execute()) {
    echo json_encode(['status'=>'success', 'message'=>'Skill deleted successfully']);
} else {
    echo json_encode(['status'=>'error', 'message'=>'Failed to delete skill: ' . $stmtDelete->error]);
}