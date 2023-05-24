<!DOCTYPE html>
<html>
<head>
    <title>Consultation App</title>
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
        
        label, input, textarea {
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
    </style>
</head>
<body>
    <h1>Welcome to the Consultation App</h1>
    
    <form action="process.php" method="post">
        <label for="emri">Emri:</label>
        <input type="text" name="emri" id="emri" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="fjalekalimi">Fjalëkalimi:</label>
        <input type="password" name="fjalekalimi" id="fjalekalimi" required>
        
        <label for="role">Role:</label>
        <select name="role" id="role" required>
            <option value="">Select role</option>
            <option value="student">Student</option>
            <option value="professor">Professor</option>
        </select>
        
        <input type="submit" value="Start Consultation">
    </form>
</body>
</html>