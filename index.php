<?php
// Start the session
session_start();

// Establish the database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pd";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is already logged in
if (isset($_SESSION['email'])) {
    $role = $_SESSION['role'];

    // Determine the appropriate redirection based on the user's role
    if ($role == 'student') {
        header("Location: chat.php");
        exit();
    } elseif ($role == 'professor') {
        header("Location: profesori.php");
        exit();
    } else {
        echo "Invalid role.";
    }
}

// Retrieve the form data and perform login processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['fjalekalimi'];

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT * FROM regjistrimi WHERE email = ? AND fjalekalimi = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the query returned a row
    if ($result->num_rows == 1) {
        // Fetch the user's role from the result
        $row = $result->fetch_assoc();
        $role = $row['roli'];

        // Store user information in the session
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;

        // Determine the appropriate redirection based on the user's role
        if ($role == 'student') {
            header("Location: chat.php");
            exit();
        } elseif ($role == 'professor') {
            header("Location: profesori.php");
            exit();
        } else {
            echo "Invalid role.";
        }
    } else {
        echo "Invalid email or password.";
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        h1 {
            color: #333;
        }
        
        form {
            margin-top: 20px;
        }
        
        label, input {
            display: block;
            margin-bottom: 10px;
        }
        
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #555;
        }

        a {
            text-decoration: none;
            color: black;
            font-size: smaller;
        }
    </style>
</head>
<body>
    <h1>Login</h1>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        
        <label for="fjalekalimi">Fjalëkalimi:</label>
        <input type="password" name="fjalekalimi" id="fjalekalimi" required>
        
        <input type="submit" value="Login">
        <a href="regjistrohu.php">Nuk keni llogari? Kyçu</a>
    </form>
</body>
</html>
