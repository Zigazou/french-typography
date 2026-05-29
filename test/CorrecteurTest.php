<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zigazou\FrenchTypography\Correcteur;

/**
 * Tests for the Correcteur class.
 */
final class CorrecteurTest extends TestCase {

  /**
   * Returns a hexadecimal dump of a string.
   *
   * @param string $string
   *   The string to convert.
   *
   * @return string
   *   The hexadecimal representation of the string.
   */
  private static function hexDump(string $string): string {
    $output = '';
    for ($i = 0; $i < strlen($string); $i++) {
      $output .= str_pad(dechex(ord($string[$i])), 2, '0', STR_PAD_LEFT) . " ";
    }

    return trim($output);
  }

  /**
   * Test the splitting of a string into elements.
   *
   * (with the correct classification of each element)
   */
  public function testSplitElements(): void {
    $tests = [
      '' => [
        'all' => [],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [],
        'word' => [],
        'other' => [],
      ],
      ' ' => [
        'all' => [' '],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [],
        'word' => [],
        'other' => [0 => ' '],
      ],
      'a' => [
        'all' => ['a'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [],
        'word' => [0 => 'a'],
        'other' => [],
      ],
      ' a ' => [
        'all' => [' ', 'a', ' '],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [],
        'word' => [1 => 'a'],
        'other' => [0 => ' ', 2 => ' '],
      ],
      '1a1' => [
        'all' => ['1', 'a', '1'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [0 => '1', 2 => '1'],
        'word' => [1 => 'a'],
        'other' => [],
      ],
      '1€' => [
        'all' => ['1', '€'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [1 => '€'],
        'number' => [0 => '1'],
        'word' => [],
        'other' => [],
      ],
      '/a/' => [
        'all' => ['/', 'a', '/'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [],
        'word' => [1 => 'a'],
        'other' => [0 => '/', 2 => '/'],
      ],
      '1 €' => [
        'all' => ['1', ' €'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [1 => ' €'],
        'number' => [0 => '1'],
        'word' => [],
        'other' => [],
      ],
      '1k€' => [
        'all' => ['1', 'k€'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [1 => 'k€'],
        'number' => [0 => '1'],
        'word' => [],
        'other' => [],
      ],
      '1 k€' => [
        'all' => ['1', ' k€'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [1 => ' k€'],
        'number' => [0 => '1'],
        'word' => [],
        'other' => [],
      ],
      '100 000 000 €' => [
        'all' => ['100 000 000', ' €'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [1 => ' €'],
        'number' => [0 => '100 000 000'],
        'word' => [],
        'other' => [],
      ],
      '100.000.000 €' => [
        'all' => ['100.000.000', ' €'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [1 => ' €'],
        'number' => [0 => '100.000.000'],
        'word' => [],
        'other' => [],
      ],
      '403 925,64' => [
        'all' => ['403 925,64'],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [0 => '403 925,64'],
        'word' => [],
        'other' => [],
      ],
      "a\x1Da" => [
        'all' => ["a", "\x1D", "a"],
        'inlinetag' => [],
        'blocktag' => [1 => "\x1D"],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [],
        'word' => [0 => "a", 2 => "a"],
        'other' => [],
      ],
      "a\x1Ea" => [
        'all' => ["a", "\x1E", "a"],
        'inlinetag' => [1 => "\x1E"],
        'blocktag' => [],
        'phone' => [],
        'url' => [],
        'unit' => [],
        'number' => [],
        'word' => [0 => "a", 2 => "a"],
        'other' => [],
      ],
    ];

    foreach ($tests as $string => $expected) {
      $actual = Correcteur::splitElements($string);
      $this->assertSame($expected, $actual);
    }
  }

  /**
   * Test the correction of units, with or without a space before the unit.
   */
  public function testCorrectUnit(): void {
    $tests = [
      " €" => " €",
    ];

    foreach ($tests as $string => $expected) {
      $actual = Correcteur::correctUnit($string, FALSE);
      $this->assertSame($expected, $actual, var_export($string, TRUE));
    }
  }

  /**
   * Test the correction of numbers, with or without a space before the number.
   */
  public function testCorrectOther(): void {
    $tests = [
      "\n" => "\n",
      "\n\n" => "\n\n",
      ".\n\n" => ".\n\n",
      " \n" => "\n",
      "\n " => "\n",
      " \n " => "\n",
      " \n\n " => "\n\n",
    ];

    foreach ($tests as $string => $expected) {
      $actual = Correcteur::correctOther(
        $string,
        Correcteur::NOT_FIRST_ELEMENT,
        Correcteur::NOT_LAST_ELEMENT
      );
      $this->assertSame($expected, $actual, var_export($string, TRUE));
    }
  }

  /**
   * Test the correction of a string, with all the corrections applied.
   */
  public function testCorriger(): void {
    $tests = [
      'Economie' => 'Économie',
      'Céramique' => 'Céramique',
      'céramique' => 'céramique',
      'Musée de la céramique' => 'Musée de la céramique',
      'Musée de la Céramique' => 'Musée de la Céramique',
      'Mus&eacute;e de la C&eacute;ramique' => 'Musée de la Céramique',
      'boeuf' => 'bœuf',
      NULL => '',
      '' => '',
      ' ' => '',
      '  ' => '',
      'ab' => 'ab',
      'a' => 'a',
      '/a/' => '/a/',
      'économie' => 'économie',
      ' économie ' => 'économie',
      ' Economie ' => 'Économie',
      'Economiquement efficace' => 'Économiquement efficace',
      'oeuf314' => 'œuf314',
      'Eric314' => 'Éric314',
      'Hello,world!' => 'Hello, world !',
      'Hello... world!' => 'Hello… world !',
      '10€' => '10 €',
      '10.000,00€' => '10.000,00 €',
      '10.000,00k€' => '10.000,00 k€',
      '10.000,00 €' => '10.000,00 €',
      '10.000,00 k€' => '10.000,00 k€',
      '403 925,64' => '403 925,64',
      '10k€' => '10 k€',
      '10 k€' => '10 k€',
      '"Hello"' => '« Hello »',
      'Hello "Hello"' => 'Hello « Hello »',
      '"Hello" Hello' => '« Hello » Hello',
      'Hello "Hello" Hello' => 'Hello « Hello » Hello',
      "Hello\nWorld!" => "Hello\nWorld !",
      "\n" => "",
      "a\na" => "a\na",
      "a \n a" => "a\na",
      "a\n a" => "a\na",
      "a \na" => "a\na",
      "a      a" => "a a",
      "Hello \n World!" => "Hello\nWorld !",
      "Hello \n\n World!" => "Hello\n\nWorld !",
      "a.\n\nb" => "a.\n\nb",
      "x.\n  \ny" => "x.\n\ny",
      "z.\n\n  t" => "z.\n\nt",
      "f.  \n\ng" => "f.\n\ng",
      "10 m²" => "10 m²",
      "10m×15m" => "10 m×15 m",
      "10,5kWh" => "10,5 kWh",
      "10%" => "10 %",
      "09 99 99 99 99" => "09 99 99 99 99",
      "0999999999" => "09 99 99 99 99",
      "a b" => "a b",
      "a&nbsp;b" => "a b",
      " <strong> a </strong> " => "<strong> a </strong>",
      "a <strong> b </strong> c" => "a <strong> b </strong> c",
      "http://example.com" => "http://example.com",
      "example.com" => "example.com",
      "site example.fr" => "site example.fr",
      "site example.fr/test" => "site example.fr/test",
      "site example.fr/test?query=1" => "site example.fr/test?query=1",
      "site example.fr/test?query=1#fragment" => "site example.fr/test?query=1#fragment",
      "Introduction: Hello world!" => "Introduction : Hello world !",
      "Introduction : Hello world!" => "Introduction : Hello world !",
      "Hello <strong>world</strong>!" => "Hello <strong>world</strong> !",
      "Hello <a href=\"https://example.com\">World</a>!" => "Hello <a href=\"https://example.com\">World</a> !",
      "Hello <a href=\"https://example.com\">World</a>:Coucou le monde" => "Hello <a href=\"https://example.com\">World</a> : Coucou le monde",
      "Hello<strong> <a href=\"https://example.com\">World</a></strong>:Coucou le monde" => "Hello<strong> <a href=\"https://example.com\">World</a></strong> : Coucou le monde",
    ];

    $index = 0;
    foreach ($tests as $string => $expected) {
      $actual = Correcteur::corriger($string, TRUE);

      $message = "Test #$index: expected=" . self::hexDump($expected) . " => actual=" . self::hexDump($actual);
      $this->assertSame($expected, $actual, $message);
      $index++;
    }
  }

}
