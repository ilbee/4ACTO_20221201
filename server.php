<?php
error_reporting(E_ALL);

/* Autorise l'exécution infinie du script, en attente de connexion. */
set_time_limit(0);

/* Active le vidage implicite des buffers de sortie, pour que nous
 * puissions voir ce que nous lisons au fur et à mesure. */
ob_implicit_flush();

function encode($string, $clef)
{
    $output = '';
    foreach ( str_split($string) as $char ) {
        $char = ord($char);
        $char = $char + numerize_key($clef);
        $output .= chr($char);
    }

    return base64_encode($output);
}

function numerize_key($key)
{
    $key = str_split($key);
    $key = array_map('ord', $key);
    $key = array_sum($key);
    return $key;
}

function decode($input, $clef)
{
    $input = base64_decode($input);
    $output = '';
    foreach ( str_split($input) as $char ) {
        $char = ord($char);
        $char = $char - numerize_key($clef);
        $output .= chr($char);
    }

    return $output;
}

//$address = '127.0.0.1';
//$address = '10.0.1.182';
$address = '0.0.0.0';
$port = 10000;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() a échoué : raison : " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() a échoué : raison : " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "socket_listen() a échoué : raison : " . socket_strerror(socket_last_error($sock)) . "\n";
}

$dynamicKey = '';
for ($i=0;$i<30;$i++) {
    $dynamicKey .= chr(rand(33,126));
}

do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "socket_accept() a échoué : raison : " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }
    /* Send instructions. */
    $msg = "\Bienvenue sur le serveur de test PHP chiffré." . PHP_EOL .
        'La clef de chiffrement est "' .$dynamicKey . '"' . PHP_EOL;
    socket_write($msgsock, $msg, strlen($msg));

    do {
        if (false === ($buf = socket_read($msgsock, 204800, PHP_NORMAL_READ))) {
            echo "socket_read() a échoué : raison : " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (!$buf = trim($buf)) {
            continue;
        }

        $buf = decode($buf, $dynamicKey);

        switch ($buf) {
            case 'quit':
                $talkback = "Au revoir !\n";
                echo 'Client déconnecté' . PHP_EOL;
                socket_write($msgsock, $talkback, strlen($talkback));
                break 2;
            case 'shutdown':
                $talkback = "Au revoir !\n";
                socket_write($msgsock, $talkback, strlen($talkback));
                socket_close($msgsock);
                break 2;
            default:
                if (preg_match('/([0-9]+) ([0-9]+)/', $buf, $matches)) {
                    $talkback = encode(($matches[1] + $matches[2]), $dynamicKey) . PHP_EOL;
                } else {
                    $talkback = "Commande inconnue : $buf\n";
                }
                break;
        }

        socket_write($msgsock, $talkback, strlen($talkback));
        echo "$buf\n";

    } while (true);
    socket_close($msgsock);
} while (true);

socket_close($sock);