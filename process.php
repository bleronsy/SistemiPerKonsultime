<?php
// Establish the database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pd";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the form data
$emri = $_POST['emri'];
$email = $_POST['email'];
$fjalekalimi = $_POST['fjalekalimi'];
$roli = $_POST['role'];

// Prepare and execute the SQL query
$stmt = $conn->prepare("INSERT INTO regjistrimi (emri, email, fjalekalimi, roli) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $emri, $email, $fjalekalimi, $roli);
$stmt->execute();

// Check if the query was successful
if ($stmt->affected_rows > 0) {
    header("Location: index.php");
    exit();
} else {
    echo "Error inserting data: " . $stmt->error;
}

// Close the statement and database connection
$stmt->close();
$conn->close();
?>
