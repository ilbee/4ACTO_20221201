<?php
$input = $argv[1];

$encode = encode($input);
$decode = decode($encode);

echo 'Valeur encodée : '.$encode;
echo PHP_EOL;
echo 'Valeur décodée : '.$decode;
echo PHP_EOL;

function encode($string)
{
    $output = '';
    foreach ( str_split($string) as $char ) {
        $char = ord($char);
        $char = $char + 1;
        $output .= chr($char);
    }

    return $output;
}

function decode($input)
{
    $output = '';
    foreach ( str_split($input) as $char ) {
        $char = ord($char);
        $char = $char - 1;
        $output .= chr($char);
    }

    return $output;
}