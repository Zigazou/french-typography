<?php
namespace Zigazou\FrenchTypography\HTML;

class HTMLElement
{
    const REGEXTAG = '|^</?([a-zA-Z][a-zA-Z0-9-]*[a-zA-Z0-9]*)( .*)?>$|';
    const TAGNAMEINDEX = 1;
    const FOUND = 1;
    const NOTATAG = '';

    public readonly string $string;
    public readonly string $tagName;

    function __construct(string $string)
    {
        $this->string = $string;

        // Is this a tag?
        if (preg_match(self::REGEXTAG, $string, $matches) === self::FOUND) {
            $this->tagName = strtolower($matches[self::TAGNAMEINDEX]);
        } else {
            $this->tagName = self::NOTATAG;
        }
    }

    public function isTag(): bool
    {
        return ($this->tagName !== self::NOTATAG);
    }

    public function __toString(): string
    {
        return $this->string;
    }
}