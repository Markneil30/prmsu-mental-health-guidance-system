<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: counselors.php");
    exit();
}

$id = intval($_GET['id']);

$query = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id='$id'
    AND role='counselor'
");

if (mysqli_num_rows($query) == 0) {
    header("Location: counselors.php");
    exit();
}

$row = mysqli_fetch_assoc($query);

$message = "";

if (isset($_POST['update'])) {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $sql = "UPDATE users SET
            fullname='$fullname',
            username='$username',
            email='$email'
            WHERE id='$id'
            AND role='counselor'";

    if (mysqli_query($conn, $sql)) {

        $message = "
        <div class='alert alert-success'>
            Counselor updated successfully.
        </div>";

        $query = mysqli_query($conn, "
            SELECT *
            FROM users
            WHERE id='$id'
            AND role='counselor'
        ");

        $row = mysqli_fetch_assoc($query);

    } else {

        $message = "
        <div class='alert alert-danger'>
            " . mysqli_error($conn) . "
        </div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Edit Counselor</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body {
    background: #f4f6f9;
    font-family: Poppins, sans-serif;
}

.card {
    margin-top: 40px;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

</style>

</head>

<body>

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-warning">

            <h4 class="mb-0">

                <i class="bi bi-pencil-square"></i>

                Edit Counselor

            </h4>

        </div>

        <div class="card-body">

            <?php echo $message; ?>

            <form method="POST">

                <!-- Full Name -->

                <div class="mb-3">

                    <label class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="fullname"
                        class="form-control"
                        value="<?php echo htmlspecialchars($row['fullname'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Username -->

                <div class="mb-3">

                    <label class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?php echo htmlspecialchars($row['username'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Email -->

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Buttons -->

                <button
                    type="submit"
                    name="update"
                    class="btn btn-success">

                    <i class="bi bi-save"></i>

                    Update Counselor

                </button>


                <a
                    href="counselors.php"
                    class="btn btn-secondary">

                    Cancel

                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>