<?php

namespace Zigazou\FrenchTypography\Unicode;

/**
 * Class handling Unicode characters.
 */
class Char {
  const VALID_UTF8_WORD_CHARACTER =
        '/^([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|[\xC4-\xC9][\x80-\xBF])$/';

  /**
   * Checks if a string is a valid UTF-8 word character.
   *
   * (a letter, including accented letters, or NULL)
   */
  public static function isWordCharacter(?string $string): bool {
    return ($string === NULL)
            || (preg_match(self::VALID_UTF8_WORD_CHARACTER, $string) === 1);
  }

}
