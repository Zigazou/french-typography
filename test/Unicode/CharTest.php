<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Zigazou\FrenchTypography\Unicode\Char;

final class CharTest extends TestCase
{
    public function testIsWordCharacter(): void
    {
        $tests = [
            'a',
            'é',
            'X',
            'É',
        ];

        foreach ($tests as $string) {
            $this->assertSame(true, Char::isWordCharacter($string), $string);
        }
    }

    public function testIsNotWordCharacter(): void
    {
        $tests = [
            'ab',
            '',
            ' ',
            "\n",
            'Éa',
            '+',
            '×',
            '÷',
        ];

        foreach ($tests as $string) {
            $this->assertSame(false, Char::isWordCharacter($string), $string);
        }
    }
}