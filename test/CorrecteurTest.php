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
                'phone' => [],
                'unit' => [],
                'number' => [],
                'word' => [],
                'other' => [],
            ],
            ' ' => [
                'all' => [' '],
                'phone' => [null],
                'unit' => [null],
                'number' => [null],
                'word' => [null],
                'other' => [' '],
            ],
            'a' => [
                'all' => ['a'],
                'phone' => [null],
                'unit' => [null],
                'number' => [null],
                'word' => ['a'],
                'other' => [null],
            ],
            ' a ' => [
                'all' => [' ', 'a', ' '],
                'phone' => [null, null, null],
                'unit' => [null, null, null],
                'number' => [null, null, null],
                'word' => [null, 'a', null],
                'other' => [' ', null, ' '],
            ],
            '1a1' => [
                'all' => ['1', 'a', '1'],
                'phone' => [null, null, null],
                'unit' => [null, null, null],
                'number' => ['1', null, '1'],
                'word' => [null, 'a', null],
                'other' => [null, null, null],
            ],
            '1€' => [
                'all' => ['1', '€'],
                'phone' => [null, null],
                'unit' => [null, '€'],
                'number' => ['1', null],
                'word' => [null, null],
                'other' => [null, null],
            ],
            '/a/' => [
                'all' => ['/', 'a', '/'],
                'phone' => [null, null, null],
                'unit' => [null, null, null],
                'number' => [null, null, null],
                'word' => [null, 'a', null],
                'other' => ['/', null, '/'],
            ],
            '1 €' => [
                'all' => ['1', ' €'],
                'phone' => [null, null],
                'unit' => [null, ' €'],
                'number' => ['1', null],
                'word' => [null, null],
                'other' => [null, null],
            ],
            '1k€' => [
                'all' => ['1', 'k€'],
                'phone' => [null, null],
                'unit' => [null, 'k€'],
                'number' => ['1', null],
                'word' => [null, null],
                'other' => [null, null],
            ],
            '1 k€' => [
                'all' => ['1', ' k€'],
                'phone' => [null, null],
                'unit' => [null, ' k€'],
                'number' => ['1', null],
                'word' => [null, null],
                'other' => [null, null],
            ],
            '100 000 000 €' => [
                'all' => ['100 000 000', ' €'],
                'phone' => [null, null],
                'unit' => [null, ' €'],
                'number' => ['100 000 000', null],
                'word' => [null, null],
                'other' => [null, null],
            ],
            '100.000.000 €' => [
                'all' => ['100.000.000', ' €'],
                'phone' => [null, null],
                'unit' => [null, ' €'],
                'number' => ['100.000.000', null],
                'word' => [null, null],
                'other' => [null, null],
            ],
        ];

        foreach ($tests as $string => $expected) {
            $actual = Correcteur::splitElements($string);
            $this->assertSame($expected, $actual);
        }
    }

    public function testCorrectUnit(): void {
        $tests = [
            " €" => " €",
        ];

        foreach ($tests as $string => $expected) {
            $actual = Correcteur::correctUnit($string, false);
            $this->assertSame($expected, $actual, var_export($string, true));
        }
    }

    public function testCleanSpaces(): void
    {
        $tests = [
            "\n" => "\n",
            "\n\n" => "\n\n",
            " \n" => "\n",
            "\n " => "\n",
            " \n " => "\n",
            " \n\n " => "\n\n",
        ];

        foreach ($tests as $string => $expected) {
            $actual = Correcteur::cleanSpaces($string);
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
        ];

        foreach ($tests as $string => $expected) {
            $actual = Correcteur::corriger($string, TRUE);
            $this->assertSame($expected, $actual);
        }
    }
}