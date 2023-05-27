<?php
session_start(); // Start the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the selected professor ID and appointment datetime
    $professorId = $_POST['professor'];
    $appointmentDatetime = $_POST['datetime'];

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
    $insert_query = "INSERT INTO appointments (student_id, professor_id, datetime, status) VALUES ('$studentId', '$professorId', '$appointmentDatetime', 'pending')";

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
        <label for="datetime">Select a Date and Time:</label>
        <input type="datetime-local" name="datetime" id="datetime" required><br><br>

        <input type="submit" value="Schedule">
    </form>

    <h2>Approved Appointments</h2>
    <?php
    if (mysqli_num_rows($approved_appointments_result) > 0) {
        while ($row = mysqli_fetch_assoc($approved_appointments_result)) {
            echo '<p>Professor: ' . $row['professor_id'] . '</p>';
            echo '<p>Date and Time: ' . $row['datetime'] . '</p>';
            echo '<hr>';
        }
    } else {
        echo 'No approved appointments.';
    }
    ?>
</body>
</html>
