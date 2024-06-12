<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Zigazou\FrenchTypography\HTML\HTMLElement;

final class HTMLElementTest extends TestCase
{
    public function testCanCreateTag(): void
    {
        $tests = [
            '<span class="abc">' => 'span',
            '<SPAN class="abc">' => 'span',
            '<span>' => 'span',
            '<SPAN>' => 'span',
            '</span class="abc">' => 'span',
            '</SPAN class="abc">' => 'span',
            '</span>' => 'span',
            '</SPAN>' => 'span',
        ];

        foreach ($tests as $string => $tagName) {
            $element = new HTMLElement($string);

            $this->assertSame(TRUE, $element->isTag());
            $this->assertSame($string, $element->string);
            $this->assertSame($tagName, $element->tagName);
        }
    }

    public function testCanCreateText(): void
    {
        $tests = [
            'hello world' => '',
            ' ' => '',
        ];

        foreach ($tests as $string => $tagName) {
            $element = new HTMLElement($string);

            $this->assertSame(FALSE, $element->isTag());
            $this->assertSame($string, $element->string);
            $this->assertSame($tagName, $element->tagName);
        }
    }
}