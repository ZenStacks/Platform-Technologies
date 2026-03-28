<?php
session_start();
include "backend/conn.php";

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: ../login/login.php");
    exit();
}

// Fetch all categories from DB (or hardcode them)
$categories = [
    1 => "Programming",
    2 => "Cooking",
    3 => "Graphic Design"
];

// Count enrolled users per category
$categoryCounts = [];
foreach($categories as $id => $name){
    $sql = "SELECT COUNT(*) AS total 
            FROM enrollments e 
            JOIN skills s ON e.skill_id = s.id 
            WHERE s.category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $categoryCounts[$id] = $row['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillShare Home</title>
    <link rel="stylesheet" href="assets/style/home.css?v=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="navbar">
    <div class="logo">SkillShare</div>
    <div class="nav-links">
        <button onclick="goToProfile()">Profile</button>
        <button onclick="logout()">Logout</button>
    </div>
</div>

<div class="header">
    <h1>Learn & Share Skills</h1>
    <p>Find people who can teach you anything</p>
</div>

<div class="container" id="skillsContainer">
    <?php foreach($categories as $id => $name): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($name); ?></h3>
            <p><?php echo $categoryCounts[$id]; ?> people enrolled</p>
            <button onclick="viewCategory(<?php echo $id; ?>)">View Courses</button>
        </div>
    <?php endforeach; ?>
</div>

<script>
function logout(){
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if(result.isConfirmed){
            fetch('backend/login/logout.php')
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Logged out!',
                    text: 'You have been logged out successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "frontend/login/login.php";
                });
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Logout failed!', 'error');
            });
        }
    });
}
function goToProfile(){
    window.location.href = "frontend/profile.php";
}
function viewCategory(categoryId){
    window.location.href = "frontend/skills/course.php?category=" + categoryId;
}
</script>
</body>
</html>