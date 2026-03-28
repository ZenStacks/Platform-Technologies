<?php
session_start();
include "../conn.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "You must be logged in to rate a skill.";
    exit;
}

$user_id = $_SESSION['user_id'];
$skill_id = isset($_POST['skill_id']) ? (int)$_POST['skill_id'] : null;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

// Validate input
if (!$skill_id || !$rating || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo "Invalid skill or rating.";
    exit;
}

// Check if user already rated this skill
$sql_check = "SELECT id FROM ratings WHERE user_id = ? AND skill_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $user_id, $skill_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $sql_update = "UPDATE ratings SET rating = ? WHERE user_id = ? AND skill_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("iii", $rating, $user_id, $skill_id);
    if ($stmt_update->execute()) {
        echo "Rating updated!";
    } else {
        http_response_code(500);
        echo "Failed to update rating.";
    }
} else {
    $sql_insert = "INSERT INTO ratings (user_id, skill_id, rating) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $user_id, $skill_id, $rating);
    if ($stmt_insert->execute()) {
        echo "Rating added!";
    } else {
        http_response_code(500);
        echo "Failed to add rating.";
    }
}
?>