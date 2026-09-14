<?php

session_start();
include("../includes/db.php");

// =====================================
// CHECK COUNSELOR LOGIN
// =====================================

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'counselor'
) {
    header("Location: login.php");
    exit();
}


// =====================================
// CHECK RECORD ID
// =====================================

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: records.php");
    exit();
}

$record_id = (int) $_GET['id'];


// =====================================
// GET LOGGED-IN USER ID
// =====================================

$user_id = (int) $_SESSION['user_id'];


// =====================================
// GET ACTUAL COUNSELOR ID
// users.id != counselors.counselor_id
// =====================================

$counselor_stmt = mysqli_prepare(
    $conn,
    "SELECT counselor_id
     FROM counselors
     WHERE user_id = ?
     LIMIT 1"
);

if (!$counselor_stmt) {
    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}

mysqli_stmt_bind_param(
    $counselor_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute(
    $counselor_stmt
);

$counselor_result =
    mysqli_stmt_get_result(
        $counselor_stmt
    );

$counselor_data =
    mysqli_fetch_assoc(
        $counselor_result
    );

mysqli_stmt_close(
    $counselor_stmt
);


// =====================================
// CHECK COUNSELOR LINK
// =====================================

if (!$counselor_data) {

    die(
        "Counselor account is not properly linked to the counselors table."
    );

}

$counselor_id =
    (int) $counselor_data['counselor_id'];


// =====================================
// CHECK RECORD OWNERSHIP
// =====================================

$check_stmt = mysqli_prepare(
    $conn,
    "SELECT record_id
     FROM counseling_records
     WHERE record_id = ?
     AND counselor_id = ?
     LIMIT 1"
);

if (!$check_stmt) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}

mysqli_stmt_bind_param(
    $check_stmt,
    "ii",
    $record_id,
    $counselor_id
);

mysqli_stmt_execute(
    $check_stmt
);

$check_result =
    mysqli_stmt_get_result(
        $check_stmt
    );

$record_exists =
    mysqli_num_rows(
        $check_result
    );

mysqli_stmt_close(
    $check_stmt
);


// =====================================
// RECORD DOES NOT EXIST
// OR NOT OWNED BY COUNSELOR
// =====================================

if ($record_exists === 0) {

    header("Location: records.php");
    exit();

}


// =====================================
// DELETE RECORD
// =====================================

$delete_stmt = mysqli_prepare(
    $conn,
    "DELETE FROM counseling_records
     WHERE record_id = ?
     AND counselor_id = ?"
);

if (!$delete_stmt) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}

mysqli_stmt_bind_param(
    $delete_stmt,
    "ii",
    $record_id,
    $counselor_id
);

mysqli_stmt_execute(
    $delete_stmt
);


// =====================================
// CHECK DELETE RESULT
// =====================================

if (
    mysqli_stmt_affected_rows(
        $delete_stmt
    ) > 0
) {

    mysqli_stmt_close(
        $delete_stmt
    );

    header(
        "Location: records.php?deleted=1"
    );

    exit();

}


// =====================================
// DELETE FAILED
// =====================================

mysqli_stmt_close(
    $delete_stmt
);

header(
    "Location: records.php"
);

exit();

?>