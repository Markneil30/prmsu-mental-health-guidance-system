<?php

session_start();

include("../includes/db.php");

$error = "";


/*
|--------------------------------------------------------------------------
| CHECK ADMIN SESSION
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['user_id']) &&
    isset($_SESSION['role']) &&
    $_SESSION['role'] === "admin"
) {

    header("Location: dashboard.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');


    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | GET ADMIN ACCOUNT
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT id, fullname, email, password, role
            FROM users
            WHERE email = ?
            AND role = 'admin'
            LIMIT 1
        ");


        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            /*
            |--------------------------------------------------------------------------
            | ADMIN ACCOUNT FOUND
            |--------------------------------------------------------------------------
            */

            if ($result->num_rows === 1) {

                $row = $result->fetch_assoc();

                $stored_password = $row['password'];


                /*
                |--------------------------------------------------------------------------
                | SPECIAL FIX FOR EXISTING ADMIN ACCOUNT
                |--------------------------------------------------------------------------
                |
                | Existing database hash is incomplete.
                | If correct temporary password is entered,
                | generate a new valid password hash.
                |
                */

                if (
                    $email === "admin@prmsu.edu.ph" &&
                    $password === "admin123"
                ) {

                    $new_hash = password_hash(
                        "admin123",
                        PASSWORD_DEFAULT
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE PASSWORD HASH
                    |--------------------------------------------------------------------------
                    */

                    $update = $conn->prepare("
                        UPDATE users
                        SET password = ?
                        WHERE id = ?
                        AND email = ?
                        AND role = 'admin'
                    ");


                    if (!$update) {

                        $error =
                            "Unable to update password: "
                            . $conn->error;

                    } else {

                        $update->bind_param(
                            "sis",
                            $new_hash,
                            $row['id'],
                            $email
                        );


                        if ($update->execute()) {

                            /*
                            |--------------------------------------------------------------------------
                            | LOGIN SUCCESS
                            |--------------------------------------------------------------------------
                            */

                            session_regenerate_id(true);


                            $_SESSION['user_id'] =
                                $row['id'];

                            $_SESSION['fullname'] =
                                $row['fullname'];

                            $_SESSION['role'] =
                                $row['role'];

                            $_SESSION['email'] =
                                $row['email'];


                            $update->close();

                            $stmt->close();


                            header(
                                "Location: dashboard.php"
                            );

                            exit();


                        } else {

                            $error =
                                "Password update failed: "
                                . $update->error;

                        }


                        $update->close();

                    }


                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | NORMAL PASSWORD VERIFICATION
                    |--------------------------------------------------------------------------
                    */

                    if (
                        password_verify(
                            $password,
                            $stored_password
                        )
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | LOGIN SUCCESS
                        |--------------------------------------------------------------------------
                        */

                        session_regenerate_id(true);


                        $_SESSION['user_id'] =
                            $row['id'];

                        $_SESSION['fullname'] =
                            $row['fullname'];

                        $_SESSION['role'] =
                            $row['role'];

                        $_SESSION['email'] =
                            $row['email'];


                        $stmt->close();


                        header(
                            "Location: dashboard.php"
                        );

                        exit();


                    } else {

                        $error =
                            "Incorrect Password!";

                    }

                }


            } else {

                $error =
                    "Administrator account not found!";

            }


            $stmt->close();

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Administrator Login</title>

<link
    rel="icon"
    href="../assets/images/prmsu-logo.png"
>

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

    margin:0;

    padding:0;

    background:
    url("../assets/images/dashboard-bg.jpg");

    background-size:cover;

    background-position:center;

    background-repeat:no-repeat;

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100vh;

}


body::before{

    content:"";

    position:fixed;

    inset:0;

    background:
    rgba(0,33,71,.60);

}


.login-container{

    position:relative;

    z-index:2;

    width:430px;

    padding:35px;

    border-radius:20px;

    background:
    rgba(255,255,255,.10);

    backdrop-filter:
    blur(12px);

    -webkit-backdrop-filter:
    blur(12px);

    border:
    1px solid
    rgba(255,255,255,.25);

    box-shadow:
    0 15px 40px
    rgba(0,0,0,.35);

}


.logo{

    width:90px;

}


h3,
p,
label{

    color:white;

}


.text-muted{

    color:#f1f1f1 !important;

}


.form-control{

    height:48px;

    background:
    rgba(255,255,255,.15);

    color:white;

    border:
    1px solid
    rgba(255,255,255,.30);

}


.form-control::placeholder{

    color:#ddd;

}


.form-control:focus{

    background:
    rgba(255,255,255,.20);

    color:white;

    border-color:white;

    box-shadow:none;

}


.input-group-text{

    background:
    rgba(255,255,255,.15);

    color:white;

    border:
    1px solid
    rgba(255,255,255,.30);

    cursor:pointer;

}


.btn-login{

    background:#002147;

    color:white;

    font-weight:bold;

    border:none;

}


.btn-login:hover{

    background:#0A3D91;

    color:white;

}


.forgot-password{

    color:#ffc107;

    text-decoration:none;

    font-size:14px;

    font-weight:500;

}


.forgot-password:hover{

    color:#ffca2c;

    text-decoration:underline;

}


.btn-secondary{

    background:
    rgba(255,255,255,.20);

    border:
    1px solid
    rgba(255,255,255,.30);

    color:white;

}


.btn-secondary:hover{

    background:
    rgba(255,255,255,.35);

    color:white;

}


.alert{

    border:none;

}

</style>

</head>


<body>


<div class="login-container">


    <!-- HEADER -->

    <div class="text-center">


        <img
            src="../assets/images/prmsu-logo.png"
            class="logo mb-3"
        >


        <h3>
            Administrator Login
        </h3>


        <p class="text-muted">

            PRMSU Mental Health and
            Guidance Counseling System

        </p>


    </div>



    <!-- ERROR -->

    <?php if (!empty($error)) { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>



    <!-- LOGIN FORM -->

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
                placeholder="Enter Email Address"
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



        <!-- FORGOT PASSWORD -->

        <div class="text-end mb-3">


            <a
                href="forgot_password.php"
                class="forgot-password"
            >

                Forgot Password?

            </a>


        </div>



        <!-- LOGIN BUTTON -->

        <button
            type="submit"
            name="login"
            class="btn btn-login w-100"
        >

            <i
                class="bi bi-box-arrow-in-right"
            ></i>

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

    let pass =
        document.getElementById("password");

    let eye =
        document.getElementById("eyeIcon");


    if(pass.type === "password"){

        pass.type = "text";

        eye.className =
            "bi bi-eye-slash";

    }else{

        pass.type = "password";

        eye.className =
            "bi bi-eye";

    }

}

</script>


</body>

</html>