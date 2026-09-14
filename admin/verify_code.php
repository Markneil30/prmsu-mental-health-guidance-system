<?php

session_start();

include("../includes/db.php");

$error = "";


/*
|--------------------------------------------------------------------------
| CHECK ADMIN RESET SESSION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['admin_reset_user_id']) ||
    !isset($_SESSION['admin_reset_email']) ||
    !isset($_SESSION['admin_reset_code'])
) {

    header("Location: forgot_password.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| VERIFY CODE
|--------------------------------------------------------------------------
*/

if (isset($_POST['verify_code'])) {

    $entered_code =
        trim($_POST['verification_code'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | CHECK EMPTY
    |--------------------------------------------------------------------------
    */

    if (empty($entered_code)) {

        $error =
            "Please enter the verification code.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CHECK CODE EXPIRATION
        |--------------------------------------------------------------------------
        */

        if (
            isset($_SESSION['admin_reset_code_time']) &&
            (time() - $_SESSION['admin_reset_code_time']) > 600
        ) {

            $error =
                "Verification code has expired. Please request a new code.";

            unset(
                $_SESSION['admin_reset_code'],
                $_SESSION['admin_reset_code_time']
            );


        } else {


            /*
            |--------------------------------------------------------------------------
            | CHECK CODE
            |--------------------------------------------------------------------------
            */

            if (
                $entered_code ==
                $_SESSION['admin_reset_code']
            ) {


                /*
                |--------------------------------------------------------------------------
                | CREATE ADMIN RESET VERIFICATION SESSION
                |--------------------------------------------------------------------------
                */

                $_SESSION['admin_reset_verified'] = true;


                /*
                |--------------------------------------------------------------------------
                | CREATE RESET RECORD ID
                |--------------------------------------------------------------------------
                */

                $user_id =
                    (int) $_SESSION['admin_reset_user_id'];

                $email =
                    $_SESSION['admin_reset_email'];


                /*
                |--------------------------------------------------------------------------
                | GET LATEST PASSWORD RESET RECORD
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    SELECT id
                    FROM password_resets
                    WHERE user_id = ?
                    AND email = ?
                    AND verification_code = ?
                    AND verified = 0
                    ORDER BY id DESC
                    LIMIT 1
                ");


                if (!$stmt) {

                    $error =
                        "Database Error: " .
                        $conn->error;

                } else {

                    $verification_code =
                        (int) $_SESSION['admin_reset_code'];


                    $stmt->bind_param(
                        "isi",
                        $user_id,
                        $email,
                        $verification_code
                    );


                    $stmt->execute();

                    $result =
                        $stmt->get_result();


                    if ($result->num_rows == 1) {

                        $reset =
                            $result->fetch_assoc();


                        $_SESSION['admin_reset_id'] =
                            (int) $reset['id'];


                        /*
                        |--------------------------------------------------------------------------
                        | REMOVE CODE AFTER SUCCESSFUL VERIFICATION
                        |--------------------------------------------------------------------------
                        */

                        unset(
                            $_SESSION['admin_reset_code'],
                            $_SESSION['admin_reset_code_time']
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | GO TO RESET PASSWORD
                        |--------------------------------------------------------------------------
                        */

                        header(
                            "Location: reset_password.php"
                        );

                        exit();


                    } else {

                        $error =
                            "Verification record not found or has already been used.";

                        unset(
                            $_SESSION['admin_reset_verified']
                        );

                    }


                    $stmt->close();

                }


            } else {

                $error =
                    "Incorrect verification code.";

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

<title>Verify Administrator Code</title>

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


.verify-container{

    width:430px;

    padding:35px;

    border-radius:20px;

    background:
    rgba(255,255,255,.12);

    backdrop-filter:blur(15px);

    -webkit-backdrop-filter:blur(15px);

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


.email-text{

    color:#ffc107;

    font-weight:bold;

    word-break:break-word;

}


.form-control{

    height:52px;

    background:
    rgba(255,255,255,.15);

    border:
    1px solid
    rgba(255,255,255,.30);

    color:white;

    text-align:center;

    font-size:22px;

    letter-spacing:8px;

}


.form-control::placeholder{

    color:#ddd;

    font-size:14px;

    letter-spacing:0;

}


.form-control:focus{

    background:
    rgba(255,255,255,.20);

    border-color:white;

    color:white;

    box-shadow:none;

}


.btn-verify{

    height:48px;

    background:#ffc107;

    color:#002147;

    font-weight:bold;

    border:none;

}


.btn-verify:hover{

    background:#ffca2c;

    color:#002147;

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


<div class="verify-container">


    <!-- HEADER -->

    <div class="text-center">


        <img
            src="../assets/images/prmsu-logo.png"
            class="logo mb-3"
        >


        <h3>
            Verify Your Email
        </h3>


        <p>

            Enter the 6-digit verification code
            sent to:

        </p>


        <p class="email-text">

            <?= htmlspecialchars(
                $_SESSION['admin_reset_email']
            ); ?>

        </p>


    </div>



    <!-- ERROR -->

    <?php if (!empty($error)) { ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php } ?>



    <!-- VERIFICATION FORM -->

    <form method="POST">


        <div class="mb-3">


            <label class="form-label">

                Verification Code

            </label>


            <input
                type="text"
                name="verification_code"
                class="form-control"
                placeholder="000000"
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                autocomplete="one-time-code"
                required
            >


        </div>



        <button
            type="submit"
            name="verify_code"
            class="btn btn-verify w-100"
        >

            <i class="bi bi-shield-check"></i>

            Verify Code

        </button>


    </form>



    <br>



    <a
        href="forgot_password.php"
        class="btn btn-secondary w-100"
    >

        <i class="bi bi-arrow-left"></i>

        Back to Forgot Password

    </a>


</div>


</body>

</html>