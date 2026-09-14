<?php
session_start();
include("../includes/db.php");

if (isset($_SESSION['user_id']) && $_SESSION['role'] == "counselor") {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        AND role = 'counselor'
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Incorrect Password!";

        }

    } else {

        $error = "Guidance Counselor account not found!";

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Guidance Counselor Login</title>

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

/* LOGIN CONTAINER */

.login-container{

    width:430px;
    padding:35px;

    background:rgba(255,255,255,.15);

    backdrop-filter:blur(15px);
    -webkit-backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,.25);

    border-radius:20px;

    box-shadow:0 15px 40px rgba(0,0,0,.35);

}

/* LOGO */

.logo{
    width:90px;
}

/* TEXT */

h3{
    color:white;
    font-weight:bold;
}

p{
    color:#f1f1f1;
}

label{
    color:white;
    font-weight:500;
}

/* INPUT */

.form-control{

    height:48px;

    background:rgba(255,255,255,.15);

    border:1px solid rgba(255,255,255,.30);

    color:white;

}

.form-control::placeholder{
    color:#ddd;
}

.form-control:focus{

    background:rgba(255,255,255,.20);

    border-color:white;

    color:white;

    box-shadow:none;

}

/* PASSWORD EYE */

.input-group-text{

    background:rgba(255,255,255,.15);

    border:1px solid rgba(255,255,255,.30);

    color:white;

    cursor:pointer;

}

/* LOGIN BUTTON */

.btn-login{

    background:#ffc107;

    color:#002147;

    font-weight:bold;

    border:none;

    height:48px;

}

.btn-login:hover{

    background:#ffca2c;

    color:#002147;

}

/* FORGOT PASSWORD */

.forgot-password{

    color:white;

    text-decoration:none;

    font-size:14px;

    font-weight:500;

}

.forgot-password:hover{

    color:#ffc107;

    text-decoration:underline;

}

/* BACK BUTTON */

.btn-secondary{

    background:rgba(255,255,255,.20);

    border:1px solid rgba(255,255,255,.30);

    color:white;

    height:48px;

}

.btn-secondary:hover{

    background:rgba(255,255,255,.35);

    color:white;

}

/* ERROR */

.alert-danger{

    background:rgba(220,53,69,.90);

    border:none;

    color:white;

}

</style>

</head>

<body>

<div class="login-container">

    <div class="text-center">

        <img
            src="../assets/images/prmsu-logo.png"
            class="logo mb-3"
        >

        <h3>
            Guidance Counselor Login
        </h3>

        <p>
            PRMSU Mental Health and Guidance Counseling System
        </p>

    </div>


    <?php if (!empty($error)) { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <form method="POST">


        <!-- EMAIL -->

        <div class="mb-3">

            <label class="form-label">
                Email Address
            </label>

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Enter your email"
                required
            >

        </div>


        <!-- PASSWORD -->

        <div class="mb-1">

            <label class="form-label">
                Password
            </label>

            <div class="input-group">

                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Enter your password"
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


        <!-- FORGOT PASSWORD -->

        <div class="text-end mb-3">

            <a
                href="forgot_password.php"
                class="forgot-password"
            >

                Forgot Password?

            </a>

        </div>


        <!-- LOGIN -->

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


    <!-- BACK TO MAIN -->

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