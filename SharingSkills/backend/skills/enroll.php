<?php
session_start();
include __DIR__ . '/../conn.php'; // adjust path if needed

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status"=>"error","message"=>"Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$skill_id = $_POST['skill_id'] ?? null;

if(!$skill_id){
    echo json_encode(["status"=>"error","message"=>"Invalid skill"]);
    exit;
}

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT IGNORE INTO enrollments (user_id, skill_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $skill_id);
$stmt->execute();

echo json_encode(["status"=>"success","message"=>"Course enrolled successfully!"]);