<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../../assets/style/register.css?v=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="register-box">
    <h2>Create Account</h2>

    <div class="input-box">
        <input type="text" name="name" id="name" placeholder="Full Name" required>
    </div>

    <div class="input-box">
        <input type="text" name="username" id="username" placeholder="Username" required>
    </div>

    <div class="input-box">
        <input type="email" name="email" id="email" placeholder="Email" required>
    </div>

    <div class="input-box">
        <input type="password" name="password" id="password" placeholder="Password" required>
    </div>

    <div class="input-box">
        <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
    </div>

    <button class="register-btn" onclick="register()">Register</button>

    <div class="footer">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
<script>
function register(){
    const name = document.getElementById("name").value;
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    if(name === "" || username === "" || email === "" || password === "" || confirmPassword === ""){
        Swal.fire({
            icon: 'warning',
            title: 'Please fill in all fields',
            showConfirmButton: false,
            timer: 2000
        });
        return;
    }

    if(password !== confirmPassword){
        Swal.fire({
            icon: 'error',
            title: 'Passwords do not match',
            showConfirmButton: false,
            timer: 2000
        });
        return;
    }

    fetch("../../backend/login/register.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            name: name,
            username: username,
            email: email,
            password: password
        })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);
        if(data.status === "success"){
            Swal.fire({
                icon: 'success',
                title: data.message || 'Registered successfully',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didClose: () => {
                    window.location.href = "login.php";
                }
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: data.message || 'Registration failed',
                showConfirmButton: false,
                timer: 2000
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Something went wrong',
            showConfirmButton: false,
            timer: 2000
        });
    });
}
</script>
</html>