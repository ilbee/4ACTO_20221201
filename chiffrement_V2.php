<?php
$clef = $argv[1];
$input = $argv[2];

$encode = encode($input, $clef);
$decode = decode($encode, $clef);

echo 'Valeur encodée : "'.$encode.'"';
echo PHP_EOL;
echo 'Valeur décodée : "'.$decode.'"';
echo PHP_EOL;

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