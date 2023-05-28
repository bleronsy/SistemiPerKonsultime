<?php
session_start();

if (isset($_GET['logout'])) {
    // Redirect to the appropriate page based on the user's role
    if ($_SESSION['role'] === 'professor') {
        header("Location: profesori.php");
    } else {
        header("Location: studenti.php");
    }
    exit();
}

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usermsg'])) {
    $usermsg = $_POST['usermsg'];
    $message = "<div class='msgln'><span class='user-name'>" . $_SESSION['name'] . "</span>: " . $usermsg . "<br></div>";
    file_put_contents("log.html", $message, FILE_APPEND | LOCK_EX);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importantmsg'])) {
    $importantmsg = $_POST['importantmsg'];
    $message = "<div class='msgln'><span class='user-name'>ME RËNDËSI-" . $_SESSION['name'] . "</span>: " . $importantmsg . "<br></div>";
    file_put_contents("important.html", $message, FILE_APPEND | LOCK_EX);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeimportantmsg'])) {
    file_put_contents("important.html", ""); // Empty the "important.html" file
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Komunikimi me mesazh</title>
    <meta name="description" content="Komunikimi me mesazh" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class= 'main'> 
    <div id="wrapper">
        <div id="menu">
            <p class="welcome">Mirë se vini, <b><?php echo $_SESSION['name']; ?></b></p>
            <p class="logout"><a id="exit" href="chat.php?logout=true">Largohu</a></p>
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
            <input name="submitmsg" type="submit" id="submitmsg" value="Dërgo" />
        </form>
    </div>
    <div id="smallerWrapper">
    <div id="smallerChatbox">
        <?php
        if (file_exists("important.html") && filesize("important.html") > 0) {
            $contents = file_get_contents("important.html");
            echo $contents;
        }
        ?>
    </div>
    <?php if ($_SESSION['role'] === 'professor') : ?>
        <div class="button-group">
        <form name="importantmessage" action="" method="post">
            <input name="importantmsg" type="text" id="importantmsg" />
            <input name="submitimportantmsg" type="submit" id="submitimportantmsg" value="Dërgo diçka me rëndësi" />
        </form>
        <form name="removemessage" action="" method="post">
            <input name="removeimportantmsg" type="submit" id="removeimportantmsg" value="Shlyej mesazhet" />
        </form>
        </div>
    <?php endif; ?>
    </div>
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
                    var name = receivedData.name;
                    var message = receivedData.message;
                    $('#chatbox').append('<div class="msgln"><b>' + name + ':</b> ' + message + '</div>');
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
                        name: "<?php echo $_SESSION['name']; ?>",
                        message: clientmsg
                    };
                    socket.send(JSON.stringify(data));

                    // Append the sent message to the chatbox
                    $('#chatbox').append('<div class="msgln"><b><?php echo $_SESSION['name']; ?>:</b> ' + clientmsg + '</div>');

                    // Scroll to the bottom of the chatbox
                    var chatbox = document.getElementById("chatbox");
                    chatbox.scrollTop = chatbox.scrollHeight;

                    // Submit the message to the server
                    $.ajax({
                        type: 'POST',
                        url: 'chat.php',
                        data: { usermsg: clientmsg },
                        success: function (data) {
                            console.log("Mesazhi u dërgua me sukses");
                        }
                    });
                }
                $("#usermsg").val("");
                return false;
            });

            $("#exit").click(function () {
                var exit = confirm("A jeni i sigurt?");
                if (exit == true) {
                    var data = {
                        type: "exit",
                        name: "<?php echo $_SESSION['name']; ?>"
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

            // Load new messages for smallerChatbox every 3 seconds
            setInterval(function () {
                // Get current contents from smallerChatbox
                var currentContents = $('#smallerChatbox').html();

                // Load contents from important.html
                $.ajax({
                    url: 'important.html',
                    dataType: 'html',
                    success: function (data) {
                        // Only append new messages to the smallerChatbox
                        if (currentContents !== data) {
                            $('#smallerChatbox').html(data);
                        }
                    }
                });
            }, 3000);


            // Submit important message to the server
            $("#submitimportantmsg").click(function () {
                var importantmsg = $("#importantmsg").val();
                if (importantmsg !== "") {
                    $.ajax({
                        type: 'POST',
                        url: 'chat.php',
                        data: { importantmsg: importantmsg },
                        success: function (data) {
                            console.log("Mesazhi i rëndësishëm u dërgua me sukses");
                            // Append the sent message to the smaller chatbox
                            $('#smallerChatbox').append('<div class="msgln"><b><?php echo $_SESSION['name']; ?>:</b> ' + importantmsg + '</div>');
                            // Clear the input field
                            $("#importantmsg").val("");
                        }
                    });
                }
                return false;
            });

            // Remove important messages from the smaller chatbox
            $("#removeimportantmsg").click(function () {
                if (confirm("A jeni i sigurt që doni të fshini të gjitha mesazhet e rëndësishme?")) {
                    $.ajax({
                        type: 'POST',
                        url: 'chat.php',
                        data: { removeimportantmsg: true },
                        success: function (data) {
                            console.log("Mesazhet e rëndësishme u fshinë me sukses");
                            // Clear the smaller chatbox
                            $('#smallerChatbox').html("");
                        }
                    });
                }
                return false;
            });
        });
        
    </script>
</body>
</html>
