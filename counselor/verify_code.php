<?php

session_start();

include("../includes/db.php");

// =====================================
// CHECK RESET SESSION
// =====================================

if (
    !isset($_SESSION['reset_user_id']) ||
    !isset($_SESSION['reset_email'])
) {
    header("Location: forgot_password.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$email = $_SESSION['reset_email'];

$error = "";
$message = "";


// =====================================
// VERIFY CODE
// =====================================

if (isset($_POST['verify_code'])) {

    $code = trim($_POST['verification_code'] ?? '');

    // =================================
    // CHECK CODE FORMAT
    // =================================

    if (empty($code)) {

        $error = "Please enter the verification code.";

    } elseif (!preg_match('/^[0-9]{6}$/', $code)) {

        $error = "Verification code must be 6 digits.";

    } else {

        // =================================
        // GET LATEST RESET CODE
        // =================================

        $stmt = $conn->prepare("
            SELECT
                id,
                user_id,
                email,
                verification_code,
                expires_at,
                verified
            FROM password_resets
            WHERE user_id = ?
            AND email = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        if (!$stmt) {

            $error =
                "Database Error: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "is",
                $user_id,
                $email
            );

            $stmt->execute();

            $result = $stmt->get_result();


            // =================================
            // CODE NOT FOUND
            // =================================

            if ($result->num_rows == 0) {

                $error =
                    "Verification code not found. " .
                    "Please request a new code.";

                $stmt->close();

            } else {

                $reset = $result->fetch_assoc();

                $stmt->close();


                // =================================
                // CHECK IF ALREADY VERIFIED
                // =================================

                if ((int)$reset['verified'] === 1) {

                    $error =
                        "This verification code has already been used.";

                }

                // =================================
                // CHECK EXPIRATION
                // =================================

                elseif (
                    strtotime($reset['expires_at']) < time()
                ) {

                    $error =
                        "Verification code has expired. " .
                        "Please request a new code.";

                }

                // =================================
                // CHECK CODE
                // =================================

                elseif (
                    !hash_equals(
                        (string)$reset['verification_code'],
                        (string)$code
                    )
                ) {

                    $error =
                        "Incorrect verification code.";

                }

                // =================================
                // CODE CORRECT
                // =================================

                else {

                    // =================================
                    // MARK CODE AS VERIFIED
                    // =================================

                    $stmt = $conn->prepare("
                        UPDATE password_resets
                        SET verified = 1
                        WHERE id = ?
                    ");

                    if (!$stmt) {

                        $error =
                            "Database Error: " .
                            $conn->error;

                    } else {

                        $stmt->bind_param(
                            "i",
                            $reset['id']
                        );

                        if ($stmt->execute()) {

                            $stmt->close();


                            // =================================
                            // SAVE VERIFIED SESSION
                            // =================================

                            $_SESSION[
                                'password_reset_verified'
                            ] = true;

                            $_SESSION[
                                'password_reset_id'
                            ] = $reset['id'];


                            // =================================
                            // GO TO RESET PASSWORD
                            // =================================

                            header(
                                "Location: reset_password.php"
                            );

                            exit();

                        } else {

                            $error =
                                "Unable to verify code.";

                            $stmt->close();

                        }

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

<title>Verify Code</title>


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


.verify-container {

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


.email-text {

    color: #ffc107;

    font-weight: 600;

    word-break: break-all;

}


label {

    color: white;

    font-weight: 500;

}


.code-input {

    height: 58px;

    text-align: center;

    font-size: 26px;

    font-weight: bold;

    letter-spacing: 8px;

    background:
        rgba(255,255,255,.15);

    border:
        1px solid
        rgba(255,255,255,.30);

    color: white;

}


.code-input::placeholder {

    color: #ddd;

    letter-spacing: 5px;

}


.code-input:focus {

    background:
        rgba(255,255,255,.20);

    border-color: white;

    color: white;

    box-shadow: none;

}


.btn-verify {

    height: 48px;

    background: #ffc107;

    color: #002147;

    border: none;

    font-weight: bold;

}


.btn-verify:hover {

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


.info-box {

    background:
        rgba(255,255,255,.10);

    border:
        1px solid
        rgba(255,255,255,.20);

    border-radius: 10px;

    padding: 12px;

    color: white;

    font-size: 13px;

}


@media(max-width:500px) {

    .verify-container {

        width: calc(100% - 30px);

        padding: 25px;

    }

}

</style>

</head>


<body>


<div class="verify-container">


<div class="text-center">


<img
    src="../assets/images/prmsu-logo.png"
    class="logo mb-3"
>


<h3>
    Verify Your Email
</h3>


<p class="description mb-3">

We sent a 6-digit verification code to:

</p>


<div class="email-text mb-4">

<?= htmlspecialchars($email); ?>

</div>


</div>


<?php if (!empty($error)) { ?>

<div class="alert alert-danger">

<i class="bi bi-exclamation-circle me-1"></i>

<?= htmlspecialchars($error); ?>

</div>

<?php } ?>


<div class="info-box mb-4">

<i class="bi bi-info-circle me-1"></i>

Check your email for the verification code.
The code is valid for <strong>10 minutes</strong>.

</div>


<form method="POST">


<div class="mb-4">

<label class="form-label">

Verification Code

</label>


<input
    type="text"
    name="verification_code"
    class="form-control code-input"
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

<i class="bi bi-shield-check me-1"></i>

Verify Code

</button>


</form>


<br>


<a
    href="forgot_password.php"
    class="btn btn-back w-100"
>

<i class="bi bi-arrow-left me-1"></i>

Request New Code

</a>


</div>


<script>

// =====================================
// ALLOW NUMBERS ONLY
// =====================================

const codeInput =
    document.querySelector(".code-input");

if (codeInput) {

    codeInput.addEventListener(
        "input",
        function () {

            this.value =
                this.value
                .replace(/[^0-9]/g, "")
                .slice(0, 6);

        }
    );

}

</script>


</body>

</html>