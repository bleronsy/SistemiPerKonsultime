<?php
$host = 'localhost';
$port = 12345;

$null = NULL;

// Create WebSocket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, $port);
socket_listen($socket);

// Create & add listening socket to the list
$clients = array($socket);

while (true) {
    $changed = $clients;
    socket_select($changed, $null, $null, 0, 10);

    // Check for new socket
    if (in_array($socket, $changed)) {
        $socket_new = socket_accept($socket);
        $clients[] = $socket_new;

        $header = socket_read($socket_new, 1024);
        performHandshaking($header, $socket_new, $host, $port);

        socket_getpeername($socket_new, $ip);
        $response = mask(json_encode(array('type' => 'system', 'message' => $ip . ' connected')));
        send_message($response);
        
    }

    // Check for any incomming data
    foreach ($changed as $changed_socket) {
        while (socket_recv($changed_socket, $buf, 1024, 0) >= 1) {
            $received_text = unmask($buf);
            $tst_msg = json_decode($received_text);

            if ($tst_msg->type === 'chat') {
                $response_text = mask(json_encode(array('type' => 'usermsg', 'email' => $tst_msg->email, 'message' => $tst_msg->message)));
                send_message($response_text);
            } elseif ($tst_msg->type === 'exit') {
                socket_getpeername($changed_socket, $ip);
                $response_text = mask(json_encode(array('type' => 'system', 'message' => $ip . ' disconnected')));
                send_message($response_text);

                $index = array_search($changed_socket, $clients);
                unset($clients[$index]);
                socket_close($changed_socket);
            }
            break 2;
        }

        $buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
        if ($buf === false) {
            socket_getpeername($changed_socket, $ip);
            $response_text = mask(json_encode(array('type' => 'system', 'message' => $ip . ' disconnected')));
            send_message($response_text);

            $index = array_search($changed_socket, $clients);
            unset($clients[$index]);
            socket_close($changed_socket);
        }
    }
}

// Close the listening socket
socket_close($socket);

function send_message($msg)
{
    global $clients;
    foreach ($clients as $client) {
        @socket_write($client, $msg, strlen($msg));
    }
    return true;
}

// Unmask incoming framed message
function unmask($text)
{
    $length = ord($text[1]) & 127;

    if ($length == 126) {
        $masks = substr($text, 4, 4);
        $data = substr($text, 8);
    } elseif ($length == 127) {
        $masks = substr($text, 10, 4);
        $data = substr($text, 14);
    } else {
        $masks = substr($text, 2, 4);
        $data = substr($text, 6);
    }

    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

// Encode message for transfer to client
function mask($text)
{
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if ($length <= 125) {
        $header = pack('CC', $b1, $length);
    } elseif ($length > 125 && $length < 65536) {
        $header = pack('CCn', $b1, 126, $length);
    } elseif ($length >= 65536) {
        $header = pack('CCNN', $b1, 127, $length);
    }
    return $header . $text;
}

// Handshake new client
function performHandshaking($receved_header, $client_conn, $host, $port)
{
    $headers = array();
    $lines = preg_split("/\r\n/", $receved_header);
    foreach ($lines as $line) {
        $line = rtrim($line);
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $headers[$matches[1]] = $matches[2];
        }
    }

    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "WebSocket-Origin: $host\r\n" .
        "WebSocket-Location: ws://$host:$port/socket.php\r\n" .
        "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    socket_write($client_conn, $upgrade, strlen($upgrade));
}
?>
