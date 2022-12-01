<?php
error_reporting(E_ALL);

/* Autorise l'exécution infinie du script, en attente de connexion. */
set_time_limit(0);

/* Active le vidage implicite des buffers de sortie, pour que nous
 * puissions voir ce que nous lisons au fur et à mesure. */
ob_implicit_flush();

$address = '127.0.0.1';
//$address = '10.0.1.182';
//$address = '0.0.0.0';
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

do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "socket_accept() a échoué : raison : " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }
    /* Send instructions. */
    $msg = "\Bienvenue sur le serveur de test PHP.\n" .
        "Pour quitter, tapez 'quit'. Pour éteindre le serveur, tapez 'shutdown'.\n";
    socket_write($msgsock, $msg, strlen($msg));

    do {
        if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
            echo "socket_read() a échoué : raison : " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (!$buf = trim($buf)) {
            continue;
        }
        if ($buf == 'quit') {
            break;
        }
        if ($buf == 'shutdown') {
            socket_close($msgsock);
            break 2;
        }
        $talkback = "PHP: You said '$buf'.\n";

        // https://regex101.com/r/PsVm5C/1
        $pattern = '([A-Z]+) (.*) (HTTP\/(([0-9]+).([0-9]+)))';

        if (preg_match('/' . $pattern . '/', $buf, $result)) {
			
			switch ($result[1]) {
				case 'GET':
					$reponse = '<html><body>Hello World !</body></html>';
				
					$talkback = $result[3] . ' 200 OK' . "\n";
					$talkback .= 'Server: MyPHPServer' . "\n";
					$talkback .= 'Content-Type: text/html;charset=utf8' . "\n";
					$talkback .= 'Content-Length: ' . strlen($reponse) . "\n";
					$talkback .= "\n";
					$talkback .= $reponse;
					break;
				default: 
					$talkback = 'Unkown method';
			}
			
        } else {
			$talkback = 'Unable to read your request';
		} 

        socket_write($msgsock, $talkback, strlen($talkback));
        echo "$buf\n";
    } while (true);
    socket_close($msgsock);
} while (true);

socket_close($sock);