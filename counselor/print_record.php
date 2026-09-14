
<?php
session_start();

require_once __DIR__ . "/../config/db.php";

// Check counselor login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counselor') {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Get record ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid record ID.");
}

$record_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get actual counselor_id
|--------------------------------------------------------------------------
*/

$counselor_stmt = mysqli_prepare(
    $conn,
    "SELECT counselor_id
     FROM counselors
     WHERE user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $counselor_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($counselor_stmt);

$counselor_result = mysqli_stmt_get_result($counselor_stmt);
$counselor_data = mysqli_fetch_assoc($counselor_result);

mysqli_stmt_close($counselor_stmt);

if (!$counselor_data) {
    die("Counselor account is not properly linked to the counselors table.");
}

$counselor_id = (int) $counselor_data['counselor_id'];


/*
|--------------------------------------------------------------------------
| Get counseling record
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        cr.record_id,
        cr.student_id,
        cr.counselor_id,
        cr.appointment_id,
        cr.session_date,
        cr.notes,
        cr.created_at,

        student_user.fullname AS student_name,
        student_user.email AS student_email,

        students.student_number,
        students.year_level,
        students.course,
        students.gender,

        counselor_user.fullname AS counselor_name,
        counselor_user.email AS counselor_email

    FROM counseling_records cr

    INNER JOIN students
        ON cr.student_id = students.student_id

    INNER JOIN users AS student_user
        ON students.user_id = student_user.id

    INNER JOIN counselors
        ON cr.counselor_id = counselors.counselor_id

    INNER JOIN users AS counselor_user
        ON counselors.user_id = counselor_user.id

    WHERE cr.record_id = ?
      AND cr.counselor_id = ?

    LIMIT 1
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $record_id,
    $counselor_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$record = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$record) {
    die("Counseling record not found or you do not have permission to view this record.");
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

    <title>
        Counseling Record #<?= htmlspecialchars($record['record_id']) ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #eef1f5;
            font-family: 'Poppins', sans-serif;
            color: #222;
        }

        /* =========================
           ACTION BUTTONS
        ========================= */

        .actions {
            text-align: center;
            margin-bottom: 20px;
        }

        .actions .btn {
            margin: 0 3px;
            border-radius: 7px;
            font-size: 13px;
            padding: 8px 15px;
        }

        /* =========================
           A4 REPORT
        ========================= */

        .report {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 15mm 17mm 12mm;
            box-shadow: 0 5px 25px rgba(0, 0, 0, .12);
            position: relative;
        }

        /* =========================
           HEADER
        ========================= */

        .school-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #222;
        }

        .logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
        }

        .school-details {
            text-align: center;
        }

        .school-details h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.3;
        }

        .school-details p {
            margin: 3px 0 0;
            font-size: 10.5px;
            color: #555;
        }

        /* =========================
           TITLE
        ========================= */

        .report-heading {
            text-align: center;
            margin: 15px 0 13px;
        }

        .report-heading h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .confidential {
            display: inline-block;
            margin-top: 5px;
            font-size: 8.5px;
            color: #777;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        /* =========================
           SECTIONS
        ========================= */

        .section {
            margin-top: 11px;
        }

        .section-title {
            background: #f1f3f5;
            border-left: 4px solid #1d4ed8;
            padding: 6px 9px;
            margin-bottom: 7px;

            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        /* =========================
           INFORMATION TABLE
        ========================= */

        .info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9.5px;
        }

        .info-table td {
            border: 1px solid #d5d9de;
            padding: 6px 8px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .info-table .label {
            width: 20%;
            background: #fafafa;
            font-weight: 600;
        }

        .info-table .value {
            width: 30%;
        }

        /* =========================
           COUNSELING NOTES
        ========================= */

        .notes-box {
            border: 1px solid #d5d9de;
            min-height: 95px;
            padding: 9px 10px;

            font-size: 9.5px;
            line-height: 1.55;

            white-space: pre-wrap;
            overflow-wrap: break-word;
        }

        /* =========================
           SIGNATURES
        ========================= */

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 0 35px;
        }

        .signature-line {
            border-top: 1px solid #222;
            padding-top: 5px;
            font-size: 9px;
        }

        .signature-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        /* =========================
           FOOTER
        ========================= */

        .report-footer {
            position: absolute;
            bottom: 9mm;
            left: 17mm;
            right: 17mm;

            border-top: 1px solid #ddd;
            padding-top: 6px;

            text-align: center;
            font-size: 7.5px;
            color: #777;
        }

        /* =========================
           PRINT SETTINGS
        ========================= */

        @media print {

            @page {
                size: A4 portrait;
                margin: 0;
            }

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .actions {
                display: none !important;
            }

            .report {
                width: 210mm;
                height: 297mm;
                margin: 0;
                box-shadow: none;
                padding: 15mm 17mm 12mm;
            }

        }

        /* =========================
           MOBILE PREVIEW
        ========================= */

        @media screen and (max-width: 850px) {

            body {
                padding: 10px;
            }

            .report {
                width: 100%;
                height: auto;
                min-height: auto;
                padding: 25px;
            }

            .school-header {
                flex-direction: column;
                gap: 8px;
            }

            .school-details h1 {
                font-size: 16px;
            }

            .info-table {
                font-size: 9px;
            }

            .signature-table td {
                padding: 0 10px;
            }

            .report-footer {
                position: static;
                margin-top: 30px;
            }

        }

    </style>

</head>

<body>


<!-- =========================
     ACTION BUTTONS
========================= -->

<div class="actions">

    <button
        onclick="window.print()"
        class="btn btn-primary"
    >

        <i class="bi bi-printer"></i>

        Print Report

    </button>


    <a
        href="view_record.php?id=<?= $record['record_id'] ?>"
        class="btn btn-secondary"
    >

        <i class="bi bi-arrow-left"></i>

        Back

    </a>

</div>


<!-- =========================
     REPORT
========================= -->

<div class="report">


    <!-- SCHOOL HEADER -->

    <div class="school-header">

        <img
            src="../assets/images/prmsu-logo.png"
            class="logo"
            alt="PRMSU Logo"
        >

        <div class="school-details">

            <h1>
                President Ramon Magsaysay State University
            </h1>

            <p>
                Guidance and Counseling Office
            </p>

            <p>
                Sta. Cruz Campus
            </p>

        </div>

    </div>


    <!-- REPORT TITLE -->

    <div class="report-heading">

        <h2>
            Counseling Record
        </h2>

        <span class="confidential">
            Confidential Counseling Document
        </span>

    </div>


    <!-- RECORD INFORMATION -->

    <div class="section">

        <div class="section-title">
            Record Information
        </div>

        <table class="info-table">

            <tr>

                <td class="label">
                    Record ID
                </td>

                <td class="value">
                    #<?= htmlspecialchars($record['record_id']) ?>
                </td>

                <td class="label">
                    Session Date
                </td>

                <td class="value">
                    <?= date(
                        "F d, Y",
                        strtotime($record['session_date'])
                    ) ?>
                </td>

            </tr>

            <tr>

                <td class="label">
                    Created
                </td>

                <td colspan="3">

                    <?= date(
                        "F d, Y h:i A",
                        strtotime($record['created_at'])
                    ) ?>

                </td>

            </tr>

        </table>

    </div>


    <!-- STUDENT INFORMATION -->

    <div class="section">

        <div class="section-title">
            Student Information
        </div>

        <table class="info-table">

            <tr>

                <td class="label">
                    Student Name
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['student_name']) ?>
                </td>

                <td class="label">
                    Student Number
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['student_number']) ?>
                </td>

            </tr>

            <tr>

                <td class="label">
                    Year
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['year_level'] ?? '-') ?>
                </td>

                <td class="label">
                    Course
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['course'] ?? '-') ?>
                </td>

            </tr>

            <tr>

                <td class="label">
                    Gender
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['gender'] ?? '-') ?>
                </td>

                <td class="label">
                    Email
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['student_email']) ?>
                </td>

            </tr>

        </table>

    </div>


    <!-- COUNSELOR INFORMATION -->

    <div class="section">

        <div class="section-title">
            Counselor Information
        </div>

        <table class="info-table">

            <tr>

                <td class="label">
                    Counselor Name
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['counselor_name']) ?>
                </td>

                <td class="label">
                    Email
                </td>

                <td class="value">
                    <?= htmlspecialchars($record['counselor_email']) ?>
                </td>

            </tr>

        </table>

    </div>


    <!-- COUNSELING NOTES -->

    <div class="section">

        <div class="section-title">
            Counseling Notes
        </div>

        <div class="notes-box">

            <?= htmlspecialchars($record['notes'] ?? '') ?>

        </div>

    </div>


    <!-- SIGNATURES -->

    <table class="signature-table">

        <tr>

            <td>

                <div class="signature-line">

                    <div class="signature-name">
                        <?= htmlspecialchars($record['student_name']) ?>
                    </div>

                    Student Signature

                </div>

            </td>


            <td>

                <div class="signature-line">

                    <div class="signature-name">
                        <?= htmlspecialchars($record['counselor_name']) ?>
                    </div>

                    Counselor Signature

                </div>

            </td>

        </tr>

    </table>


    <!-- FOOTER -->

    <div class="report-footer">

        This document contains confidential counseling information.
        Unauthorized disclosure or distribution is prohibited.

    </div>


</div>


</body>

</html>

