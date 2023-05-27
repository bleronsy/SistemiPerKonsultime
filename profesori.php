<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the appointment ID and decision (accept or refuse)
    $appointmentId = $_POST['appointment_id'];
    $decision = $_POST['decision'];

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

    // Update the appointment status based on the decision
    if ($decision === 'accept') {
        $status = 'accepted';
    } else if ($decision === 'refuse') {
        $status = 'refused';
    } else {
        echo 'Invalid decision.';
        exit();
    }

    // Prepare the update query
    $update_query = "UPDATE appointments SET status = '$status' WHERE appointment_id = '$appointmentId'";

    if (mysqli_query($conn, $update_query)) {
        echo "Appointment status updated successfully.";
    } else {
        echo "Error updating the appointment status: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Professor Dashboard</title>
    <link rel="stylesheet" href="./styles/profesori.php">
</head>
<body>
    <h1>Professor Dashboard</h1>
    <h2>Appointments</h2>
    <?php
    // Retrieve the professor ID from the logged-in professor (replace with your authentication logic)
    if (isset($_SESSION['professor_id'])) {
        $professorId = $_SESSION['professor_id'];

        $servername = 'localhost';
        $username = 'root';
        $password_db = '';
        $database = 'pd';

        // Connect to the database (replace with your database credentials)
        $conn = mysqli_connect($servername, $username, $password_db, $database);

        // Check connection
        if (!$conn) {
            die('Connection failed: ' . mysqli_connect_error());
        }

        // Fetch the list of appointments for the professor from the database
        $appointments_query = "SELECT * FROM appointments WHERE professor_id = '$professorId'";
        $appointments_result = mysqli_query($conn, $appointments_query);

        if (mysqli_num_rows($appointments_result) > 0) {
            echo '<table>';
            echo '<tr><th>Appointment ID</th><th>Student ID</th><th>Start Datetime</th><th>End Datetime</th><th>Status</th><th>Decision</th></tr>';
            while ($row = mysqli_fetch_assoc($appointments_result)) {
                echo '<tr>';
                echo '<td>' . $row['appointment_id'] . '</td>';
                $studentId = $row['student_id'];

                // Fetch the student's name and surname from the students table
                $student_query = "SELECT student_name, student_surname FROM students WHERE student_id = '$studentId'";
                $student_result = mysqli_query($conn, $student_query);

                if (mysqli_num_rows($student_result) > 0) {
                    $student_row = mysqli_fetch_assoc($student_result);
                    $studentName = $student_row['student_name'];
                    $studentSurname = $student_row['student_surname'];
                } else {
                    $studentName = 'Unknown';
                    $studentSurname = 'Unknown';
                }

                echo '<td>' . $studentName . ' ' . $studentSurname . '</td>';
                echo '<td>' . $row['datetime_start'] . '</td>';
                echo '<td>' . $row['datetime_end'] . '</td>';
                echo '<td>' . $row['status'] . '</td>';
                if ($row['status'] === 'pending') {
                    echo '<td>';
                    echo '<form method="POST" action="profesori.php">';
                    echo '<input type="hidden" name="appointment_id" value="' . $row['appointment_id'] . '">';
                    echo '<input type="submit" name="decision" value="accept">';
                    echo '<input type="submit" name="decision" value="refuse">';
                    echo '</form>';
                    echo '</td>';
                } else {
                    echo '<td></td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo 'No appointments found.';
        }

        // Close the database connection
        mysqli_close($conn);
    } else {
        echo 'You are not logged in as a professor.';
    }
    ?>
</body>
</html>
