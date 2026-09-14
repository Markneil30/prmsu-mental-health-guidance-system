<?php

session_start();
include("../includes/db.php");

// =====================================
// CHECK VERIFIED CODE
// =====================================

if (
    !isset($_SESSION['admin_reset_user_id']) ||
    !isset($_SESSION['admin_reset_email']) ||
    !isset($_SESSION['admin_reset_verified']) ||
    $_SESSION['admin_reset_verified'] !== true ||
    !isset($_SESSION['admin_reset_id'])
) {
    header("Location: forgot_password.php");
    exit();
}

$user_id = (int) $_SESSION['admin_reset_user_id'];
$email = $_SESSION['admin_reset_email'];
$reset_id = (int) $_SESSION['admin_reset_id'];

$error = "";
$success = "";


// =====================================
// RESET PASSWORD
// =====================================

if (isset($_POST['reset_password'])) {

    /*
    |-------------------------------------
    | DO NOT TRIM PASSWORD
    |-------------------------------------
    */

    $new_password =
        $_POST['new_password'] ?? "";

    $confirm_password =
        $_POST['confirm_password'] ?? "";


    // =================================
    // CHECK EMPTY
    // =================================

    if (
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $error =
            "Please enter your new password.";


    // =================================
    // CHECK PASSWORD MATCH
    // =================================

    } elseif ($new_password !== $confirm_password) {

        $error =
            "Passwords do not match.";


    // =================================
    // CHECK PASSWORD LENGTH
    // =================================

    } elseif (strlen($new_password) < 8) {

        $error =
            "Password must be at least 8 characters.";


    // =================================
    // CHECK UPPERCASE
    // =================================

    } elseif (!preg_match('/[A-Z]/', $new_password)) {

        $error =
            "Password must contain at least one uppercase letter.";


    // =================================
    // CHECK LOWERCASE
    // =================================

    } elseif (!preg_match('/[a-z]/', $new_password)) {

        $error =
            "Password must contain at least one lowercase letter.";


    // =================================
    // CHECK NUMBER
    // =================================

    } elseif (!preg_match('/[0-9]/', $new_password)) {

        $error =
            "Password must contain at least one number.";


    // =================================
    // CHECK SPECIAL CHARACTER
    // =================================

    } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {

        $error =
            "Password must contain at least one special character.";


    } else {

        // =================================
        // HASH NEW PASSWORD
        // =================================

        $hashed_password =
            password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


        // =================================
        // UPDATE ADMIN PASSWORD
        // =================================

        $stmt = $conn->prepare("
            UPDATE users
            SET password = ?
            WHERE id = ?
            AND email = ?
            AND role = 'admin'
        ");


        if (!$stmt) {

            $error =
                "Database Error: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "sis",
                $hashed_password,
                $user_id,
                $email
            );


            if ($stmt->execute()) {

                // =================================
                // CHECK IF ADMIN WAS UPDATED
                // =================================

                if ($stmt->affected_rows < 1) {

                    $error =
                        "Unable to change password. Admin account was not found.";

                    $stmt->close();

                } else {

                    $stmt->close();


                    // =================================
                    // MARK RESET RECORD AS USED
                    // =================================

                    $used = $conn->prepare("
                        UPDATE password_resets
                        SET verified = 2
                        WHERE id = ?
                    ");


                    if ($used) {

                        $used->bind_param(
                            "i",
                            $reset_id
                        );

                        $used->execute();
                        $used->close();

                    }


                    // =================================
                    // CLEAR ADMIN RESET SESSION
                    // =================================

                    unset(
                        $_SESSION['admin_reset_user_id'],
                        $_SESSION['admin_reset_email'],
                        $_SESSION['admin_reset_code'],
                        $_SESSION['admin_reset_code_time'],
                        $_SESSION['admin_reset_verified'],
                        $_SESSION['admin_reset_id']
                    );


                    // =================================
                    // SUCCESS
                    // =================================

                    $success =
                        "Password successfully changed.";

                }

            } else {

                $error =
                    "Unable to change password: " .
                    $stmt->error;

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

<title>Reset Password</title>

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


.reset-container {

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


.input-group-text {

    background:
        rgba(255,255,255,.15);

    border:
        1px solid
        rgba(255,255,255,.30);

    color: white;

    cursor: pointer;

}


.btn-reset {

    height: 48px;

    background: #ffc107;

    color: #002147;

    border: none;

    font-weight: bold;

}


.btn-reset:hover {

    background: #ffca2c;

    color: #002147;

}


.btn-login {

    height: 48px;

    background:
        rgba(255,255,255,.20);

    border:
        1px solid
        rgba(255,255,255,.30);

    color: white;

}


.btn-login:hover {

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


.password-policy {

    color: #f1f1f1;

    font-size: 13px;

    line-height: 1.6;

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


<p class="description">

Create a new password for:

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


<?php if (!empty($success)) { ?>

<div class="alert alert-success">

<i class="bi bi-check-circle-fill me-1"></i>

<?= htmlspecialchars($success); ?>

</div>


<div class="mt-3">

<a
    href="login.php"
    class="btn btn-login w-100"
>

<i class="bi bi-box-arrow-in-right me-1"></i>

Go to Login

</a>

</div>


<?php } else { ?>


<div class="info-box mb-4">

<i class="bi bi-shield-lock me-1"></i>

Your verification code has been confirmed.
You can now create a new password.

</div>


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
    placeholder="Enter new password"
    minlength="8"
    required
>


<span
    class="input-group-text"
    onclick="togglePassword('new_password','eye1')"
>

<i
    class="bi bi-eye"
    id="eye1"
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
    placeholder="Confirm new password"
    minlength="8"
    required
>


<span
    class="input-group-text"
    onclick="togglePassword('confirm_password','eye2')"
>

<i
    class="bi bi-eye"
    id="eye2"
></i>

</span>

</div>

</div>


<!-- PASSWORD POLICY -->

<div class="password-policy mb-4">

<strong>Password must contain:</strong>

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

</div>


<button
    type="submit"
    name="reset_password"
    class="btn btn-reset w-100"
>

<i class="bi bi-key-fill me-1"></i>

Change Password

</button>


</form>


<?php } ?>


</div>


<script>

function togglePassword(
    inputId,
    iconId
) {

    const password =
        document.getElementById(inputId);

    const icon =
        document.getElementById(iconId);


    if (password.type === "password") {

        password.type = "text";

        icon.className =
            "bi bi-eye-slash";

    } else {

        password.type = "password";

        icon.className =
            "bi bi-eye";

    }

}

</script>


</body>

</html>