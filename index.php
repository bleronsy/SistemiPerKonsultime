<?php
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted ID and password
    $id = $_POST['id'];
    $password = $_POST['password'];

    // Connect to the database (replace with your database credentials)
    $servername = 'localhost';
    $username = 'root';
    $password_db = '';
    $database = 'pd';

    $conn = mysqli_connect($servername, $username, $password_db, $database);

    // Check connection
    if (!$conn) {
        die('Connection failed: ' . mysqli_connect_error());
    }

    // Check if the user is a student
    $student_query = "SELECT * FROM students WHERE student_id = '$id' AND student_password = '$password'";
    $student_result = mysqli_query($conn, $student_query);

    // Check if the student login is successful
    if (mysqli_num_rows($student_result) === 1) {
        // Redirect to studenti.php
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

        // Start the session and set the professor ID
        session_start();
        $_SESSION['professor_id'] = $professorId;

        // Redirect to profesori.php
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
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="POST" action="index.php">
        <label for="id">ID:</label>
        <input type="text" name="id" id="id" required><br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>
