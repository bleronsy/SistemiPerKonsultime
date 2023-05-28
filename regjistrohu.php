<!DOCTYPE html>
<html>
<head>
    <title>Regjistrimi i përdoruesit</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        h2 {
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
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
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

        a:hover {
            color: #666;
        }
    </style>
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
        die("Lidhja dështoi: " . $conn->connect_error);
    }

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form data
        $id = $_POST['id'];
        $emri = $_POST['emri'];
        $mbiemri = $_POST['mbiemri'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Check if the user is a professor or a student and insert into the table
        if ($role === 'professor') {
            // Insert into the 'professors' table
            $sql = "INSERT INTO professors (professor_id, professor_name, professor_surname, professor_password) VALUES ('$id', '$emri', '$mbiemri', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "Profesori u regjistrua me sukses!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } elseif ($role === 'student') {
            // Insert into the 'students' table
            $sql = "INSERT INTO students (student_id, student_name, student_surname, student_password) VALUES ('$id', '$emri', '$mbiemri', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "Studenti u regjistrua me sukses!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Zgjedhje e gabuar e rolit!";
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
