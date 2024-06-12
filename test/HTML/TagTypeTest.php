<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Zigazou\FrenchTypography\HTML\TagType;

final class TagTypeTest extends TestCase
{
    public function testBlockTags(): void
    {
        $tags = [
            'div',
            'p',
        ];

        foreach ($tags as $tag) {
            $this->assertSame(FALSE, TagType::isInline($tag));
        }
    }

    public function testInlineTags(): void
    {
        $tags = [
            'strong',
            'i',
        ];

        foreach ($tags as $tag) {
            $this->assertSame(TRUE, TagType::isInline($tag));
        }
    }
}