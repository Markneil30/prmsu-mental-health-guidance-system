<?php

session_start();

include("../includes/db.php");

// =====================================
// PHPMailer
// =====================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../PHPMailer/src/Exception.php";
require "../PHPMailer/src/PHPMailer.php";
require "../PHPMailer/src/SMTP.php";


// =====================================
// VARIABLES
// =====================================

$message = "";
$error = "";


// =====================================
// SEND VERIFICATION CODE
// =====================================

if (isset($_POST['send_code'])) {

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {

        $error = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // =====================================
        // CHECK COUNSELOR ACCOUNT
        // =====================================

        $stmt = $conn->prepare("
            SELECT
                id,
                fullname,
                email
            FROM users
            WHERE email = ?
            AND role = 'counselor'
            LIMIT 1
        ");

        if (!$stmt) {

            $error = "Database Error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows == 0) {

                $error = "Guidance Counselor account not found.";

                $stmt->close();

            } else {

                $user = $result->fetch_assoc();

                $stmt->close();


                // =====================================
                // GENERATE 6-DIGIT CODE
                // =====================================

                $verification_code = (string) random_int(100000, 999999);

                // Code expires after 10 minutes
                $expires_at = date(
                    "Y-m-d H:i:s",
                    strtotime("+10 minutes")
                );


                // =====================================
                // CREATE PASSWORD RESET TABLE
                // =====================================
                // The table must exist first.
                // See SQL below the code.

                $stmt = $conn->prepare("
                    INSERT INTO password_resets
                    (
                        user_id,
                        email,
                        verification_code,
                        expires_at,
                        verified,
                        created_at
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        0,
                        NOW()
                    )
                ");


                if (!$stmt) {

                    $error =
                        "Database Error: " .
                        $conn->error;

                } else {

                    $stmt->bind_param(
                        "isss",
                        $user['id'],
                        $email,
                        $verification_code,
                        $expires_at
                    );


                    if ($stmt->execute()) {

                        $stmt->close();


                        // =====================================
                        // SEND EMAIL USING PHPMailer
                        // =====================================

                        $mail = new PHPMailer(true);


                        try {

                            // =================================
                            // SMTP SETTINGS
                            // =================================

                            $mail->isSMTP();

                            $mail->Host = 'smtp.gmail.com';

                            $mail->SMTPAuth = true;

                            /*
                             * PUT YOUR GMAIL HERE
                             */
                            $mail->Username = 'markneilarayata27@gmail.com';

                            /*
                             * PUT YOUR GMAIL APP PASSWORD HERE
                             *
                             * NOT your normal Gmail password.
                             */
                            $mail->Password = 'jggbxdrmyozrihla';

                            $mail->SMTPSecure =
                                PHPMailer::ENCRYPTION_STARTTLS;

                            $mail->Port = 587;


                            // =================================
                            // EMAIL
                            // =================================

                            $mail->setFrom(
                                'markneilarayata27@gmail.com',
                                'PRMSU Guidance System'
                            );

                            $mail->addAddress(
                                $email,
                                $user['fullname']
                            );


                            $mail->isHTML(true);

                            $mail->Subject =
                                'PRMSU Guidance System - Password Verification Code';


                            $mail->Body = '

                            <div style="
                                font-family: Arial, sans-serif;
                                background:#f4f6f9;
                                padding:30px;
                            ">

                                <div style="
                                    max-width:550px;
                                    margin:auto;
                                    background:white;
                                    border-radius:12px;
                                    padding:30px;
                                    box-shadow:0 5px 20px rgba(0,0,0,.08);
                                ">

                                    <div style="
                                        text-align:center;
                                        margin-bottom:20px;
                                    ">

                                        <h2 style="
                                            color:#002147;
                                            margin-bottom:5px;
                                        ">
                                            PRMSU Guidance System
                                        </h2>

                                        <p style="
                                            color:#64748b;
                                            margin:0;
                                        ">
                                            Password Reset Verification
                                        </p>

                                    </div>


                                    <p>
                                        Hello
                                        <strong>
                                            ' .
                                            htmlspecialchars(
                                                $user['fullname']
                                            ) .
                                            '
                                        </strong>,
                                    </p>


                                    <p>
                                        We received a request to reset
                                        your password.
                                    </p>


                                    <p>
                                        Your verification code is:
                                    </p>


                                    <div style="
                                        text-align:center;
                                        margin:25px 0;
                                    ">

                                        <span style="
                                            display:inline-block;
                                            background:#002147;
                                            color:white;
                                            font-size:32px;
                                            font-weight:bold;
                                            letter-spacing:8px;
                                            padding:15px 25px;
                                            border-radius:10px;
                                        ">
                                            ' .
                                            $verification_code .
                                            '
                                        </span>

                                    </div>


                                    <p>
                                        This verification code will
                                        expire in
                                        <strong>
                                            10 minutes
                                        </strong>.
                                    </p>


                                    <p style="
                                        color:#64748b;
                                        font-size:13px;
                                    ">
                                        If you did not request a
                                        password reset, you can safely
                                        ignore this email.
                                    </p>


                                    <hr>


                                    <p style="
                                        text-align:center;
                                        color:#94a3b8;
                                        font-size:12px;
                                    ">
                                        PRMSU Mental Health and Guidance
                                        Counseling System
                                    </p>

                                </div>

                            </div>

                            ';


                            // =================================
                            // PLAIN TEXT VERSION
                            // =================================

                            $mail->AltBody =
                                "PRMSU Guidance System\n\n" .
                                "Your password reset " .
                                "verification code is: " .
                                $verification_code .
                                "\n\n" .
                                "This code expires in 10 minutes.";


                            // =================================
                            // SEND
                            // =================================

                            $mail->send();


                            // =================================
                            // SAVE SESSION
                            // =================================

                            $_SESSION[
                                'reset_user_id'
                            ] = $user['id'];

                            $_SESSION[
                                'reset_email'
                            ] = $email;


                            // =================================
                            // REDIRECT TO VERIFY PAGE
                            // =================================

                            header(
                                "Location: verify_code.php"
                            );

                            exit();


                        } catch (Exception $e) {

                            $error =
                                "Email could not be sent. " .
                                "Mailer Error: " .
                                htmlspecialchars(
                                    $mail->ErrorInfo
                                );

                        }

                    } else {

                        $error =
                            "Unable to save verification code: " .
                            htmlspecialchars(
                                $stmt->error
                            );

                        $stmt->close();

                    }

                }

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

<title>Forgot Password</title>


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

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

    font-family: Poppins, sans-serif;

}


body {

    background:
        linear-gradient(
            rgba(0,33,71,.75),
            rgba(0,33,71,.75)
        ),
        url("../assets/images/dashboard-bg.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    background-attachment: fixed;

    display: flex;

    justify-content: center;

    align-items: center;

    min-height: 100vh;

}


.forgot-container {

    width: 430px;

    padding: 35px;

    background:
        rgba(255,255,255,.15);

    backdrop-filter:
        blur(15px);

    -webkit-backdrop-filter:
        blur(15px);

    border:
        1px solid
        rgba(255,255,255,.25);

    border-radius: 20px;

    box-shadow:
        0 15px 40px
        rgba(0,0,0,.35);

}


.logo {

    width: 90px;

}


h3 {

    color: white;

    font-weight: bold;

}


.description {

    color: #f1f1f1;

    font-size: 14px;

    line-height: 1.5;

}


label {

    color: white;

    font-weight: 500;

}


.form-control {

    height: 48px;

    background:
        rgba(255,255,255,.15);

    border:
        1px solid
        rgba(255,255,255,.30);

    color: white;

}


.form-control::placeholder {

    color: #ddd;

}


.form-control:focus {

    background:
        rgba(255,255,255,.20);

    border-color: white;

    color: white;

    box-shadow: none;

}


.btn-send {

    height: 48px;

    background: #ffc107;

    color: #002147;

    border: none;

    font-weight: bold;

}


.btn-send:hover {

    background: #ffca2c;

    color: #002147;

}


.btn-back {

    height: 48px;

    background:
        rgba(255,255,255,.20);

    border:
        1px solid
        rgba(255,255,255,.30);

    color: white;

}


.btn-back:hover {

    background:
        rgba(255,255,255,.35);

    color: white;

}


.alert-danger {

    background:
        rgba(220,53,69,.90);

    border: none;

    color: white;

}


.alert-success {

    background:
        rgba(25,135,84,.90);

    border: none;

    color: white;

}


@media(max-width:500px) {

    .forgot-container {

        width: calc(100% - 30px);

        padding: 25px;

    }

}

</style>

</head>


<body>


<div class="forgot-container">


<div class="text-center">


<img
    src="../assets/images/prmsu-logo.png"
    class="logo mb-3"
>


<h3>
    Forgot Password?
</h3>


<p class="description mb-4">

Enter your registered counselor email
and we will send you a verification code.

</p>


</div>


<?php if (!empty($error)) { ?>

<div class="alert alert-danger">

<i class="bi bi-exclamation-circle me-1"></i>

<?= htmlspecialchars($error); ?>

</div>

<?php } ?>


<?php if (!empty($message)) { ?>

<div class="alert alert-success">

<i class="bi bi-check-circle me-1"></i>

<?= htmlspecialchars($message); ?>

</div>

<?php } ?>


<form method="POST">


<div class="mb-4">

<label class="form-label">

Email Address

</label>


<input
    type="email"
    name="email"
    class="form-control"
    placeholder="Enter your registered email"
    autocomplete="email"
    required
>

</div>


<button
    type="submit"
    name="send_code"
    class="btn btn-send w-100"
>

<i class="bi bi-envelope-fill me-1"></i>

Send Verification Code

</button>


</form>


<br>


<a
    href="login.php"
    class="btn btn-back w-100"
>

<i class="bi bi-arrow-left me-1"></i>

Back to Login

</a>


</div>


</body>

</html>