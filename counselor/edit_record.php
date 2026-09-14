
<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'counselor') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: records.php");
    exit();
}

$id = intval($_GET['id']);

$query = mysqli_query($conn, "
    SELECT record_id, session_date, notes
    FROM counseling_records
    WHERE record_id='$id'
");

if (mysqli_num_rows($query) == 0) {
    header("Location: records.php");
    exit();
}

$row = mysqli_fetch_assoc($query);

$message = "";

if (isset($_POST['update'])) {

    $session_date = mysqli_real_escape_string(
        $conn,
        $_POST['session_date']
    );

    $notes = mysqli_real_escape_string(
        $conn,
        $_POST['notes']
    );

    $sql = "UPDATE counseling_records SET
        session_date='$session_date',
        notes='$notes'
        WHERE record_id='$id'";

    if (mysqli_query($conn, $sql)) {

        header("Location: records.php?updated=1");
        exit();

    } else {

        $message = "
        <div class='alert alert-danger'>
            " . htmlspecialchars(mysqli_error($conn)) . "
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Counseling Record</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<style>

body {
    background: #f4f6f9;
    font-family: Poppins, sans-serif;
    min-height: 100vh;
    padding: 20px 0;
    overflow-x: hidden;
}

.container {
    width: 100%;
}

.card {
    margin-top: 20px;
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.card-header {
    padding: 18px 22px;
}

.card-header h4 {
    margin: 0;
    font-size: 21px;
    font-weight: 600;
}

.card-body {
    padding: 25px;
}

.form-label {
    font-size: 14px;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 7px;
}

.form-control {
    min-height: 46px;
    font-size: 14px;
    border-radius: 8px;
}

textarea.form-control {
    min-height: 140px;
    resize: vertical;
}

.form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 22px;
}

.form-buttons .btn {
    min-height: 44px;
    padding: 9px 18px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 8px;
}

@media (max-width: 991.98px) {

    body {
        padding: 15px 0;
    }

    .card {
        margin-top: 15px;
    }

    .card-header {
        padding: 17px 20px;
    }

    .card-header h4 {
        font-size: 20px;
    }

    .card-body {
        padding: 22px;
    }
}

@media (max-width: 767.98px) {

    body {
        padding: 10px 0;
    }

    .container {
        padding-left: 12px;
        padding-right: 12px;
    }

    .row {
        margin-left: 0;
        margin-right: 0;
    }

    .col-md-8 {
        padding-left: 0;
        padding-right: 0;
    }

    .card {
        margin-top: 10px;
        border-radius: 12px;
    }

    .card-header {
        padding: 17px 18px;
    }

    .card-header h4 {
        font-size: 19px;
        line-height: 1.3;
    }

    .card-body {
        padding: 20px 18px;
    }

    .mb-3 {
        margin-bottom: 18px !important;
    }

    .form-label {
        font-size: 14px;
        margin-bottom: 7px;
    }

    .form-control {
        min-height: 48px;
        font-size: 14px;
        padding: 10px 12px;
    }

    textarea.form-control {
        min-height: 150px;
    }

    .form-buttons {
        flex-direction: column;
        gap: 10px;
        margin-top: 24px;
    }

    .form-buttons .btn {
        width: 100%;
        min-height: 46px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {

    body {
        padding: 6px 0;
    }

    .container {
        padding-left: 9px;
        padding-right: 9px;
    }

    .card {
        margin-top: 6px;
        border-radius: 11px;
    }

    .card-header {
        padding: 16px;
    }

    .card-header h4 {
        font-size: 18px;
    }

    .card-body {
        padding: 18px 15px;
    }

    .form-label {
        font-size: 14px;
    }

    .form-control {
        min-height: 48px;
        font-size: 14px;
    }

    textarea.form-control {
        min-height: 145px;
    }

    .form-buttons .btn {
        min-height: 47px;
        font-size: 14px;
    }
}

@media (max-width: 360px) {

    .container {
        padding-left: 7px;
        padding-right: 7px;
    }

    .card-header {
        padding: 15px;
    }

    .card-header h4 {
        font-size: 17px;
    }

    .card-body {
        padding: 17px 13px;
    }

    .form-control {
        min-height: 47px;
        font-size: 13.5px;
    }

    .form-label {
        font-size: 13.5px;
    }

    textarea.form-control {
        min-height: 140px;
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-12 col-md-8">

            <div class="card shadow">

                <div class="card-header bg-warning">

                    <h4>Edit Counseling Record</h4>

                </div>

                <div class="card-body">

                    <?= $message; ?>

                    <form method="POST">

                        <!-- SESSION DATE -->

                        <div class="mb-3">

                            <label class="form-label">
                                Session Date
                            </label>

                            <input
                                type="date"
                                name="session_date"
                                class="form-control"
                                value="<?= htmlspecialchars($row['session_date'] ?? ''); ?>"
                                required
                            >

                        </div>

                        <!-- NOTES -->

                        <div class="mb-3">

                            <label class="form-label">
                                Notes
                            </label>

                            <textarea
                                name="notes"
                                class="form-control"
                                rows="5"
                                required
                            ><?= htmlspecialchars($row['notes'] ?? ''); ?></textarea>

                        </div>

                        <!-- BUTTONS -->

                        <div class="form-buttons">

                            <button
                                type="submit"
                                name="update"
                                class="btn btn-warning"
                            >
                                Update Record
                            </button>

                            <a
                                href="records.php"
                                class="btn btn-secondary"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>

