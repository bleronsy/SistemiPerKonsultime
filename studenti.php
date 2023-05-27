<?php
session_start(); // Start the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the selected professor ID, appointment start datetime, and end datetime
    $professorId = $_POST['professor'];
    $appointmentStartDatetime = $_POST['datetime_start'];
    $appointmentEndDatetime = $_POST['datetime_end'];

    // Get the logged-in student ID from the session
    $studentId = $_SESSION['student_id'];

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

    // Insert the appointment into the appointments table
    $insert_query = "INSERT INTO appointments (student_id, professor_id, datetime_start, datetime_end, status) VALUES ('$studentId', '$professorId', '$appointmentStartDatetime', '$appointmentEndDatetime', 'pending')";

    if (mysqli_query($conn, $insert_query)) {
        echo "Appointment scheduled successfully.";
    } else {
        echo "Error scheduling the appointment: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}

// Retrieve approved appointments
$studentId = $_SESSION['student_id']; // Retrieve student ID from session

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

// Fetch the approved appointments for the student from the database
$approved_appointments_query = "SELECT * FROM appointments WHERE student_id = '$studentId' AND status = 'accepted'";
$approved_appointments_result = mysqli_query($conn, $approved_appointments_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>
    <h1>Student Dashboard</h1>
    <h2>Schedule an Appointment</h2>
    <form method="POST" action="studenti.php">
        <?php
        // Fetch the list of professors from the database
        $professor_query = "SELECT * FROM professors";
        $professor_result = mysqli_query($conn, $professor_query);

        if (mysqli_num_rows($professor_result) > 0) {
            echo '<label for="professor">Select a Professor:</label>';
            echo '<select name="professor" id="professor" required>';
            while ($row = mysqli_fetch_assoc($professor_result)) {
                echo '<option value="' . $row['professor_id'] . '">' . $row['professor_name'] . '</option>';
            }
            echo '</select>';
        }
        ?>
        <br><br>
        <label for="datetime_start">Select a Start Date and Time:</label>
        <input type="datetime-local" name="datetime_start" id="datetime_start" required><br><br>
        <label for="datetime_end">Select an End Date and Time:</label>
        <input type="datetime-local" name="datetime_end" id="datetime_end" required><br><br>

        <input type="submit" value="Schedule">
    </form>
    <h2>Approved Appointments</h2>
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

            echo '<p>Professor: ' . $professorName . '</p>';
            echo '<p>Start Date and Time: ' . $row['datetime_start'] . '</p>';
            echo '<p>End Date and Time: ' . $row['datetime_end'] . '</p>';
            echo '<hr>';
        }
    } else {
        echo 'No approved appointments.';
    }
    ?>
</body>
</html>
