<?php
session_start(); // Start the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted ID and password
    $id = $_POST['id'];
    $password = $_POST['password'];

    // Connect to the database
    $servername = 'localhost';
    $username = 'root';
    $password_db = '';
    $database = 'pd';

    $conn = mysqli_connect($servername, $username, $password_db, $database);

    // Check connection
    if (!$conn) {
        die('Lidhja dështoi: ' . mysqli_connect_error());
    }

    // Check if the user is a student
    $student_query = "SELECT * FROM students WHERE student_id = '$id' AND student_password = '$password'";
    $student_result = mysqli_query($conn, $student_query);

    // Check if the student login is successful
    if (mysqli_num_rows($student_result) === 1) {
        // Get the student ID
        $student_row = mysqli_fetch_assoc($student_result);
        $studentId = $student_row['student_id'];
        $studentName = $student_row['student_name'];

        // Set the student ID in the session
        $_SESSION['student_id'] = $studentId;
        $_SESSION['id'] = $studentId; 
        $_SESSION['name'] = $studentName;
        $_SESSION['role']='student';
        header('Location: studenti.php');
        exit();
    }

    // Check if the user is a professor
    $professor_query = "SELECT * FROM professors WHERE professor_id = '$id' AND professor_password = '$password'";
    $professor_result = mysqli_query($conn, $professor_query);

    // Check if the professor login is successful
    if (mysqli_num_rows($professor_result) === 1) {
        // Get the professor ID
        $professor_row = mysqli_fetch_assoc($professor_result);
        $professorId = $professor_row['professor_id'];
        $professorName = $professor_row['professor_name'];

        // Set the professor ID in the session
        $_SESSION['professor_id'] = $professorId;
        $_SESSION['id'] = $professorId; 
        $_SESSION['name'] =$professorName;
        $_SESSION['role']='professor';

        header('Location: profesori.php');
        exit();
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kyçu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        h1 {
            text-align: center;
        }

        form {
            width: 300px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #4caf50;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        a {
            display: block;
            text-align: center;
            color: #999;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Kyçu</h1>
    <form method="POST" action="index.php">
        <label for="id">ID:</label>
        <input type="text" name="id" id="id" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <input type="submit" value="Login">
        <a href="./regjistrohu.php">Nuk keni llogari? Regjistrohu</a>
    </form>
</body>
</html>