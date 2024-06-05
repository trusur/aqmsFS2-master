<?php

function hexTo32Float($strHex) {
    $v = hexdec($strHex);
    $x = ($v & ((1 << 23) - 1)) + (1 << 23) * ($v >> 31 | 1);
    $exp = ($v >> 23 & 0xFF) - 127;
    return $x * pow(2, $exp - 23);
}

echo dechex("16129");
echo dechex("5049");
echo "<br>";
echo hexTo32Float(dechex("16129").dechex("5049")) . "<br>";