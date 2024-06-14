<?php
namespace Zigazou\FrenchTypography\HTML;

class TagType
{
  const INLINETAGS = [
    'b' => 0,
    'big' => 0,
    'i' => 0,
    'small' => 0,
    'tt' => 0,
    'abbr' => 0,
    'acronym' => 0,
    'cite' => 0,
    'code' => 0,
    'dfn' => 0,
    'em' => 0,
    'kbd' => 0,
    'strong' => 0,
    'samp' => 0,
    'var' => 0,
    'a' => 0,
    'bdo' => 0,
    'br' => 0,
    'img' => 0,
    'map' => 0,
    'object' => 0,
    'q' => 0,
    'script' => 0,
    'span' => 0,
    'sub' => 0,
    'sup' => 0,
    'button' => 0,
    'input' => 0,
    'label' => 0,
    'select' => 0,
    'textarea' => 0,
  ];

  public static function isInline(string $tagName)
  {
    return isset(self::INLINETAGS[$tagName]);
  }
}