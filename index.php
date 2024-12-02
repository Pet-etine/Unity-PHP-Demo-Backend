<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
</head>

<body>
    <?php

    $connection = mysqli_connect("localhost:3306", "root", "", "mysql");

    if (!$connection) { // Check if connection failed
        die("Error: " . mysqli_connect_error());
    }

    echo '<h1>Unity-PHP-Demo backend</h1>';


    mysqli_close($connection);

    ?>
</body>