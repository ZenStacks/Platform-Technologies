<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

include "../conn.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

$email = htmlspecialchars(trim($data['email']), ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars(trim($data['password']), ENT_QUOTES, 'UTF-8');

if ($email === "" || $password === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, username, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email not registered"
    ]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Incorrect password"
    ]);
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['name'] = $user['name'];

echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "user" => [
        "id" => $user['id'],
        "username" => $user['username'],
        "name" => $user['name'],
        "email" => $user['email']
    ]
]);
exit;
?>