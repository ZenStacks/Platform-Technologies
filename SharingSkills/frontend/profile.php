<?php
session_start();
include "../backend/conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../frontend/login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, name, email, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt2 = $conn->prepare("SELECT * FROM skills WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$skills = $result2->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dashboard</title>
    <link rel="stylesheet" href="../assets/style/profile.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="navbar">
    <button onclick="goHome()">⬅ Back to Home</button>
    <h2 style="margin:0; color:white;">Profile Information</h2>
</div>
<div class="sidebar">
    <button onclick="showSection('profile')">👤 Profile</button>
    <button onclick="showSection('skills')">📚 My Skills</button>
    <button onclick="showSection('add')">➕ Add Skill</button>
</div>

<div class="content">
    <div id="profile" class="section">
        <h2>Profile Info</h2>
        <div class="input-box">
            <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Name">
        </div>
        <div class="input-box">
            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email">
        </div>
        <button class="primary" onclick="updateProfile()">Save</button>
    </div>

    <div id="skills" class="section" style="display:none;">
        <h2>My Skills</h2>
        <div id="skillList">
            <?php foreach($skills as $skill): ?>
                <div class="skill-item" style="margin-bottom:10px;">
                    <strong><?php echo htmlspecialchars($skill['title']); ?></strong> 
                    <em>(<?php echo htmlspecialchars($skill['category']); ?>)</em><br>
                    <small><?php echo htmlspecialchars($skill['description']); ?></small><br>
                    <?php if($skill['pdf']): ?>
                        <a href="../../assets/pdfs/<?php echo $skill['pdf']; ?>" target="_blank">View PDF</a>
                    <?php endif; ?>
                    <button onclick="deleteSkill(<?php echo $skill['id']; ?>)" style="color:red; margin-left:10px;">Delete</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="add" class="section" style="display:none;">
        <h2>Add Skill</h2>
        <form id="addSkillForm" enctype="multipart/form-data" method="POST">
            <div class="input-box">
                <input type="text" name="title" placeholder="Skill Title" required>
            </div>
            <div class="input-box">
                <textarea name="description" placeholder="Description" required></textarea>
            </div>
            <div class="input-box">
                <label for="category">Select Category</label>
                <select name="category" id="category" required>
                    <option value="">--Choose Category--</option>
                    <option value="1">Programming</option>
                    <option value="2">Cooking</option>
                    <option value="3">Graphic Design</option>
                </select>
            </div>
            <div class="input-box">
                <label for="pdfFile">Upload PDF</label>
                <input type="file" name="pdf" accept=".pdf">
            </div>
            <button type="submit" class="primary">Add Skill</button>
        </form>
    </div>
</div>

<script>
// Toggle sections
function showSection(section){
    document.getElementById("profile").style.display = "none";
    document.getElementById("skills").style.display = "none";
    document.getElementById("add").style.display = "none";
    document.getElementById(section).style.display = "block";
}

// Go back home
function goHome(){
    window.location.href = "../index.php";
}

// AJAX: Update profile info without reloading
function updateProfile(){
    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;

    if(!name || !email){
        alert("Fill all fields");
        return;
    }

    fetch('../backend/users/update_profile.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email)
    })
    .then(res => res.text())
    .then(data => alert(data))
    .catch(err => console.error(err));
}
function deleteSkill(skillId){
    Swal.fire({
        title: 'Are you sure?',
        text: "This skill will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if(result.isConfirmed){
            fetch('../backend/skills/delete_skill.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'skill_id=' + encodeURIComponent(skillId)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success'){
                    Swal.fire('Deleted!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Something went wrong!', 'error');
            });
        }
    });
}
document.getElementById('addSkillForm').addEventListener('submit', function(e){
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch('../backend/skills/add_skill.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            Swal.fire({
                icon: 'success',
                title: 'Skill Added!',
                text: data.message,
                confirmButtonText: 'OK'
            }).then(() => {
                form.reset();
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Something went wrong!'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong!'
        });
    });
});
</script>
</body>
</html>