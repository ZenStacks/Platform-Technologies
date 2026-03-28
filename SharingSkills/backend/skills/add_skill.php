<?php
session_start();
include __DIR__ . '/../conn.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(['status'=>'error','message'=>'Not logged in']); 
    exit;
}

$user_id = $_SESSION['user_id'];
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$category = (int)($_POST['category'] ?? 0);
$pdf = '';

if(isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0){
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['pdf']['tmp_name']);
    finfo_close($finfo);

    if($mime === 'application/pdf'){
        $pdf = time() . '_' . basename($_FILES['pdf']['name']);
        move_uploaded_file($_FILES['pdf']['tmp_name'], __DIR__ . '/../../assets/pdfs/' . $pdf);
    } else {
        echo json_encode(['status'=>'error','message'=>'Invalid file type']);
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO skills (user_id, title, description, category, pdf) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issis", $user_id, $title, $description, $category, $pdf);

if($stmt->execute()){
    echo json_encode(['status'=>'success','message'=>'Skill added successfully!']);
} else {
    echo json_encode(['status'=>'error','message'=>$stmt->error]);
}