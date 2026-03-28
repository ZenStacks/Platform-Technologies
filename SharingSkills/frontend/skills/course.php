<?php
session_start();
include "../../backend/conn.php";

// Get category ID from URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 1;

// Categories names
$categories = [
    1 => "Programming",
    2 => "Cooking",
    3 => "Graphic Design"
];
$category_name = $categories[$category_id] ?? "Unknown";

// Fetch courses in this category
$sql = "SELECT * FROM skills WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category_name; ?> Courses</title>
    <link rel="stylesheet" href="../../assets/style/programming.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="whole-main-container">
    <div class="main-container">
        <div class="navigation">
            <i class="bi bi-arrow-left" onclick="window.history.back()"></i>
            <h2><?php echo $category_name; ?> Courses</h2>
        </div>

        <div class="courses">
            <?php if(count($courses) > 0): ?>
                <?php foreach($courses as $course): ?>
                    <?php
                        $sql_enrolled = "SELECT COUNT(*) AS total FROM enrollments WHERE skill_id = ?";
                        $stmt2 = $conn->prepare($sql_enrolled);
                        $stmt2->bind_param("i", $course['id']);
                        $stmt2->execute();
                        $result2 = $stmt2->get_result();
                        $row = $result2->fetch_assoc();
                        $totalEnrolled = $row['total'] ?? 0;

                        $sql_rating = "SELECT ROUND(AVG(rating),0) AS avg_rating FROM ratings WHERE skill_id = ?";
                        $stmt3 = $conn->prepare($sql_rating);
                        $stmt3->bind_param("i", $course['id']);
                        $stmt3->execute();
                        $result3 = $stmt3->get_result();
                        $row3 = $result3->fetch_assoc();
                        $rating = $row3['avg_rating'] ?? 0;
                    ?>
                    <div class="courses-display" style="margin-bottom:20px; border:1px solid #ddd; padding:15px; border-radius:10px;">
                        <div class="title">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars($course['description']); ?></p>
                            <p><?php echo $totalEnrolled; ?> people enrolled</p>

                            <div class="stars">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <button type="button" class="star" data-skill="<?php echo $course['id']; ?>">
                                        <?php echo ($i <= $rating) ? "&#9733;" : "&#9734;"; ?>
                                    </button>
                                <?php endfor; ?>
                            </div>

                            <?php if(!empty($course['pdf'])): ?>
                                <p><a href="../../assets/pdfs/<?php echo $course['pdf']; ?>" target="_blank">Download PDF</a></p>
                            <?php endif; ?>
                        </div>

                       
                        <form class="enrollForm">
                            <input type="hidden" name="skill_id" value="<?php echo $course['id']; ?>">
                            <button type="submit" class="btn btn-primary">Enroll</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No courses found in this category.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.courses-display').forEach(courseContainer => {
    const stars = courseContainer.querySelectorAll('.star');
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const skillId = star.getAttribute('data-skill');

            stars.forEach((s, i) => {
                s.innerHTML = (i <= index) ? "&#9733;" : "&#9734;";
            });

            fetch('../../backend/skills/rate.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'skill_id=' + skillId + '&rating=' + (index + 1)
            })
            .then(res => res.text())
            .then(msg => {
                Swal.fire({
                    icon: 'success',
                    title: 'Rated!',
                    text: msg,
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });
    });
});

document.querySelectorAll('.enrollForm').forEach(form => {
    form.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(form);

        fetch('../../backend/skills/enroll.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === "success"){
                Swal.fire({
                    icon: 'success',
                    title: 'Enrolled!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: data.message
                });
            }
        });
    });
});

</script>
</body>
</html>


