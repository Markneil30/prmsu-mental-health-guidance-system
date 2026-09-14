<?php
session_start();

if(isset($_SESSION['user_id']) && $_SESSION['role']=="student"){
    header("Location: dashboard.php");
    exit();
}

include("../includes/db.php");

if(isset($_POST['login'])){

    $student_number = trim($_POST['student_number']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT users.*
        FROM users
        INNER JOIN students
        ON users.id = students.user_id
        WHERE students.student_number=?
        AND users.role='student'
    ");

    $stmt->bind_param("s", $student_number);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $row = $result->fetch_assoc();

        /*
        |--------------------------------------------------------------------------
        | VERIFY PASSWORD
        |--------------------------------------------------------------------------
        */

        if(password_verify($password, $row['password'])){

            /*
            |--------------------------------------------------------------------------
            | PASSWORD POLICY CHECK
            |--------------------------------------------------------------------------
            | Minimum 8 characters
            | At least 1 uppercase letter
            | At least 1 lowercase letter
            | At least 1 number
            | At least 1 special character
            |--------------------------------------------------------------------------
            */

            $password_valid = true;

            if(strlen($password) < 8){
                $password_valid = false;
            }

            if(!preg_match('/[A-Z]/', $password)){
                $password_valid = false;
            }

            if(!preg_match('/[a-z]/', $password)){
                $password_valid = false;
            }

            if(!preg_match('/[0-9]/', $password)){
                $password_valid = false;
            }

            if(!preg_match('/[^A-Za-z0-9]/', $password)){
                $password_valid = false;
            }


            /*
            |--------------------------------------------------------------------------
            | BLOCK LOGIN IF PASSWORD DOES NOT FOLLOW POLICY
            |--------------------------------------------------------------------------
            */

            if(!$password_valid){

                $error = "Your password no longer meets the password policy. Please update your password before logging in.";

            }else{

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['role'] = $row['role'];

                header("Location: dashboard.php");
                exit();

            }

        }else{

            $error = "Incorrect Password!";

        }

    }else{

        $error = "Student account not found!";

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Student Login</title>

<link rel="icon" href="../assets/images/prmsu-logo.png">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet"
>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins,sans-serif;
}

body{

    background:
    linear-gradient(
        rgba(0,33,71,.75),
        rgba(0,33,71,.75)
    ),
    url("../assets/images/dashboard-bg.jpg");

    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
    background-attachment:fixed;

    display:flex;
    justify-content:center;
    align-items:center;

    height:100vh;
}

.card{

    width:430px;
    padding:35px;

    background:rgba(255,255,255,.15);

    backdrop-filter:blur(15px);
    -webkit-backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,.25);

    border-radius:20px;

    box-shadow:0 15px 40px rgba(0,0,0,.35);

}

.logo{
    width:90px;
}

h3,
label{
    color:#fff;
}

.text-muted{
    color:#f1f1f1 !important;
}

.form-control{

    height:48px;

    background:rgba(255,255,255,.15);

    border:1px solid rgba(255,255,255,.30);

    color:#fff;

}

.form-control::placeholder{
    color:#ddd;
}

.form-control:focus{

    background:rgba(255,255,255,.20);

    border-color:#fff;

    color:#fff;

    box-shadow:none;

}

.input-group-text{

    background:rgba(255,255,255,.15);

    border:1px solid rgba(255,255,255,.30);

    color:#fff;

    cursor:pointer;

}

.btn-login{

    background:#ffc107;

    color:#002147;

    font-weight:bold;

    border:none;

}

.btn-login:hover{

    background:#ffca2c;

    color:#002147;

}

.btn-secondary{

    background:rgba(255,255,255,.20);

    border:1px solid rgba(255,255,255,.30);

    color:#fff;

}

.btn-secondary:hover{

    background:rgba(255,255,255,.35);

    color:#fff;

}

.forgot-password{

    color:#fff;

    text-decoration:none;

    font-size:14px;

}

.forgot-password:hover{

    color:#ffc107;

    text-decoration:underline;

}

.alert{

    background:rgba(220,53,69,.90);

    border:none;

    color:white;

}

</style>

</head>

<body>

<div class="card">

<div class="text-center">

<img
src="../assets/images/prmsu-logo.png"
class="logo mb-3"
>

<h3>Student Login</h3>

<p class="text-muted">
PRMSU Mental Health and Guidance Counseling System
</p>

</div>


<?php if(isset($error)){ ?>

<div class="alert alert-danger">

<?= htmlspecialchars($error); ?>

</div>

<?php } ?>


<form method="POST">


<div class="mb-3">

<label>
Student Number
</label>

<input
type="text"
name="student_number"
class="form-control"
placeholder="Enter Student Number"
required
>

</div>


<div class="mb-2">

<label>
Password
</label>

<div class="input-group">

<input
type="password"
name="password"
id="password"
class="form-control"
placeholder="Enter Password"
required
>

<span
class="input-group-text"
onclick="togglePassword()"
>

<i
class="bi bi-eye"
id="eyeIcon"
></i>

</span>

</div>

</div>


<div class="text-end mb-3">

<a
href="forgot_password.php"
class="forgot-password"
>

Forgot Password?

</a>

</div>


<button
type="submit"
name="login"
class="btn btn-login w-100"
>

<i class="bi bi-box-arrow-in-right"></i>

Login

</button>


</form>


<br>


<a
href="../index.php"
class="btn btn-secondary w-100"
>

<i class="bi bi-arrow-left"></i>

Back to Main

</a>


</div>


<script>

function togglePassword(){

    let pass = document.getElementById("password");
    let eye = document.getElementById("eyeIcon");

    if(pass.type === "password"){

        pass.type = "text";

        eye.className = "bi bi-eye-slash";

    }else{

        pass.type = "password";

        eye.className = "bi bi-eye";

    }

}

</script>

</body>

</html>