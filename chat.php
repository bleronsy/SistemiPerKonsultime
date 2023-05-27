<?php
session_start();

if (isset($_GET['logout'])) {
    // Simple exit message
    $logout_message = "<div class='msgln'><span class='left-info'>User <b class='user-name-left'>" . $_SESSION['email'] . "</b> has left the chat session.</span><br></div>";
    file_put_contents("log.html", $logout_message, FILE_APPEND | LOCK_EX);

    session_destroy();
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usermsg'])) {
    $usermsg = $_POST['usermsg'];
    $message = "<div class='msgln'><span class='user-name'>" . $_SESSION['email'] . "</span>: " . $usermsg . "<br></div>";
    file_put_contents("log.html", $message, FILE_APPEND | LOCK_EX);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tuts+ Chat Application</title>
    <meta name="description" content="Tuts+ Chat Application" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div id="wrapper">
        <div id="menu">
            <p class="welcome">Welcome, <b><?php echo $_SESSION['email']; ?></b></p>
            <p class="logout"><a id="exit" href="chat.php?logout=true">Exit Chat</a></p>
        </div>
        <div id="chatbox">
            <?php
            if (file_exists("log.html") && filesize("log.html") > 0) {
                $contents = file_get_contents("log.html");
                echo $contents;
            }
            ?>
        </div>
        <form name="message" action="" method="post">
            <input name="usermsg" type="text" id="usermsg" />
            <input name="submitmsg" type="submit" id="submitmsg" value="Send" />
        </form>
    </div>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript">
        // jQuery Document
        $(document).ready(function () {
            var socket = new WebSocket("ws://localhost:8080/socket.php");

            socket.onopen = function () {
                console.log("Socket connection established");
            };

            socket.onmessage = function (event) {
                var receivedData = JSON.parse(event.data);
                var messageType = receivedData.type;

                if (messageType === 'usermsg') {
                    var email = receivedData.email;
                    var message = receivedData.message;
                    $('#chatbox').append('<div class="msgln"><b>' + email + ':</b> ' + message + '</div>');
                } else if (messageType === 'userlist') {
                    var userList = receivedData.users;
                    var userListHTML = '';

                    for (var i = 0; i < userList.length; i++) {
                        userListHTML += '<div class="user-info">' + userList[i] + '</div>';
                    }

                    $('#userlist').html(userListHTML);
                }

                // Scroll to the bottom of the chatbox
                var chatbox = document.getElementById("chatbox");
                chatbox.scrollTop = chatbox.scrollHeight;
            };

            $("#submitmsg").click(function () {
                var clientmsg = $("#usermsg").val();
                if (clientmsg !== "") {
                    var data = {
                        type: "chat",
                        email: "<?php echo $_SESSION['email']; ?>",
                        message: clientmsg
                    };
                    socket.send(JSON.stringify(data));

                    // Append the sent message to the chatbox
                    $('#chatbox').append('<div class="msgln"><b><?php echo $_SESSION['email']; ?>:</b> ' + clientmsg + '</div>');

                    // Scroll to the bottom of the chatbox
                    var chatbox = document.getElementById("chatbox");
                    chatbox.scrollTop = chatbox.scrollHeight;

                    // Submit the message to the server
                    $.ajax({
                        type: 'POST',
                        url: 'chat.php',
                        data: { usermsg: clientmsg },
                        success: function (data) {
                            console.log("Message submitted successfully");
                        }
                    });
                }
                $("#usermsg").val("");
                return false;
            });

            $("#exit").click(function () {
                var exit = confirm("Are you sure you want to end the session?");
                if (exit == true) {
                    var data = {
                        type: "exit",
                        email: "<?php echo $_SESSION['email']; ?>"
                    };
                    socket.send(JSON.stringify(data));
                    socket.close();
                    window.location = "chat.php?logout=true";
                }
            });

            // Load new messages every 3 seconds
            setInterval(function () {
                // Get current contents from chatbox
                var currentContents = $('#chatbox').html();

                // Load contents from log.html
                $.ajax({
                    url: 'log.html',
                    dataType: 'html',
                    success: function (data) {
                        // Only append new messages to the chatbox
                        if (currentContents !== data) {
                            $('#chatbox').html(data);

                            // Scroll to the bottom of the chatbox
                            var chatbox = document.getElementById("chatbox");
                            chatbox.scrollTop = chatbox.scrollHeight;
                        }
                    }
                });
            }, 3000);
        });
    </script>
</body>
</html>
