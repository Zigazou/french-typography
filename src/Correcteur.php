<?php
namespace Zigazou\FrenchTypography;

use Zigazou\FrenchTypography\Operation;

class Correcteur
{
    const FIRST_ELEMENT = true;
    const NOT_FIRST_ELEMENT = false;
    const LAST_ELEMENT = true;
    const NOT_LAST_ELEMENT = false;

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

    const PHONE_NUMBER =
        '(?<![.\d])0\d\s?\d\d\s?\d\d\s?\d\d\s?\d\d(?![.,]?[\d])';

    const NUMBER = '-?(?'
        . '|\d{1,3}(?: \d{3})+(?:[.,]\d+)?'
        . '|\d{1,3}.\d{3}(?:.\d{3})+(?:,\d+)?'
        . '|\d{1,3}.\d{3},\d+'
        . '|\d+(?:[.,]\d+)?'
        . ')';

    const WORD = '\pL+';

    const NOT_A_WORD = '\PL+';

    const ELEMENT_SPLIT = '('
        . '(?<phone>' . self::PHONE_NUMBER . ')|'
        . '(?<unit>' . self::UNIT . ')|'
        . '(?<number>' . self::NUMBER . ')|'
        . '(?<word>' . self::WORD . ')|'
        . '(?<other>' . self::NOT_A_WORD . ')'
        . ')';

    const CLEANSPACES = '/(?'
        . '|\pZ+(\p{Cc}+)\pZ+'
        . '|(\p{Cc}+)\pZ+'
        . '|\pZ+(\p{Cc}+)'
        . '| *([  ]) *'
        . '|( ) +'
        . ')/u';

    public static function cleanSpaces(string $string): string
    {
        return preg_replace(self::CLEANSPACES, '\1', $string);
    }

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
            'phone' => $matches['phone'],
            'unit' => $matches['unit'],
            'number' => $matches['number'],
            'word' => $matches['word'],
            'other' => $matches['other'],
        ];
    }

    public static function correctWord(string $word): string
    {
        static $corrections;
        require_once(__DIR__ . "/frenchtypo.corrections.php");

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

        // Apply stylistic ligatures.
        $word = str_replace(
            ['ffl', 'ffi', 'fl', 'fi', 'ff'],
            //'st'],
            ['ﬄ', 'ﬃ', 'ﬂ', 'ﬁ', 'ﬀ'],
            //'ﬆ'],
            $word
        );

        return $word;
    }

    public static function correctOther(
        string $string, bool $first, bool $last
    ): string {
        static $unicodeFrom;
        static $unicodeTo;
        require_once(__DIR__ . "/frenchtypo.unicode.php");

        // Corrects supernumerary characters.
        $string = preg_replace('/\.\.\.*/u', '...', $string);

        // Converts ascii characters to dedicated Unicode characters.
        $string = str_replace($unicodeFrom, $unicodeTo, $string);

        // No space before, one space after dot and comma.
        $string = preg_replace('/\s*([.,])\p{Zs}*/u', '\1 ', $string);

        // One no-break space before, one space after semicolon, colon,
        // exclamation mark and question mark.
        $string = preg_replace('/\s*([;:!?])\p{Zs}*/u', ' \1 ', $string);

        // Converts english double quotes to french guillemets.
        if (!$last) {
            $string = preg_replace('/"$/u', '«', $string);
        }
        $string = preg_replace('/ ?"([ .,)]|\R|)/u', '»\1', $string);
        $string = preg_replace('/«\p{Zs}*/u', '« ', $string);
        $string = preg_replace('/\p{Zs}*»/u', ' »', $string);

        // Clean spaces.
        $string = self::cleanSpaces($string);
        if ($first)
            $string = ltrim($string);
        if ($last)
            $string = rtrim($string);

        return $string;
    }

    public static function correctUnit(string $string, bool $first): string
    {
        $string = ' ' . ltrim($string);
        // $elements[$index - 1] = rtrim($elements[$index - 1]);

        return $string;
    }

    public static function correctPhone(string $text): string
    {
        $text = preg_replace('/[^\d]/u', '', $text);
        $text = preg_replace('/(\d\d)(?=\d)/u', '\1 ', $text);
        return $text;
    }
    public static function corriger(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $types = self::splitElements($text);
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

            if ($words[$index]) {
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

        return implode('', $elements);
    }
}