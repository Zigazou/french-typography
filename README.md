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

- Addition of non-breaking thin spaces before double punctuation marks (: ; ? !)
- Convert English double quotes (" ") into French guillemets (« »)
- Adjustment of spaces in units of measurement
- Restore accents on uppercased first letters (Ecole → École)
- Corrects multiple consecutive exclamation/question mark
- Simple quotes are replaced with typographic quote
- <->, -> and <- are replaced with ↔, → and ←
- <=>, => and <= are replaced with ⇔, ⇒ and ⇐
- (c) and (C) are replaced with ©
- (r) and (R) are replaced with ®
- french phone numbers are formatted (0999999999 → 09 99 99 99 99)
- number followed by units will be separated by a no-break space
- Corrects missing œ ligatures (oeil → œil)
- Consecutive spaces are reduced to one space
- Leading and trailing spaces are trimmed

## Installation

To install **FrenchTypography**, you can use Composer:

```bash
composer require zigazou/french-typography
```

