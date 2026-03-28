<?php
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");
include "../conn.php";
$data = json_decode(file_get_contents("php://input"), true);
if(!$data){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}
$name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars(trim($data['username']), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars(trim($data['email']), ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars(trim($data['password']), ENT_QUOTES, 'UTF-8');

if($name == "" || $username == "" || $email == "" || $password == ""){
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email format"
    ]);
    exit;
}

$checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$resultEmail = $checkEmail->get_result();

if($resultEmail->num_rows > 0){
    echo json_encode([
        "status" => "error",
        "message" => "Email already exists"
    ]);
    exit;
}

$checkUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
$checkUser->bind_param("s", $username);
$checkUser->execute();
$resultUser = $checkUser->get_result();

if($resultUser->num_rows > 0){
    echo json_encode([
        "status" => "error",
        "message" => "Username already taken"
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $username, $email, $hashedPassword);

if($stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "Registered successfully"
    ]);
}else{
    if($conn->errno == 1062){
        echo json_encode([
            "status" => "error",
            "message" => "Email or Username already exists"
        ]);
    }else{
        echo json_encode([
            "status" => "error",
            "message" => "Registration failed"
        ]);
    }
}

exit;
?>