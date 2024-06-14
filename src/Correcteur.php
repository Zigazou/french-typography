<?php
namespace Zigazou\FrenchTypography;

use Zigazou\FrenchTypography\Operation;
use Zigazou\FrenchTypography\FlatEditableHTML;

class Correcteur
{
  /**
   * Indicates this is the first element.
   * 
   * @var bool
   */
  const FIRST_ELEMENT = true;

  /**
   * Indicates this is not the first element.
   * 
   * @var bool
   */
  const NOT_FIRST_ELEMENT = false;

  /**
   * Indicates this is the last element.
   * 
   * @var bool
   */
  const LAST_ELEMENT = true;

  /**
   * Indicates this is not the last element.
   * 
   * @var bool
   */
  const NOT_LAST_ELEMENT = false;

  /**
   * Regular expression for a unit.
   * 
   * @var string
   */
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
  const NUMBER = '-?(?'
    . '|\d{1,3}(?: \d{3})+(?:[.,]\d+)?'
    . '|\d{1,3}.\d{3}(?:.\d{3})+(?:,\d+)?'
    . '|\d{1,3}.\d{3},\d+'
    . '|\d+(?:[.,]\d+)?'
    . ')';

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
   * Regular expression for an inline tag (according to FlatEditableHTML, this
   * is not an actual tag).
   * 
   * @var string
   */
  const INLINE_TAG = '\pZ*' . FlatEditableHTML::INLINETAGCODE . '\pZ*';

  /**
   * Regular expression for a block tag (according to FlatEditableHTML, this is
   * not an actual tag).
   * 
   * @var string
   */
  const BLOCK_TAG =  '\pZ*' . FlatEditableHTML::BLOCKTAGCODE . '\pZ*';

  /**
   * Regular expression for splitting a text into elements of type phone, unit
   * number, word or anything else.
   * 
   * @var string
   */
  const ELEMENT_SPLIT = '('
    . '(?<inlinetag>' . self::INLINE_TAG . ')|'
    . '(?<blocktag>' . self::BLOCK_TAG . ')|'
    . '(?<phone>' . self::PHONE_NUMBER . ')|'
    . '(?<unit>' . self::UNIT . ')|'
    . '(?<number>' . self::NUMBER . ')|'
    . '(?<word>' . self::WORD . ')|'
    . '(?<other>' . self::NOT_A_WORD . ')'
    . ')';

  /**
   * Regular expression used for cleaning spaces.
   * 
   * @var string
   */
  const CLEANSPACES = '/(?'
    . '|\pZ+(\p{Cc}+)\pZ+'
    . '|(\p{Cc}+)\pZ+'
    . '|\pZ+(\p{Cc}+)'
    . '| *([  ]) *'
    . '|( ) +'
    . ')/u';

  /**
   * Removes spaces around punctuation marks.
   * 
   * @param string $string The string to clean.
   * @return string The cleaned string.
   */
  public static function cleanSpaces(string $string): string
  {
    return preg_replace(self::CLEANSPACES, '\1', $string);
  }

  /**
   * Splits a text into elements of type phone, unit, number, word or anything
   * else.
   * 
   * @param string $text The text to split.
   * @return array An array containing the following keys:
   *    - all: An array containing all the elements.
   *    - phone: An array containing the phone numbers.
   *    - unit: An array containing the units.
   *    - number: An array containing the numbers.
   *    - word: An array containing the words.
   *    - other: An array containing the other elements.
   */
  public static function splitElements(string $text): array
  {
    preg_match_all(
      '/' . self::ELEMENT_SPLIT . '/u',
      $text,
      $matches,
      PREG_UNMATCHED_AS_NULL
    );

    return [
      'all' => $matches[0],
      'inlinetag' => $matches['inlinetag'],
      'blocktag' => $matches['blocktag'],
      'phone' => $matches['phone'],
      'unit' => $matches['unit'],
      'number' => $matches['number'],
      'word' => $matches['word'],
      'other' => $matches['other'],
    ];
  }

  /**
   * Corrects a word.
   *
   * This includes:
   * - Restoring accents on first capital letters
   * - Replacing 'oe' by 'œ'
   *
   * @param string $word The word to correct.
   * @return string The corrected word.
   */
  public static function correctWord(string $word): string
  {
    static $corrections;
    require_once (__DIR__ . "/frenchtypo.corrections.php");

    if (mb_strlen($word) === 1) {
      return $word;
    }

    if (isset($corrections[$word])) {
      $operation = $corrections[$word];

      if ($operation & Operation::FIRST_CAPITAL_EACUTE) {
        $word = 'É' . mb_substr($word, 1);
      } else if ($operation & Operation::FIRST_CAPITAL_EGRAVE) {
        $word = 'È' . mb_substr($word, 1);
      } else if ($operation & Operation::FIRST_CAPITAL_ECIRC) {
        $word = 'Ê' . mb_substr($word, 1);
      } else if ($operation & Operation::FIRST_CAPITAL_ACIRC) {
        $word = 'Â' . mb_substr($word, 1);
      } else if ($operation & Operation::FIRST_CAPITAL_CCEDIL) {
        $word = 'Ç' . mb_substr($word, 1);
      } else if ($operation & Operation::FIRST_CAPITAL_OELIG) {
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
   * @param string $string The string to correct.
   * @param bool $first Indicates if this is the first element.
   * @param bool $last Indicates if this is the last element.
   * @return string The corrected string.
   */
  public static function correctOther(
    string $string,
    bool $first,
    bool $last
  ): string {
    static $unicodeFrom;
    static $unicodeTo;
    require_once (__DIR__ . "/frenchtypo.unicode.php");

    // Corrects supernumerary characters.
    $string = preg_replace('/\.\.\.*/u', '...', $string);

    // Converts ascii characters to dedicated Unicode characters.
    $string = str_replace($unicodeFrom, $unicodeTo, $string);

    // No space before, one space after dot and comma.
    $string = preg_replace('/\s*([.,])\p{Zs}*/u', '\1 ', $string);

    // One thin-space before, one space after semicolon, colon,
    // exclamation mark and question mark.
    $string = preg_replace('/\s*([;:!?])\p{Zs}*/u', ' \1 ', $string);

    // Converts english double quotes to french guillemets.
    if (!$last) {
      $string = preg_replace('/"$/u', '«', $string);
    }
    $string = preg_replace('/ ?"([ .,)]|\R|)/u', '»\1', $string);
    $string = preg_replace('/«\p{Zs}*/u', '« ', $string);
    $string = preg_replace('/\p{Zs}*»/u', ' »', $string);

    // Clean spaces.
    $string = self::cleanSpaces($string);
    if ($first)
      $string = ltrim($string);
    if ($last)
      $string = rtrim($string);

    return $string;
  }

  /**
   * Corrects a unit.
   * 
   * @param string $string The unit to correct.
   * @param bool $first Indicates if this is the first element.
   * @return string The corrected unit.
   */
  public static function correctUnit(string $string, bool $first): string
  {
    if ($first) {
      return $string;
    }

    return ' ' . ltrim($string);
  }

  /**
   * Corrects a phone number.
   * 
   * @param string $text The phone number to correct.
   * @return string The corrected phone number.
   */
  public static function correctPhone(string $text): string
  {
    $text = preg_replace('/[^\d]/u', '', $text);
    $text = preg_replace('/(\d\d)(?=\d)/u', '\1 ', $text);
    return $text;
  }

  /**
   * Correct a text that is an inline tag.
   * 
   * @param string $text The text to correct.
   * @param bool $first Indicates if this is the first element.
   * @param bool $last Indicates if this is the last element.
   * @return string The corrected text.
   */
  public static function correctInlineTag(string $text, bool $first, bool $last): string
  {
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
   * @param string $text The block tag to correct.
   * @param bool $first Indicates if this is the first element.
   * @param bool $last Indicates if this is the last element.
   * @return string The corrected block tag.
   */
  public static function correctBlockTag(string $text, bool $first, bool $last): string
  {
    return trim($text);
  }

  /**
   * Corrects a text according to french typography rules.
   * 
   * @param string $text The text to correct.
   * @param bool $isHTML Indicates if the text is an HTML text.
   * @return string The corrected text.
   */
  public static function corriger(string $text, bool $isHTML = FALSE): string
  {
    if ($text === '') {
      return '';
    }

    if ($isHTML) {
      $html = FlatEditableHTML::fromString($text);
      $text = html_entity_decode($html->codes, ENT_QUOTES | ENT_HTML5);
    }

    $types = self::splitElements($text);
    $inlineTags = &$types['inlinetag'];
    $blockTags = &$types['blocktag'];
    $elements = &$types['all'];
    $units = &$types['unit'];
    $phones = &$types['phone'];
    $words = &$types['word'];
    $others = &$types['other'];

    $firstIndex = 0;
    $lastIndex = count($elements) - 1;

    foreach ($elements as $index => $element) {
      $first = $index === $firstIndex;
      $last = $index === $lastIndex;

      if ($inlineTags[$index]) {
        $element = self::correctInlineTag($element, $first, $last);
      } else if ($blockTags[$index]) {
        $element = self::correctBlockTag($element, $first, $last);
      } else if ($words[$index]) {
        $element = self::correctWord($element);
      } else if ($units[$index]) {
        $element = self::correctUnit($element, $first);
      } else if ($phones[$index]) {
        $element = self::correctPhone($element);
      } else if ($others[$index]) {
        $element = self::correctOther($element, $first, $last);
      }

      $elements[$index] = $element;
    }

    if ($isHTML) {
      $html->codes = htmlspecialchars(
        implode('', $elements),
        ENT_QUOTES | ENT_HTML5
      );
      return (string) $html;
    }

    return implode('', $elements);
  }
}