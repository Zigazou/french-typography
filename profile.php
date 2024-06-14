<?php

require("vendor/autoload.php");

use Zigazou\FrenchTypography\Correcteur;

$text = file_get_contents("croc-blanc.html");

$text = Correcteur::corriger($text, TRUE);

file_put_contents("croc-blanc.corrected.html", $text);

