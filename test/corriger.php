<?php
require(__DIR__ . '/../vendor/autoload.php');
use Zigazou\FrenchTypography\Correcteur;

$stdin = fopen("php://stdin","r");
$text = stream_get_contents($stdin);
fclose($stdin);

$dummy = Correcteur::corriger('aa');

$startTime = microtime(true);
$corrected = Correcteur::corriger($text);
$endTime = microtime(true);

print($corrected);
fwrite(STDERR, ($endTime - $startTime) . "\n");
