JSON AST Builder for PHP 
=====================

[![Latest Stable Version](https://poser.pugx.org/jojo1981/json-ast-builder/v/stable)](https://packagist.org/packages/jojo1981/json-ast-builder)
[![Total Downloads](https://poser.pugx.org/jojo1981/json-ast-builder/downloads)](https://packagist.org/packages/jojo1981/json-ast-builder)
[![License](https://poser.pugx.org/jojo1981/json-ast-builder/license)](https://packagist.org/packages/jojo1981/json-ast-builder)

Author: Joost Nijhuis <[jnijhuis81@gmail.com](mailto:jnijhuis81@gmail.com)>

A PHP Implementation for building an AST (Abstract Syntax Tree) from a `JSON` string.  
The AST is visitable and can be visited by implementing a visitor (Behavioral Design Pattern Visitor).
 
Purposes of this library are:

- Validate JSON syntax and give the end user a precise error message about syntax errors
- Transform an AST to a JSON string and control the format
- Travers AST by implementing your own visitor


More information about the lightweight data-interchange format JSON (JavaScript Object Notation) can be found [here](https://www.json.org/).  

## Installation

### Library

```bash
git clone https://github.com/jojo1981/json-ast-builder.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require jojo1981/json-ast-builder
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use Jojo1981\JsonAstBuilder\Generator;
use Jojo1981\JsonAstBuilder\Lexer\Lexer;
use Jojo1981\JsonAstBuilder\Parser;

// setup lexer and parser
$parser = new Parser(new Lexer());
$parser->setInput(\file_get_contents('data.json'));

// build AST
$ast = $parser->parse();

// You can use the generator to generate multiple things
$generator = new Generator();

// default generate json string options
$generateJsonStringOptions = [
    'useTabs' => false,
    'pretty' => true,
    'indentSize' => 2,
    'spacesBeforeColon' => 0,
    'spacesAfterColon' => 1,
    'lineSeparator' => PHP_EOL
];

// options can be omitted
$jsonString = $generator->generateJsonString($ast, $generateJsonStringOptions);

// default generate json string options
$generateDataOptions = [
    'assoc' => false
];

// options can be omitted
$data = $generator->generateData($ast, $generateDataOptions);

$plantUmlString = $generator->generatePlantUmlData($ast);
\file_put_contents('test-output.puml', $plantUmlString);

$statistics = $generator->getStatistics($ast);
```
