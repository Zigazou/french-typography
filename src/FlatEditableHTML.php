<?php
namespace Zigazou\FrenchTypography;

use Zigazou\FrenchTypography\HTML\HTMLElement;
use Zigazou\FrenchTypography\HTML\TagType;

class FlatEditableHTML implements \Stringable
{
    /**
     * Single byte used to identify a block tag.
     * 
     * @var string
     */
    const BLOCKTAGCODE = "\x1D";

    /**
     * Single byte used to identify an inline tag.
     * 
     * @var string
     */
    const INLINETAGCODE = "\x1E";

    /**
     * String where the opening and closing tags are replaced by a single byte.
     * 
     * It is synchronised with the tags array.
     * 
     * @var string
     */
    public string $codes = '';

    /**
     * List of tags in the order they appear in the text.
     * 
     * @var array
     */
    protected array $tags = [];

    /**
     * Push an element to the flat editable HTML.
     * 
     * @param HTMLElement $element The element to push.
     */
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

    /**
     * Create a flat editable HTML from a string, another flat editable HTML or
     * an HTML element.
     *
     * @param HTMLElement|FlatEditableHTML|string $element The element to
     *  convert.
     * @return FlatEditableHTML The flat editable HTML.
     */
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

    /**
     * Count the number of tags in a range.
     * 
     * @param int $start The start position.
     * @param int $length The length of the range.
     * @return int The number of tags in the range.
     */
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

    /**
     * Delete a range of characters.
     * 
     * @param int $start The start position.
     * @param int $length The length of the range.
     */
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

    /**
     * Extract a substring.
     * 
     * @param int $start The start position.
     * @param int $length The length of the substring.
     * @return FlatEditableHTML The extracted substring.
     */
    public function substr(int $start, int $length): FlatEditableHTML
    {
        $feh = FlatEditableHTML::fromMixed($this);
        $feh->delete($start + $length, mb_strlen($this->codes));
        $feh->delete(0, $start);

        return $feh;
    }

    /**
     * Insert an element at a given position.
     * 
     * @param HTMLElement|FlatEditableHTML|string $element Element to insert.
     * @param int $position The position where to insert the element.
     */
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

    /**
     * Convert a string to a FlatEditableHTML object.
     * 
     * @param string $string The string to convert.
     * @return FlatEditableHTML The FlatEditableHTML object.
     */
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

    /**
     * Convert the flat editable HTML to a string.
     * 
     * @return string The string representation of the flat editable HTML.
     */
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