<?php
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