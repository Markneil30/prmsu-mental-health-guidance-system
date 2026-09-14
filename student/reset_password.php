<?php

session_start();

include("../includes/db.php");

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| CHECK VERIFICATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['student_reset_user_id']) ||
    !isset($_SESSION['student_reset_email']) ||
    !isset($_SESSION['student_code_verified']) ||
    $_SESSION['student_code_verified'] !== true
) {

    header("Location: forgot_password.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| STORE RESET EMAIL SAFELY
|--------------------------------------------------------------------------
*/

$reset_email = $_SESSION['student_reset_email'];


/*
|--------------------------------------------------------------------------
| RESET PASSWORD
|--------------------------------------------------------------------------
*/

if (isset($_POST['reset_password'])) {

    $new_password =
        $_POST['new_password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | CHECK EMPTY
    |--------------------------------------------------------------------------
    */

    if (
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $error =
            "Please enter your new password.";


    /*
    |--------------------------------------------------------------------------
    | CHECK PASSWORD MATCH
    |--------------------------------------------------------------------------
    */

    } elseif ($new_password !== $confirm_password) {

        $error =
            "Passwords do not match.";


    /*
    |--------------------------------------------------------------------------
    | PASSWORD POLICY
    |--------------------------------------------------------------------------
    */

    } elseif (strlen($new_password) < 8) {

        $error =
            "Password must be at least 8 characters.";


    } elseif (!preg_match('/[A-Z]/', $new_password)) {

        $error =
            "Password must contain at least one uppercase letter.";


    } elseif (!preg_match('/[a-z]/', $new_password)) {

        $error =
            "Password must contain at least one lowercase letter.";


    } elseif (!preg_match('/[0-9]/', $new_password)) {

        $error =
            "Password must contain at least one number.";


    } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {

        $error =
            "Password must contain at least one special character.";


    } else {

        /*
        |--------------------------------------------------------------------------
        | HASH PASSWORD
        |--------------------------------------------------------------------------
        */

        $hashed_password =
            password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


        /*
        |--------------------------------------------------------------------------
        | UPDATE PASSWORD
        |--------------------------------------------------------------------------
        */

        $user_id =
            (int) $_SESSION['student_reset_user_id'];


        $stmt = $conn->prepare("
            UPDATE users
            SET password = ?
            WHERE id = ?
            AND role = 'student'
        ");


        $stmt->bind_param(
            "si",
            $hashed_password,
            $user_id
        );


        if ($stmt->execute()) {

            /*
            |--------------------------------------------------------------------------
            | CLEAR RESET SESSION
            |--------------------------------------------------------------------------
            */

            unset(
                $_SESSION['student_reset_user_id']
            );

            unset(
                $_SESSION['student_reset_email']
            );

            unset(
                $_SESSION['student_code_verified']
            );


            $success =
                "Your password has been successfully reset.";


        } else {

            $error =
                "Unable to reset your password. Please try again.";

        }


        $stmt->close();

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

<title>Reset Student Password</title>

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

    background:
    linear-gradient(
        rgba(0,33,71,.75),
        rgba(0,33,71,.75)
    ),
    url("../assets/images/dashboard-bg.jpg");

    background-size:cover;

    background-position:center;

    background-repeat:no-repeat;

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

}


.reset-container{

    width:430px;

    padding:35px;

    border-radius:20px;

    background:rgba(255,255,255,.12);

    backdrop-filter:blur(15px);

    -webkit-backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,.25);

    box-shadow:
        0 15px 40px rgba(0,0,0,.35);

}


.logo{

    width:90px;

}


h3,
p,
label{

    color:white;

}


.email-text{

    color:#ffc107;

    font-weight:bold;

    word-break:break-word;

}


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


.input-group-text{

    background:rgba(255,255,255,.15);

    border:1px solid rgba(255,255,255,.30);

    color:white;

    cursor:pointer;

}


.btn-submit{

    height:48px;

    background:#ffc107;

    color:#002147;

    font-weight:bold;

    border:none;

}


.btn-submit:hover{

    background:#ffca2c;

    color:#002147;

}


.btn-secondary{

    background:rgba(255,255,255,.20);

    border:1px solid rgba(255,255,255,.30);

    color:white;

}


.btn-secondary:hover{

    background:rgba(255,255,255,.35);

    color:white;

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


        <p>
            Create a new password for:
        </p>


        <p class="email-text">

            <?= htmlspecialchars($reset_email); ?>

        </p>


    </div>



    <?php if (!empty($error)) { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>



    <?php if (!empty($success)) { ?>


        <div class="alert alert-success text-center">

            <?= htmlspecialchars($success); ?>

        </div>


        <a
            href="login.php"
            class="btn btn-submit w-100"
        >

            <i class="bi bi-box-arrow-in-right"></i>

            Go to Student Login

        </a>


    <?php } else { ?>


        <form method="POST">


            <!-- NEW PASSWORD -->

            <div class="mb-3">

                <label class="form-label">

                    New Password

                </label>


                <div class="input-group">

                    <input
                        type="password"
                        name="new_password"
                        id="new_password"
                        class="form-control"
                        placeholder="Enter New Password"
                        required
                    >


                    <span
                        class="input-group-text"
                        onclick="toggleNewPassword()"
                    >

                        <i
                            class="bi bi-eye"
                            id="newEyeIcon"
                        ></i>

                    </span>

                </div>

            </div>



            <!-- CONFIRM PASSWORD -->

            <div class="mb-3">

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
                        onclick="toggleConfirmPassword()"
                    >

                        <i
                            class="bi bi-eye"
                            id="confirmEyeIcon"
                        ></i>

                    </span>

                </div>

            </div>



            <!-- PASSWORD POLICY -->

            <p>

                <small>

                    Password must contain:

                    <br>
                    • At least 8 characters
                    <br>
                    • At least one uppercase letter
                    <br>
                    • At least one lowercase letter
                    <br>
                    • At least one number
                    <br>
                    • At least one special character

                </small>

            </p>



            <button
                type="submit"
                name="reset_password"
                class="btn btn-submit w-100"
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

            Back to Student Login

        </a>


    <?php } ?>


</div>


<script>

function toggleNewPassword(){

    let password =
        document.getElementById("new_password");

    let icon =
        document.getElementById("newEyeIcon");


    if(password.type === "password"){

        password.type = "text";

        icon.className =
            "bi bi-eye-slash";

    }else{

        password.type = "password";

        icon.className =
            "bi bi-eye";

    }

}


function toggleConfirmPassword(){

    let password =
        document.getElementById("confirm_password");

    let icon =
        document.getElementById("confirmEyeIcon");


    if(password.type === "password"){

        password.type = "text";

        icon.className =
            "bi bi-eye-slash";

    }else{

        password.type = "password";

        icon.className =
            "bi bi-eye";

    }

}

</script>


</body>

</html>