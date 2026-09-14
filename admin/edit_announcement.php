<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: announcements.php");
    exit();
}

$id = intval($_GET['id']);

$query = mysqli_query($conn,"SELECT * FROM announcements WHERE announcement_id='$id'");

if(mysqli_num_rows($query)==0){
    header("Location: announcements.php");
    exit();
}

$row = mysqli_fetch_assoc($query);

$message="";

if(isset($_POST['update'])){

    $title = mysqli_real_escape_string($conn,$_POST['title']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);

    $sql = "UPDATE announcements
            SET
            title='$title',
            description='$description'
            WHERE announcement_id='$id'";

    if(mysqli_query($conn,$sql)){

        header("Location: announcements.php?updated=1");
        exit();

    }else{

        $message="<div class='alert alert-danger'>
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

<title>Edit Announcement</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

<div class="card-header bg-warning">

<h4>Edit Announcement</h4>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">

<label>Title</label>

<input
type="text"
name="title"
class="form-control"
value="<?= htmlspecialchars($row['title']); ?>"
required>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"
rows="6"
required><?= htmlspecialchars($row['description']); ?></textarea>

</div>

<button
type="submit"
name="update"
class="btn btn-warning">

Update Announcement

</button>

<a
href="announcements.php"
class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>