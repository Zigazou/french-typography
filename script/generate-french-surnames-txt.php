<?php
/*
Extract all French surnames from a Wikipedia page and output them sorted, one
surname per line (UTF-8 encoded).

The path to the HTML file is given as first argument of this script and is a
copy of the page found at following URL:
"https://fr.wikipedia.org/wiki/Liste_de_pr%C3%A9noms_en_fran%C3%A7ais"

The parsing is very crude!
*/
$source = file_get_contents($argv[1]);

preg_match_all("|\([fm]\)(.*)</li>|u", $source, $surnameLines);

$surnames = [];
foreach ($surnameLines[1] as $surnameLine) {
    // Remove all HTML tags.
    $surnameLine = preg_replace("/<[^>]*>/u", "", $surnameLine);

    // Remove all text between parentheses.
    $surnameLine = preg_replace("/\([^)]*\)/u", "", $surnameLine);

    // Remove all text between brackets.
    $surnameLine = preg_replace("/\[[^\]]*\]/u", "", $surnameLine);

    // Remove all text after the first colon.
    $surnameLine = preg_replace("/(&#160;|:).*$/u", "", $surnameLine);

    // Remove all text starting with "et ses" or "et tous ses".
    $surnameLine = preg_replace("/ et (tous )?ses .*$/u", "", $surnameLine);

    // Remove all text starting with particularities.
    $surnameLine = preg_replace("/ (d'Arc|d[eu] |en|référence|dérivé|ancienne forme|composé|d'origine|féminin).*$/u", "", $surnameLine);

    // Remove all text after comma, semi-colon, dot or "ou".
    $surnameLine = preg_replace("/ *([,;.] *|ou +)/u", " ", $surnameLine);

    // Remove all spaces at the end of each line.
    $surnameLine = preg_replace("/ +$/u", "", $surnameLine);

    // Remove all spaces at the beginning of each line.
    $surnameLine = preg_replace("/^ +/u", "", $surnameLine);

    // Replace all spaces by new lines.
    $surnameLine = preg_replace("/ +/u", "\n", $surnameLine);

    $surnames[] = $surnameLine;
}

sort($surnames);
print(implode("\n", $surnames));