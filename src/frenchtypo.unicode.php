<?php

/**
 * @file
 * Provides Unicode equivalents for certain character sequences.
 */

$unicodeEquivalents = [
  '...' => '…',
  "'" => "’",
  '!!!' => '!',
  '!!' => '!',
  '???' => '?',
  '??' => '?',
  '!?' => '⁉',
  '?!' => '⁈',

  '<->' => '↔',
  '->' => '→',
  '<-' => '←',

  '<=>' => '⇔',
  '=>' => '⇒',
  '<=' => '⇐',

  '(c)' => '©',
  '(C)' => '©',
  '(r)' => '®',
  '(R)' => '®',
];

$unicodeFrom = array_keys($unicodeEquivalents);
$unicodeTo = array_values($unicodeEquivalents);
