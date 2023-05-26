<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
</head>
<body>
    <h2>User Registration</h2>
    <?php
    // Database connection configuration
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pd";

    // Connect to the database
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form data
        $id = $_POST['id'];
        $emri = $_POST['emri'];
        $mbiemri = $_POST['mbiemri'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Check if the user is a professor or a student and insert into the respective table
        if ($role === 'professor') {
            // Insert into the 'professors' table
            $sql = "INSERT INTO professors (professor_id, professor_name, professor_surname, professor_password) VALUES ('$id', '$emri', '$mbiemri', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "Professor registered successfully!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } elseif ($role === 'student') {
            // Insert into the 'students' table
            $sql = "INSERT INTO students (student_id, student_name, student_surname, student_password) VALUES ('$id', '$emri', '$mbiemri', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "Student registered successfully!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Invalid role selection!";
        }
    }
    ?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="id">ID:</label>
        <input type="text" name="id" id="id" required>
        <br><br>
        <label for="emri">Emri:</label>
        <input type="text" name="emri" id="emri" required>
        <br><br>
        <label for="mbiemri">Mbiemri:</label>
        <input type="text" name="mbiemri" id="mbiemri" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br><br>
        <label for="role">Roli:</label>
        <select name="role" id="role" required>
            <option value="professor">Profesor</option>
            <option value="student">Student</option>
        </select>
        <br><br>
        <input type="submit" value="Register">
        <a href="index.php">Keni llogari? Kyçu</a>
    </form>
</body>
</html>
