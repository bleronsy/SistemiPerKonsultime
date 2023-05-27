<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if appointment_id and decision are set
    if (isset($_POST['appointment_id'], $_POST['decision'])) {
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
        if ($decision === 'prano') {
            $status = 'pranuar';
        } else if ($decision === 'refuzo') {
            $status = 'refuzuar';
        } else {
            echo 'Vendim invalid.';
            exit();
        }

        // Prepare the update query
        $update_query = "UPDATE appointments SET status = '$status' WHERE appointment_id = '$appointmentId'";

        if (mysqli_query($conn, $update_query)) {
            echo "Statusi i konsultimit u përditësua me sukses.";
        } else {
            echo "Problem në përditësimin e statusit të konsultimit: " . mysqli_error($conn);
        }

        // Close the database connection
        mysqli_close($conn);
    }
}

// Delete the appointment if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_appointment'])) {
    // Get the appointment ID to be deleted
    $deleteAppointmentId = $_POST['delete_appointment'];

    // Connect to the database (replace with your database credentials)
    $servername = 'localhost';
    $username = 'root';
    $password_db = '';
    $database = 'pd';

    $conn = mysqli_connect($servername, $username, $password_db, $database);

    // Check connection
    if (!$conn) {
        die('Lidhja dështoi: ' . mysqli_connect_error());
    }

    // Prepare the delete query
    $delete_query = "DELETE FROM appointments WHERE appointment_id = '$deleteAppointmentId'";

    if (mysqli_query($conn, $delete_query)) {
        echo "Konsultimi u shlye me sukses.";
    } else {
        echo "Problem në fshirjen e konsultimit: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profili i profesorit</title>
    <link rel="stylesheet" href="./styles/profesori.css">
</head>
<body>
    <h1>Profili i profesorit</h1>
    <h2>Konsultimet</h2>
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
            echo '<tr><th>ID e Konsultimit</th><th>Studenti</th><th>Fillimi</th><th>Përfundimi</th><th>Statusi</th><th>Vendos</th><th>Fshij</th></tr>';
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
                    echo '<input type="submit" name="decision" value="prano">';
                    echo '<input type="submit" name="decision" value="refuzo">';
                    echo '</form>';
                    echo '</td>';
                } else {
                    echo '<td></td>';
                }
                if ($row['status'] !== 'pending') {
                    echo '<td>';
                    echo '<form method="POST" action="profesori.php">';
                    echo '<input type="hidden" name="delete_appointment" value="' . $row['appointment_id'] . '">';
                    echo '<input type="submit" value="Fshij">';
                    echo '</form>';
                    echo '</td>';
                } else {
                    echo '<td></td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo 'Nuk ka konsultime.';
        }

        // Close the database connection
        mysqli_close($conn);
    } else {
        echo 'Nuk jeni i kyçur si profesor.';
    }
    ?>
</body>
</html>