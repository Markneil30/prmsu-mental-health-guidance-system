<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
   header("Location: login.php");
exit();
}

$message = "";

if(isset($_POST['submit'])){

    $student_id = $_SESSION['user_id'];

    $appointment_date = mysqli_real_escape_string($conn,$_POST['appointment_date']);

    $appointment_time = mysqli_real_escape_string($conn,$_POST['appointment_time']);

    $purpose = mysqli_real_escape_string($conn,$_POST['purpose']);

    $description = mysqli_real_escape_string($conn,$_POST['description']);

    $status = "Pending";

    $sql = "INSERT INTO appointments
    (
        student_id,
        counselor_id,
        appointment_date,
        appointment_time,
        purpose,
        description,
        status
    )

    VALUES
    (
        '$student_id',
        NULL,
        '$appointment_date',
        '$appointment_time',
        '$purpose',
        '$description',
        '$status'
    )";

    if(mysqli_query($conn,$sql)){

        $message = "
        <div class='alert alert-success'>
        Appointment request submitted successfully.
        </div>";

    }else{

        $message = "
        <div class='alert alert-danger'>
        ".mysqli_error($conn)."
        </div>";

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Request Appointment</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
    font-family:Poppins,sans-serif;
}

.card{
    margin-top:40px;
    border-radius:15px;
}

</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>

<i class="bi bi-calendar-check"></i>

Request Counseling Appointment

</h4>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">Appointment Date</label>

<input
type="date"
name="appointment_date"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Appointment Time</label>

<input
type="time"
name="appointment_time"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Purpose</label>

<input
type="text"
name="purpose"
class="form-control"
placeholder="Example: Academic Concern"
required>

</div>

<div class="mb-3">

<label class="form-label">Description</label>

<textarea
name="description"
class="form-control"
rows="5"
placeholder="Please describe your concern..."
required></textarea>

</div>

<button
type="submit"
name="submit"
class="btn btn-primary">

<i class="bi bi-send"></i>

Submit Request

</button>

<a
href="dashboard.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>