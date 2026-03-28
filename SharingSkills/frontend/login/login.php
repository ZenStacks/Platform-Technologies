<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../../assets/style/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="login-box">
    <h2>Welcome Back</h2>

    <div class="input-box">
        <input type="email" id="email" placeholder="Email">
    </div>

    <div class="input-box">
        <input type="password" id="password" placeholder="Password">
    </div>

    <button class="login-btn" onclick="login()">Login</button>

    <div class="footer">
        Don't have an account? <a href="register.php">Sign up</a>
    </div>
</div>

</body>
<script>
function login(){
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    if(email === "" || password === ""){
        Swal.fire({
            icon: 'warning',
            title: 'Please fill in all fields',
            showConfirmButton: false,
            timer: 2000
        });
        return;
    }

    fetch("../../backend/login/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ email, password })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success"){
            Swal.fire({
                icon: 'success',
                title: data.message || 'Login successful!',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didClose: () => {
                    window.location.href = "../../index.php";
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: data.message || 'Login failed',
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