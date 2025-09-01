<?php
// page.php
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>RH360º</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            text-align: center;
            padding: 50px;
        }
        h1 {
            font-size: 48px;
            margin-bottom: 60px;
            color: #333;
        }
        .container {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        .box {
            width: 250px;
            height: 150px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #444;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.3s;
            text-decoration: none;
        }
        .box:hover {
            transform: translateY(-5px);
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
<h1>RH360º</h1>
<div class="container">
    <a href="../page/page_login.php" class="box">INOV360º</a>
    <a href="...360.php" class="box">...360º</a>
</div>
</body>
</html>
