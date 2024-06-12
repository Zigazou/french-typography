<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Zigazou\FrenchTypography\FlatEditableHTML;
use Zigazou\FrenchTypography\HTML\HTMLElement;

final class FlatEditableHTMLTest extends TestCase
{
    public function testIsReversible(): void
    {
        $tests = [
            'hello',
            'hello <span><span>World</span></span>',
            'hello <span>World</span>',
            '<p>hello</p>',
            '<span class="abc">',
            '<SPAN   class="abc">',
            '<span>',
            '<SPAN>',
            '</span class="abc">',
            '</SPAN class="abc">',
            '</span>',
            '</SPAN>',
            "hello <span>\nWorld\r</span>",
        ];

        foreach ($tests as $string) {
            $feh = FlatEditableHTML::fromString($string);
            $this->assertSame($string, "$feh");
        }
    }

    public function testInsertion(): void
    {
        $tests = [
            [
                'source' => 'hello',
                'needle' => 'ab',
                'position' => 0,
                'expected' => 'abhello'
            ],
            [
                'source' => 'hello',
                'needle' => 'a<x>b',
                'position' => 0,
                'expected' => 'a<x>bhello'
            ],
            [
                'source' => 'hello',
                'needle' => 'ab',
                'position' => 5,
                'expected' => 'helloab'
            ],
            [
                'source' => 'hello',
                'needle' => 'a<x>b',
                'position' => 5,
                'expected' => 'helloa<x>b'
            ],
            [
                'source' => 'hello',
                'needle' => 'a<x>b',
                'position' => 2,
                'expected' => 'hea<x>bllo'
            ],
            [
                'source' => 'he<y></y>llo',
                'needle' => 'a<x>b',
                'position' => 3,
                'expected' => 'he<y>a<x>b</y>llo'
            ],
            [
                'source' => 'he<y></y>llo',
                'needle' => 'ab',
                'position' => 3,
                'expected' => 'he<y>ab</y>llo'
            ],
            [
                'source' => 'he<y>llo</y>',
                'needle' => 'ab<x>',
                'position' => 2,
                'expected' => 'heab<x><y>llo</y>'
            ],
        ];

        foreach ($tests as $test) {
            $feh = FlatEditableHTML::fromString($test['source']);
            $feh->insert($test['needle'], $test['position']);
            $this->assertSame($test['expected'], "$feh");
        }
    }

    public function testDeletion(): void
    {
        $tests = [
            [
                'source' => 'hello',
                'start' => 2,
                'length' => 2,
                'expected' => 'heo'
            ],
            [
                'source' => 'hello',
                'start' => 0,
                'length' => 2,
                'expected' => 'llo'
            ],
            [
                'source' => 'hello',
                'start' => 3,
                'length' => 2,
                'expected' => 'hel'
            ],
            [
                'source' => 'he<x>llo</x>',
                'start' => 3,
                'length' => 2,
                'expected' => 'he<x>o</x>'
            ],
            [
                'source' => 'h<x>ello</x>',
                'start' => 0,
                'length' => 2,
                'expected' => 'ello</x>'
            ],
            [
                'source' => 'he<x>llo</x>',
                'start' => 4,
                'length' => 3,
                'expected' => 'he<x>l'
            ],
        ];

        foreach ($tests as $test) {
            $feh = FlatEditableHTML::fromString($test['source']);
            $feh->delete($test['start'], $test['length']);
            $this->assertSame($test['expected'], "$feh");
        }
    }

    public function testSubstr(): void
    {
        $tests = [
            [
                'source' => 'hello',
                'start' => 2,
                'length' => 2,
                'expected' => 'll'
            ],
            [
                'source' => 'hello',
                'start' => 0,
                'length' => 2,
                'expected' => 'he'
            ],
            [
                'source' => 'hello',
                'start' => 3,
                'length' => 2,
                'expected' => 'lo'
            ],
            [
                'source' => 'he<x>llo</x>',
                'start' => 3,
                'length' => 2,
                'expected' => 'll'
            ],
            [
                'source' => 'h<x>ello</x>',
                'start' => 0,
                'length' => 2,
                'expected' => 'h<x>'
            ],
            [
                'source' => 'he<x>llo</x>',
                'start' => 4,
                'length' => 3,
                'expected' => 'lo</x>'
            ],
        ];

        foreach ($tests as $test) {
            $feh = FlatEditableHTML::fromString($test['source']);
            $sub = $feh->substr($test['start'], $test['length']);
            $this->assertSame($test['expected'], "$sub");
        }
    }

    public static function generateRandomString(string $allowedChars, int $length)
    {
        $charsLength = mb_strlen($allowedChars);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= mb_substr(
                $allowedChars,
                rand(0, $charsLength - 1),
                1
            );
        }

        return $randomString;
    }

    public static function generateHTML(): array {
        $elementCount = rand(1, 20);
        $start = rand(0, $elementCount);
        $length = rand(0, $elementCount - $start);

        $feh = new FlatEditableHTML();
        for($i = 0; $i < $elementCount; $i++) {
            $elementType = rand(0, 2);
            if ($elementType === 0) {
                $elementString = self::generateRandomString(
                    " 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZéàèçÉÀ…’'",
                    rand(1, 10)
                );
            } else if ($elementType === 1) {
                $elementString = "<" . self::generateRandomString(
                    "abcdefghijklmnopqrstuvwxyz",
                    rand(1, 5)
                ) . ">";
            } else {
                $elementString = "</" . self::generateRandomString(
                    "abcdefghijklmnopqrstuvwxyz",
                    rand(1, 5)
                ) . ">";
            }
            
            $feh->push(new HTMLElement($elementString));
        }

        return [
            'source' => $feh,
            'start' => $start,
            'length' => $length
        ];
    }
    public function testRandom(): void
    {
        srand(42);

        for ($i = 0; $i < 500; $i++) {
            $test = self::generateHTML();
            $feh = $test['source'];
            $expected = "$feh";
            $sub = $feh->substr($test['start'], $test['length']);
            $feh->delete($test['start'], $test['length']);
            $feh->insert($sub, $test['start']);
            $this->assertSame($expected, "$feh");
        }
    }
}