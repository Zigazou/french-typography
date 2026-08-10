# FrenchTypography

**FrenchTypography** is a PHP module that automatically applies French
typography rules to HTML code in UTF-8. This module improves the presentation
and readability of texts by respecting the typographic standards in force in
France.

## Usage
```php
<?php
require('vendor/autoload.php');

use Zigazou\FrenchTypography\Correcteur;

// This will return: Bonjour le monde « monde » !
$a = Correcteur::corriger('Bonjour le "monde"!');

// This will return: <div class="d">Bonjour le monde « monde » !</div>
$b = Correcteur::corriger('<div class="d">Bonjour le "monde"!</div>', TRUE);
```

## Features

- Adds a narrow no-break space before `:`, `;`, `!`, and `?`, and normalizes
  the following space.
- Normalizes spacing around periods and commas, including at HTML block
  boundaries.
- Converts English double quotes to French guillemets with narrow no-break
  spaces (`"Hello"` → `« Hello »`) and straight apostrophes to typographic
  apostrophes.
- Converts three dots to an ellipsis and normalizes repeated or mixed `!` and
  `?` punctuation.
- Replaces common ASCII sequences with Unicode equivalents: arrows, copyright,
  registered, trademark, sound-recording copyright, warning, and emoticons.
- Restores selected accents on initial capital letters and missing `œ`/`Œ`
  ligatures (for example, `Economie` → `Économie` and `boeuf` → `bœuf`).
- Corrects the preposition `A` to `À` where appropriate.
- Formats French phone numbers (`0999999999` → `09 99 99 99 99`).
- Uses a no-break space between numbers and supported units or currencies, and
  narrow no-break spaces as thousands separators.
- Converts ordinal suffixes to superscript HTML (`1er` → `1<sup>er</sup>`).
- Collapses consecutive spaces, preserves line breaks, and trims leading and
  trailing whitespace.
- Supports HTML input without altering tags or URLs.

## Installation

To install **FrenchTypography**, you can use Composer:

```bash
composer require zigazou/french-typography
```

## Running tests

To run all the tests: `vendor/bin/phpunit test`
