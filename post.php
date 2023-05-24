<?php
session_start();

if (isset($_SESSION['name'])) {
    $text = $_POST['text'];

    // Broadcast the message to all connected clients
    foreach ($clientSockets as $client) {
        if ($client === $socket) {
            continue;
        }
        socket_write($client, $text, strlen($text));
    }
}
?>
