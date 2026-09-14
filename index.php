<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRMSU Mental Health and Guidance Counseling System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>

        *{
            font-family:'Poppins',sans-serif;
        }

        body{
            background:#eef2f7;
        }

        .top-bar{
            height:12px;
            background:#002147;
        }

        .logo{
            width:120px;
        }

        .system-title{
            color:#002147;
            font-weight:700;
            font-size:34px;
        }

        .sub-title{
            color:#555;
            font-size:18px;
        }

        .login-btn{

            width:320px;
            height:60px;

            border-radius:50px;

            background:#002147;

            color:white;

            font-size:22px;

            font-weight:600;

            transition:.3s;

            border:none;

            margin-top:18px;

            box-shadow:0 10px 25px rgba(0,0,0,.15);

        }

        .login-btn:hover{

            background:#0A3D91;

            transform:translateY(-4px);

        }

        footer{

            margin-top:50px;

            color:#777;

        }

    </style>

</head>

<body>

<div class="top-bar"></div>

<div class="container text-center mt-5">

    <img src="assets/images/prmsu-logo.png" class="logo">

    <h2 class="system-title mt-3">
        PRMSU Mental Health and Guidance Counseling System
    </h2>

    <p class="sub-title">
        President Ramon Magsaysay State University<br>
        Sta. Cruz Campus
    </p>

    <div class="mt-4">

        <a href="student/login.php">
            <button class="login-btn">
                <i class="bi bi-mortarboard-fill"></i>
                Student Login
            </button>
        </a>

        <br>

        <a href="counselor/login.php">
            <button class="login-btn">
                <i class="bi bi-person-workspace"></i>
                Guidance Counselor
            </button>
        </a>

        <br>

        <a href="admin/login.php">
            <button class="login-btn">
                <i class="bi bi-person-fill-lock"></i>
                Administrator
            </button>
        </a>

    </div>

</div>

<footer class="text-center">

    © 2026 PRMSU Mental Health and Guidance Counseling System

</footer>

</body>
</html>