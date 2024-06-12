<?php
require('../vendor/autoload.php');

use Zigazou\FrenchTypography\Operation;

function loadList(string $filePath): array
{
    // Split the file content into words.
    return preg_split("/[^\\p{L}]+/u", file_get_contents($filePath));
}

class CorrectionGenerator
{
    public array $corrections = [];

    public static array $frenchWords = [];
    public static array $frenchCities = [];
    public static array $frenchSurnames = [];

    public static array $frenchItems = [];

    function setCorrection(string $key, int $value): void
    {
        if (!isset($this->corrections[$key])) {
            $this->corrections[$key] = 0;
        }

        $this->corrections[$key] |= $value;
    }

    public static function initLists(): void
    {
        // Load all french words.
        self::$frenchWords = loadList(__DIR__ . "/corpus/french-words.txt");
        self::$frenchCities = loadList(__DIR__ . "/corpus/french-cities.txt");
        self::$frenchSurnames = loadList(__DIR__ . "/corpus/french-surnames.txt");

        self::$frenchItems = [
            self::$frenchWords,
            self::$frenchCities,
            self::$frenchSurnames,
        ];
    }

    public function exportPHP(string $variableName, bool $trim = true): string
    {
        $export = '$' . $variableName . '=';
        if ($trim) {
            $export .= str_replace(
                [' ', "\n"],
                '',
                var_export($this->corrections, true)
            );
        } else {
            $export .= var_export($this->corrections, true);
        }

        $export .= ";\n";

        return $export;
    }

    public function createCorrections(): void
    {
        foreach (self::$frenchItems as $items) {
            foreach ($items as $item) {
                $this->createWordCorrections($item);
            }
        }
    }

    public function createWordCorrections(string $word): void
    {
        $wordCorrections = [];
        $wordCorrections[$word] = Operation::NOP;
        $wordCorrections[mb_convert_case($word, MB_CASE_TITLE)] = Operation::NOP;

        $head = mb_strtolower(mb_substr($word, 0, 1));
        $tail = mb_substr($word, 1);

        switch ($head) {
            case 'é':
                $wordCorrections['E' . $tail] = Operation::FIRST_CAPITAL_EACUTE;
                $wordCorrections['É' . $tail] = Operation::NOP;
                break;

            case 'è':
                $wordCorrections['E' . $tail] = Operation::FIRST_CAPITAL_EGRAVE;
                $wordCorrections['È' . $tail] = Operation::NOP;
                break;

            case 'ê':
                $wordCorrections['E' . $tail] = Operation::FIRST_CAPITAL_ECIRC;
                $wordCorrections['Ê' . $tail] = Operation::NOP;
                break;

            case 'â':
                $wordCorrections['A' . $tail] = Operation::FIRST_CAPITAL_ACIRC;
                $wordCorrections['Â' . $tail] = Operation::NOP;
                break;

            case 'ç':
                $wordCorrections['C' . $tail] = Operation::FIRST_CAPITAL_CCEDIL;
                $wordCorrections['Ç' . $tail] = Operation::NOP;
                break;

            case 'œ':
                $wordCorrections['OE' . $tail] = Operation::FIRST_CAPITAL_OELIG;
                $wordCorrections['Oe' . $tail] = Operation::FIRST_CAPITAL_OELIG;
                $wordCorrections['Œ' . $tail] = Operation::NOP;
                break;

            default:
        }

        // oe ligature.
        foreach ($wordCorrections as $word => $operation) {
            if (mb_strpos($word, 'œ') !== false) {
                $key = str_replace('œ', 'oe', $word);
                if (!isset($wordCorrections[$key])) {
                    $wordCorrections[$key] = $operation;
                }
                $wordCorrections[$key] |= Operation::OELIG;
            }
        }

        foreach ($wordCorrections as $key => $operation) {
            if ($operation === Operation::NOP)
                continue;
            $this->setCorrection($key, $operation);
        }
    }
}

CorrectionGenerator::initLists();

$generator = new CorrectionGenerator();
$generator->createCorrections();

print("<?php\n");
print($generator->exportPHP('corrections', true));