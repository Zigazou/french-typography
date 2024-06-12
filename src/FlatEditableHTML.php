<?php
namespace Zigazou\FrenchTypography;

use Zigazou\FrenchTypography\HTML\HTMLElement;
use Zigazou\FrenchTypography\HTML\TagType;

class FlatEditableHTML implements \Stringable
{
    const BLOCKTAGCODE = "\x1D";
    const INLINETAGCODE = "\x1E";

    public string $codes = '';
    protected array $tags = [];

    public function push(HTMLElement $element): void
    {
        if ($element->isTag()) {
            if (TagType::isInline($element->tagName)) {
                $this->codes .= self::INLINETAGCODE;
            } else {
                $this->codes .= self::BLOCKTAGCODE;
            }

            $this->tags[] = $element->string;
        } else {
            $this->codes .= $element->string;
        }
    }

    public static function fromMixed(HTMLElement|FlatEditableHTML|string $element): FlatEditableHTML
    {
        if ($element instanceof FlatEditableHTML) {
            $feh = new FlatEditableHTML();
            $feh->codes = $element->codes;
            $feh->tags = $element->tags;
        } else if ($element instanceof HTMLElement) {
            $feh = new FlatEditableHTML();
            $feh->push($element);
        } else {
            $feh = FlatEditableHTML::fromString($element);
        }

        return $feh;
    }

    public function countTagsInRange(int $start, int $length): int
    {
        if ($length < 0 || $start < 0 || $start >= mb_strlen($this->codes)) {
            return 0;
        }

        // Count tags in the range.
        $btc = self::BLOCKTAGCODE;
        $itc = self::INLINETAGCODE;

        $tagCount = strlen(
            preg_replace(
                "/[^$btc$itc]/su",
                '',
                mb_substr($this->codes, $start, $length)
            )
        );

        return $tagCount;
    }

    public function delete(int $start, int $length): void
    {
        // Ensure position is valid.
        if ($start < 0 || $start >= mb_strlen($this->codes)) {
            return;
        }

        $tagsBefore = $this->countTagsInRange(0, $start);
        $tagsIn = $this->countTagsInRange($start, $length);

        // Insert the element.
        $this->codes = mb_substr($this->codes, 0, $start)
            . mb_substr($this->codes, $start + $length);

        // Insert the tags.
        $this->tags = array_merge(
            array_slice($this->tags, 0, $tagsBefore),
            array_slice($this->tags, $tagsBefore + $tagsIn)
        );
    }

    public function substr(int $start, int $length): FlatEditableHTML {
        $feh = FlatEditableHTML::fromMixed($this);
        $feh->delete($start + $length, mb_strlen($this->codes));
        $feh->delete(0, $start);

        return $feh;
    }

    public function insert(HTMLElement|FlatEditableHTML|string $element, int $position): void
    {
        // Ensure position is valid.
        if ($position < 0 || $position > mb_strlen($this->codes)) {
            return;
        }

        $feh = FlatEditableHTML::fromMixed($element);
        $tagCount = $this->countTagsInRange(0, $position);

        // Insert the element.
        $this->codes = mb_substr($this->codes, 0, $position)
            . $feh->codes
            . mb_substr($this->codes, $position);

        // Insert the tags.
        $this->tags = array_merge(
            array_slice($this->tags, 0, $tagCount),
            $feh->tags,
            array_slice($this->tags, $tagCount)
        );
    }

    public static function fromString(string $string): FlatEditableHTML
    {
        $codes = preg_split(
            '/(<[^>]+>)/su',
            $string,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        $feh = new self();
        foreach ($codes as $code) {
            $feh->push(new HTMLElement($code));
        }

        return $feh;
    }

    public function __toString(): string
    {
        $output = '';
        $btc = self::BLOCKTAGCODE;
        $itc = self::INLINETAGCODE;

        $codes = preg_split(
            "/($btc|$itc)/su",
            $this->codes,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        $tags = array_reverse($this->tags);
        foreach ($codes as $code) {
            if ($code === self::INLINETAGCODE || $code === self::BLOCKTAGCODE) {
                $output .= array_pop($tags);
            } else {
                $output .= $code;
            }
        }

        return $output;
    }
}