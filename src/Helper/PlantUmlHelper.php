<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2025 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
declare(strict_types=1);

namespace Jojo1981\JsonAstBuilder\Helper;

use function implode;

/**
 * @package Jojo1981\JsonAstBuilder\Helper
 */
final class PlantUmlHelper
{
    /**
     * @param string[] $objects
     * @param string[] $links
     * @return string
     */
    public static function generateDocument(array $objects, array $links): string
    {
        $lines = [];
        $lines[] = '@startuml';
        $lines[] = '';
        $lines[] = 'hide empty members';
        $lines[] = '';
        $lines[] = 'title';
        $lines[] = 'Data objects diagram';
        $lines[] = '';
        $lines[] = 'end title';
        $lines[] = '';
        $lines[] = implode(PHP_EOL, $objects);
        $lines[] = '';
        $lines[] = implode(PHP_EOL, $links);
        $lines[] = '@enduml';

        return implode(PHP_EOL, $lines);
    }
}
