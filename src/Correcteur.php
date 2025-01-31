<?php

namespace Zigazou\FrenchTypography;

/**
 * Class Correcteur provides methods to correct French typography in text.
 */
class Correcteur {
  /**
   * Indicates this is the first element.
   *
   * @var bool
   */
  const FIRST_ELEMENT = TRUE;

  /**
   * Indicates this is not the first element.
   *
   * @var bool
   */
  const NOT_FIRST_ELEMENT = FALSE;

  /**
   * Indicates this is the last element.
   *
   * @var bool
   */
  const LAST_ELEMENT = TRUE;

  /**
   * Indicates this is not the last element.
   *
   * @var bool
   */
  const NOT_LAST_ELEMENT = FALSE;

  /**
   * Regular expression for a unit.
   *
   * @var string
   */
  // phpcs:disable
  const UNIT = '(?<=\d)\s*(?'
    . '|[Mk]?\p{Sc}'
    . '|km\/h'
    . '|m\/s'
    . '|dal'
    . '|atm'
    . '|bar'
    . '|h?Pa'
    . '|Psi'
    . '|k?cal'
    . '|[kMGT]?(?|Wh|eV|Hz|t)'
    . '|[kKMGTPEZY](?|io|o|b)'
    . '|[khdgmµn]?(?|g|m[²³]?)'
    . '|[MGkhdgmµnpfaz]?[ΩAJ]'
    . '|°[CF]'
    . '|[hdcmµ]?l'
    . '|[hcm]?a'
    . '|[mµpf]?s'
    . '|[KO%]'
    . ')(?!\d)';
  // phpcs:enable

  /**
   * Regular expression for a french phone number.
   *
   * @var string
   */
  const PHONE_NUMBER =
    '(?<![.\d])0\d\s?\d\d\s?\d\d\s?\d\d\s?\d\d(?![.,]?[\d])';

  /**
   * Regular expression for a number.
   *
   * @var string
   */
  // phpcs:disable
  const NUMBER = '-?(?'
    . '|\d{1,3}(?: \d{3})+(?:[.,]\d+)?'
    . '|\d{1,3}.\d{3}(?:.\d{3})+(?:,\d+)?'
    . '|\d{1,3}.\d{3},\d+'
    . '|\d+(?:[.,]\d+)?'
    . ')';
  // phpcs:enable

  /**
   * Regular expression for a word.
   *
   * @var string
   */
  const WORD = '\pL+';

  /**
   * Regular expression for a sequence of characters that is not a word.
   *
   * @var string
   */
  const NOT_A_WORD = '\PL+';

  /**
   * Regular expression for an inline tag.
   *
   * According to FlatEditableHTML, this is not an actual tag.
   *
   * @var string
   */
  const INLINE_TAG = '\pZ*' . FlatEditableHTML::INLINETAGCODE . '\pZ*';

  /**
   * Regular expression for a block tag.
   *
   * According to FlatEditableHTML, this is not an actual tag.
   *
   * @var string
   */
  const BLOCK_TAG = '\pZ*' . FlatEditableHTML::BLOCKTAGCODE . '\pZ*';

  /**
   * Regular expression for splitting a text.
   *
   * Text is split into elements of type phone, unit number, word or anything
   * else.
   *
   * @var string
   */
  // phpcs:disable
  const ELEMENT_SPLIT = '('
    . '(?<inlinetag>' . self::INLINE_TAG . ')|'
    . '(?<blocktag>' . self::BLOCK_TAG . ')|'
    . '(?<phone>' . self::PHONE_NUMBER . ')|'
    . '(?<unit>' . self::UNIT . ')|'
    . '(?<number>' . self::NUMBER . ')|'
    . '(?<word>' . self::WORD . ')|'
    . '(?<other>' . self::NOT_A_WORD . ')'
    . ')';
  // phpcs:enable

  /**
   * Regular expression used for cleaning spaces.
   *
   * @var string
   */
  // phpcs:disable
  const CLEANSPACES = '/(?'
    . '|\pZ+(\p{Cc}+)\pZ+'
    . '|(\p{Cc}+)\pZ+'
    . '|\pZ+(\p{Cc}+)'
    . '| *([  ]) *'
    . '|( ) +'
    . ')/u';
  // phpcs:enable

  /**
   * Splits a text into elements.
   *
   * Elements are of type phone, unit, number, word or anything else.
   *
   * @param string $text
   *   The text to split.
   *
   * @return array
   *   An array containing the following keys:
   *   - all: An array containing all the elements.
   *   - phone: An array containing the phone numbers.
   *   - unit: An array containing the units.
   *   - number: An array containing the numbers.
   *   - word: An array containing the words.
   *   - other: An array containing the other elements.
   */
  public static function splitElements(string $text): array {
    preg_match_all(
      '/' . self::ELEMENT_SPLIT . '/u',
      $text,
      $matches,
      PREG_UNMATCHED_AS_NULL
    );

    return [
      'all' => &$matches[0],
      'inlinetag' => array_filter($matches['inlinetag']),
      'blocktag' => array_filter($matches['blocktag']),
      'phone' => array_filter($matches['phone']),
      'unit' => array_filter($matches['unit']),
      'number' => array_filter($matches['number']),
      'word' => array_filter($matches['word']),
      'other' => array_filter($matches['other']),
    ];
  }

  /**
   * Corrects a word.
   *
   * This includes:
   * - Restoring accents on first capital letters
   * - Replacing 'oe' by 'œ'
   *
   * @param string $word
   *   The word to correct.
   *
   * @return string
   *   The corrected word.
   */
  public static function correctWord(string $word): string {
    static $corrections;
    require_once __DIR__ . "/frenchtypo.corrections.php";

    if (mb_strlen($word) === 1) {
      return $word;
    }

    if (isset($corrections[$word])) {
      $operation = $corrections[$word];

      if ($operation & Operation::FIRST_CAPITAL_EACUTE) {
        $word = 'É' . mb_substr($word, 1);
      }
      elseif ($operation & Operation::FIRST_CAPITAL_EGRAVE) {
        $word = 'È' . mb_substr($word, 1);
      }
      elseif ($operation & Operation::FIRST_CAPITAL_ECIRC) {
        $word = 'Ê' . mb_substr($word, 1);
      }
      elseif ($operation & Operation::FIRST_CAPITAL_ACIRC) {
        $word = 'Â' . mb_substr($word, 1);
      }
      elseif ($operation & Operation::FIRST_CAPITAL_CCEDIL) {
        $word = 'Ç' . mb_substr($word, 1);
      }
      elseif ($operation & Operation::FIRST_CAPITAL_OELIG) {
        $word = 'Œ' . mb_substr($word, 2);
      }

      if ($operation & Operation::OELIG) {
        $word = str_replace('oe', 'œ', $word);
      }
    }

    return $word;
  }

  /**
   * Corrects a string that is not a word.
   *
   * This includes:
   * - Replacing supernumerary dots by three dots.
   * - Adding a thin space before semicolon, colon, exclamation mark and
   *   question mark.
   * - Converts double quotes to french guillements.
   * - Converting ascii characters to dedicated Unicode characters.
   * - Cleaning spaces.
   *
   * @param string $string
   *   The string to correct.
   * @param bool $first
   *   Indicates if this is the first element.
   * @param bool $last
   *   Indicates if this is the last element.
   *
   * @return string
   *   The corrected string.
   */
  public static function correctOther(
    string $string,
    bool $first,
    bool $last,
  ): string {
    static $unicodeFrom;
    static $unicodeTo;
    require_once __DIR__ . "/frenchtypo.unicode.php";

    // Converts ascii characters to dedicated Unicode characters.
    $string = str_replace($unicodeFrom, $unicodeTo, $string);

    // No space before, one space after dot and comma.
    $string = preg_replace('/\s*([.,])\p{Zs}*/u', '\1 ', $string);

    // One thin-space before, one space after semicolon, colon,
    // exclamation mark and question mark.
    $string = preg_replace('/\s*([;:!?])\p{Zs}*/u', ' \1 ', $string);

    // Converts english double quotes to french guillemets.
    if (!$last && mb_substr($string, -1, 1) === '"') {
      $string = mb_substr($string, 0, -1) . '«';
    }

    $string = preg_replace('/ ?"([ .,)]|\R|)/u', '»\1', $string);
    $string = preg_replace('/«\p{Zs}*/u', '« ', $string);
    $string = preg_replace('/\p{Zs}*»/u', ' »', $string);

    // Clean spaces.
    if ($first) {
      $string = ltrim($string);
    }
    if ($last) {
      $string = rtrim($string);
    }

    $string = preg_replace(self::CLEANSPACES, '\1', $string);

    return $string;
  }

  /**
   * Corrects a unit.
   *
   * @param string $string
   *   The unit to correct.
   * @param bool $first
   *   Indicates if this is the first element.
   *
   * @return string
   *   The corrected unit.
   */
  public static function correctUnit(string $string, bool $first): string {
    if ($first) {
      return $string;
    }

    return ' ' . ltrim($string);
  }

  /**
   * Corrects a phone number.
   *
   * @param string $text
   *   The phone number to correct.
   *
   * @return string
   *   The corrected phone number.
   */
  public static function correctPhone(string $text): string {
    $text = preg_replace('/[^\d]/u', '', $text);
    $text = preg_replace('/(\d\d)(?=\d)/u', '\1 ', $text);
    return $text;
  }

  /**
   * Correct a text that is an inline tag.
   *
   * @param string $text
   *   The text to correct.
   * @param bool $first
   *   Indicates if this is the first element.
   * @param bool $last
   *   Indicates if this is the last element.
   *
   * @return string
   *   The corrected text.
   */
  public static function correctInlineTag(string $text, bool $first, bool $last): string {
    if ($first) {
      $text = ltrim($text);
    }

    if ($last) {
      $text = rtrim($text);
    }

    $text = preg_replace('/\pZ+/u', ' ', $text);

    return $text;
  }

  /**
   * Corrects a block tag.
   *
   * @param string $text
   *   The block tag to correct.
   * @param bool $first
   *   Indicates if this is the first element.
   * @param bool $last
   *   Indicates if this is the last element.
   *
   * @return string
   *   The corrected block tag.
   */
  public static function correctBlockTag(string $text, bool $first, bool $last): string {
    return trim($text);
  }

  /**
   * Corrects a text according to french typography rules.
   *
   * @param string $text
   *   The text to correct.
   * @param bool $isHTML
   *   Indicates if the text is an HTML text.
   *
   * @return string
   *   The corrected text.
   */
  public static function corriger(string $text, bool $isHTML = FALSE): string {
    // If the text is empty, we don't need to do anything.
    if ($text === '') {
      return '';
    }

    // If the text is an HTML text, we need to convert it to a flat editable
    // HTML. Corrections aren't to be applied on the HTML tags, this will allow
    // later to convert the flat editable HTML back to HTML.
    // A decoding a HTML entities is done, thus avoiding having to test for
    // special chars and their entities counterparts.
    if ($isHTML) {
      $html = FlatEditableHTML::fromString($text);
      $text = html_entity_decode($html->codes, ENT_QUOTES | ENT_HTML5);
    }

    // Extract the different elements of the text.
    $types = self::splitElements($text);
    $elements = &$types['all'];
    $inlineTags = &$types['inlinetag'];
    $blockTags = &$types['blocktag'];
    $units = &$types['unit'];
    $phones = &$types['phone'];
    $words = &$types['word'];
    $others = &$types['other'];

    // Determine indexes of the first and last elements.
    $firstIndex = 0;
    $lastIndex = count($elements) - 1;

    foreach ($inlineTags as $index => $element) {
      $first = $index === $firstIndex;
      $last = $index === $lastIndex;
      $elements[$index] = self::correctInlineTag($element, $first, $last);
    }

    foreach ($blockTags as $index => $element) {
      $first = $index === $firstIndex;
      $last = $index === $lastIndex;
      $elements[$index] = self::correctBlockTag($element, $first, $last);
    }

    foreach ($words as $index => $element) {
      $elements[$index] = self::correctWord($element);
    }

    foreach ($units as $index => $element) {
      $first = $index === $firstIndex;
      $elements[$index] = self::correctUnit($element, $first);
    }

    foreach ($phones as $index => $element) {
      $elements[$index] = self::correctPhone($element);
    }

    foreach ($others as $index => $element) {
      $first = $index === $firstIndex;
      $last = $index === $lastIndex;
      $elements[$index] = self::correctOther($element, $first, $last);
    }

    // Recombine all elements.
    $text = implode('', $elements);

    // Restore the HTML tags if the text is an HTML text.
    if ($isHTML) {
      $html->codes = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
      $text = (string) $html;
    }

    return $text;
  }

}
