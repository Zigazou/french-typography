<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Zigazou\FrenchTypography\Correcteur;

final class CorrecteurTest extends TestCase
{
  public function testSplitElements(): void
  {
    $tests = [
      '' => [
        'all' => [],
        'inlinetag' => [],
        'blocktag' => [],
        'phone' => [],
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
        'unit' => [1 => ' €'],
        'number' => [0 => '100.000.000'],
        'word' => [],
        'other' => [],
      ],
      "a\x1Da" => [
        'all' => ["a", "\x1D", "a"],
        'inlinetag' => [],
        'blocktag' => [1 => "\x1D"],
        'phone' => [],
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

  public function testCorrectUnit(): void
  {
    $tests = [
      " €" => " €",
    ];

    foreach ($tests as $string => $expected) {
      $actual = Correcteur::correctUnit($string, false);
      $this->assertSame($expected, $actual, var_export($string, true));
    }
  }

  public function testCorrectOther(): void
  {
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
      $this->assertSame($expected, $actual, var_export($string, true));
    }
  }

  public function testCorriger(): void
  {
    $tests = [
      'Economie' => 'Économie',
      'boeuf' => 'bœuf',
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
    ];

    foreach ($tests as $string => $expected) {
      $actual = Correcteur::corriger($string, TRUE);
      $this->assertSame($expected, $actual);
    }
  }
}