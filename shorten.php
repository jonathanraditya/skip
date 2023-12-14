<?php

function charLowercase() {
    $char_lowercase = array();
    foreach (range('A', 'Z') as $char) {
        array_push($char_lowercase, $char);
    }
    return $char_lowercase;
};

function randomLowercaseChars($length) {
    $s = substr(str_shuffle(str_repeat("abcdefghjkmnpqrstuvwxyz", 10)), 0, 5);
    return $s;
}

echo randomLowercaseChars(5);


echo __DIR__;





?>