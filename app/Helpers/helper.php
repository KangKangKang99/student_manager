<?php

use Random\RandomException;

function generatePassword(): string
{
    try {
        $specialChars = '!@#$%^&*()_-+=';
        $numbers = '0123456789';
        $upperCase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowerCase = 'abcdefghijklmnopqrstuvwxyz';

        $allChars = $specialChars . $numbers . $upperCase . $lowerCase;

        $password = $specialChars[random_int(0, strlen($specialChars) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $upperCase[random_int(0, strlen($upperCase) - 1)];
        $password .= $lowerCase[random_int(0, strlen($lowerCase) - 1)];

        $length = random_int(6, 8); // Random length between 6 to 8
        for ($i = 0; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    } catch (RandomException $e) {
        return '12345678';
    }
}

function replaceAccentedCharacters(string $string): string
{
    return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
}
