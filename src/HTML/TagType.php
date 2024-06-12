<?php
namespace Zigazou\FrenchTypography\HTML;

class TagType
{
    const INLINETAGS = [
        'b',
        'big',
        'i',
        'small',
        'tt',
        'abbr',
        'acronym',
        'cite',
        'code',
        'dfn',
        'em',
        'kbd',
        'strong',
        'samp',
        'var',
        'a',
        'bdo',
        'br',
        'img',
        'map',
        'object',
        'q',
        'script',
        'span',
        'sub',
        'sup',
        'button',
        'input',
        'label',
        'select',
        'textarea',
    ];

    public static function isInline(string $tagName)
    {
        return in_array($tagName, self::INLINETAGS);
    }

}