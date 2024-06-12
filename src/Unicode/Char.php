<?php
namespace Zigazou\FrenchTypography\Unicode;

class Char
{
    const VALID_UTF8_WORD_CHARACTER =
        '/^([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|[\xC4-\xC9][\x80-\xBF])$/';

    public static function isWordCharacter(?string $string): bool
    {

        return ($string === null)
            || (preg_match(self::VALID_UTF8_WORD_CHARACTER, $string) === 1);
    }
}