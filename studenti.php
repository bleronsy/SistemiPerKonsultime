<?php
session_start(); // Start the session
$conditionMet = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the selected professor ID, appointment start datetime, and end datetime
    $professorId = $_POST['professor'];
    $appointmentStartDatetime = $_POST['datetime_start'];
    $appointmentEndDatetime = $_POST['datetime_end'];

    // Get the logged-in student ID from the session
    $studentId = $_SESSION['student_id'];

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
    $queryLiveAppoint = "SELECT * FROM appointments WHERE student_id = {$_SESSION['student_id']} AND status = 'pranuar' AND  NOW() BETWEEN datetime_start AND datetime_end";
    $liveResults = mysqli_query($conn, $queryLiveAppoint);
    if (mysqli_num_rows($liveResults) > 0) {
        $conditionMet = true;
    }

    // Insert the appointment into the appointments table
    $insert_query = "INSERT INTO appointments (student_id, professor_id, datetime_start, datetime_end, status) VALUES ('$studentId', '$professorId', '$appointmentStartDatetime', '$appointmentEndDatetime', 'në pritje')";

    if (mysqli_query($conn, $insert_query)) {
        echo "Konsultimi u dërgua me sukses.";
    } else {
        echo "Problem në aranzhimin e konsultimit: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}

// Retrieve approved appointments
$studentId = $_SESSION['student_id']; // Retrieve student ID from session

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

// Fetch the live appointments for the student from the database
$queryLiveAppoint = "SELECT * FROM appointments WHERE student_id = '$studentId' AND status = 'pranuar' AND NOW() BETWEEN datetime_start AND datetime_end";
$liveResults = mysqli_query($conn, $queryLiveAppoint);

// Check if there are live appointments
if (mysqli_num_rows($liveResults) > 0) {
    $conditionMet = true;
}

// Fetch the approved appointments for the student from the database
$approved_appointments_query = "SELECT * FROM appointments WHERE student_id = '$studentId' AND status = 'pranuar'";
$approved_appointments_result = mysqli_query($conn, $approved_appointments_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profili i studentit</title>
    <link rel="stylesheet" href="./styles/studenti.css">
</head>
<body>
    <h1>Profili i studentit</h1>
    <div style="margin-bottom: 30px;"><a href='logout.php'>Log out</a></div>
    <div>
        Konsiltimet ne zhvillim:
        <?php

        if ($conditionMet) {
            while ($row = mysqli_fetch_assoc($liveResults)) {
                $professorId = $row['professor_id'];

                // Fetch the professor's name from the professors table
                $professor_query = "SELECT professor_name FROM professors WHERE professor_id = '$professorId'";
                $professor_result = mysqli_query($conn, $professor_query);

                if (mysqli_num_rows($professor_result) > 0) {
                    $professor_row = mysqli_fetch_assoc($professor_result);
                    $professorName = $professor_row['professor_name'];
                } else {
                    $professorName = 'Unknown';
                }

                echo '<p>Profesori: ' . $professorName . '</p>';
                echo '<p>Data dhe ora e fillimit: ' . $row['datetime_start'] . '</p>';
                echo '<p>Data dhe ora e përfundimit: ' . $row['datetime_end'] . '</p>';
                echo '<br>';
                echo '<a href="http://localhost:5173">Shko te takimi</a>';
                echo '<hr>';
            }
        } else {
            echo 'Nuk keni konsultime të pranuara nga profesorët ne kete kohe.';
        }
        ?>
    </div>
    <h2>Cakto një konsultim</h2>
    <form method="POST" action="studenti.php">
        <?php
        // Fetch the list of professors from the database
        $professor_query = "SELECT * FROM professors";
        $professor_result = mysqli_query($conn, $professor_query);

        if (mysqli_num_rows($professor_result) > 0) {
            echo '<label for="professor">Zgjedh një profesor:</label>';
            echo '<select name="professor" id="professor" required>';
            while ($row = mysqli_fetch_assoc($professor_result)) {
                echo '<option value="' . $row['professor_id'] . '">' . $row['professor_name'] . " " . $row['professor_surname'] . '</option>';
            }
            echo '</select>';
        }
        ?>
        <br><br>
        <label for="datetime_start">Zgjedh datën dhe orën e fillimit:</label>
        <input type="datetime-local" name="datetime_start" id="datetime_start" required><br><br>
        <label for="datetime_end">Zgjedh datën dhe orën e përfundimit:</label>
        <input type="datetime-local" name="datetime_end" id="datetime_end" required><br><br>

        <input type="submit" value="Dërgo"> <br>
        <a href="./chat.php">Komuniko në chat</a>
    </form>
    <h2>Konsultimet e pranuara nga profesorët</h2>
    <?php
    if (mysqli_num_rows($approved_appointments_result) > 0) {
        while ($row = mysqli_fetch_assoc($approved_appointments_result)) {
            $professorId = $row['professor_id'];

            // Fetch the professor's name from the professors table
            $professor_query = "SELECT professor_name FROM professors WHERE professor_id = '$professorId'";
            $professor_result = mysqli_query($conn, $professor_query);

            if (mysqli_num_rows($professor_result) > 0) {
                $professor_row = mysqli_fetch_assoc($professor_result);
                $professorName = $professor_row['professor_name'];
            } else {
                $professorName = 'Unknown';
            }

            echo '<p>Profesori: ' . $professorName . '</p>';
            echo '<p>Data dhe ora e fillimit: ' . $row['datetime_start'] . '</p>';
            echo '<p>Data dhe ora e përfundimit: ' . $row['datetime_end'] . '</p>';
            echo '<hr>';
        }
    } else {
        echo 'Nuk keni konsultime të pranuara nga profesorët';
    }
    ?>
</body>
</html>