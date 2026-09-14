<?php

session_start();

include("../includes/db.php");

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| CHECK RESET SESSION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['admin_reset_user_id']) ||
    !isset($_SESSION['admin_reset_email']) ||
    !isset($_SESSION['admin_reset_verified']) ||
    $_SESSION['admin_reset_verified'] !== true
) {

    header("Location: forgot_password.php");
    exit();

}


$user_id = (int) $_SESSION['admin_reset_user_id'];
$email = $_SESSION['admin_reset_email'];


/*
|--------------------------------------------------------------------------
| RESET PASSWORD
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    if (empty($password) || empty($confirm_password)) {

        $error = "Please enter your new password and confirm password.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | PASSWORD POLICY
        |--------------------------------------------------------------------------
        */

        $policy_errors = [];


        // Minimum 8 characters
        if (strlen($password) < 8) {

            $policy_errors[] =
                "at least 8 characters";

        }


        // At least 1 uppercase
        if (!preg_match('/[A-Z]/', $password)) {

            $policy_errors[] =
                "at least 1 uppercase letter";

        }


        // At least 1 lowercase
        if (!preg_match('/[a-z]/', $password)) {

            $policy_errors[] =
                "at least 1 lowercase letter";

        }


        // At least 1 number
        if (!preg_match('/[0-9]/', $password)) {

            $policy_errors[] =
                "at least 1 number";

        }


        // At least 1 special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {

            $policy_errors[] =
                "at least 1 special character";

        }


        if (!empty($policy_errors)) {

            $error =
                "Password must contain "
                . implode(", ", $policy_errors)
                . ".";

        } else {

            /*
            |--------------------------------------------------------------------------
            | HASH PASSWORD
            |--------------------------------------------------------------------------
            */

            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            /*
            |--------------------------------------------------------------------------
            | UPDATE ADMIN PASSWORD
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
                AND email = ?
                AND role = 'admin'
            ");


            if (!$stmt) {

                $error =
                    "Database error: "
                    . $conn->error;

            } else {

                $stmt->bind_param(
                    "sis",
                    $hashed_password,
                    $user_id,
                    $email
                );


                if ($stmt->execute()) {

                    /*
                    |--------------------------------------------------------------------------
                    | CLEAR RESET SESSION
                    |--------------------------------------------------------------------------
                    */

                    unset(
                        $_SESSION['admin_reset_user_id'],
                        $_SESSION['admin_reset_email'],
                        $_SESSION['admin_reset_code'],
                        $_SESSION['admin_reset_code_time'],
                        $_SESSION['admin_reset_verified']
                    );


                    $success =
                        "Your password has been successfully updated.";

                } else {

                    $error =
                        "Unable to update password: "
                        . $stmt->error;

                }


                $stmt->close();

            }

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

<title>Reset Administrator Password</title>

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


.reset-container{

    position:relative;

    z-index:2;

    width:430px;

    padding:35px;

    border-radius:20px;

    background:
    rgba(255,255,255,.10);

    backdrop-filter:blur(12px);

    -webkit-backdrop-filter:blur(12px);

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


.btn-reset{

    background:#002147;

    color:white;

    font-weight:bold;

    border:none;

    height:48px;

}


.btn-reset:hover{

    background:#0A3D91;

    color:white;

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


.password-policy{

    color:#f1f1f1;

    font-size:12px;

    line-height:1.6;

}


.alert{

    border:none;

}

</style>

</head>


<body>


<div class="reset-container">


    <div class="text-center">


        <img
            src="../assets/images/prmsu-logo.png"
            class="logo mb-3"
        >


        <h3>
            Reset Password
        </h3>


        <p class="text-muted">

            PRMSU Mental Health and
            Guidance Counseling System

        </p>


        <p>

            <strong>
                Administrator Account
            </strong>

        </p>


    </div>


    <?php if (!empty($error)) { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <?php if (!empty($success)) { ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($success); ?>

        </div>


        <a
            href="login.php"
            class="btn btn-reset w-100"
        >

            <i class="bi bi-box-arrow-in-right"></i>

            Go to Administrator Login

        </a>


    <?php } else { ?>


        <form method="POST">


            <div class="mb-3">

                <label class="form-label">

                    New Password

                </label>


                <div class="input-group">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter New Password"
                        required
                    >


                    <span
                        class="input-group-text"
                        onclick="togglePassword('password','eyeIcon1')"
                    >

                        <i
                            class="bi bi-eye"
                            id="eyeIcon1"
                        ></i>

                    </span>

                </div>

            </div>


            <div class="mb-2">

                <label class="form-label">

                    Confirm New Password

                </label>


                <div class="input-group">

                    <input
                        type="password"
                        name="confirm_password"
                        id="confirm_password"
                        class="form-control"
                        placeholder="Confirm New Password"
                        required
                    >


                    <span
                        class="input-group-text"
                        onclick="togglePassword('confirm_password','eyeIcon2')"
                    >

                        <i
                            class="bi bi-eye"
                            id="eyeIcon2"
                        ></i>

                    </span>

                </div>

            </div>


            <div class="password-policy mb-3">

                <strong>Password must contain:</strong>

                <br>

                • At least 8 characters

                <br>

                • At least 1 uppercase letter

                <br>

                • At least 1 lowercase letter

                <br>

                • At least 1 number

                <br>

                • At least 1 special character

            </div>


            <button
                type="submit"
                class="btn btn-reset w-100"
            >

                <i class="bi bi-shield-lock"></i>

                Reset Password

            </button>


        </form>


        <br>


        <a
            href="login.php"
            class="btn btn-secondary w-100"
        >

            <i class="bi bi-arrow-left"></i>

            Back to Administrator Login

        </a>


    <?php } ?>


</div>


<script>

function togglePassword(inputId, iconId){

    let pass =
        document.getElementById(inputId);

    let eye =
        document.getElementById(iconId);


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