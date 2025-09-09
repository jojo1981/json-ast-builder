<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
declare(strict_types=1);

namespace Jojo1981\JsonAstBuilder\Lexer;

use UnexpectedValueException;
use function array_key_exists;
use function implode;
use function sprintf;

/**
 * @package Jojo1981\JsonAstBuilder\Lexer
 */
final class TokenType
{
    /** @var int */
    public const TOKEN_EOF = -1;

    /** @var int */
    public const TOKEN_WHITE_SPACE = 1;

    /** @var int */
    public const TOKEN_NEWLINE = 2;

    /** @var int */
    public const TOKEN_INT = 3;

    /** @var int */
    public const TOKEN_NUMBER = 4;

    /** @var int */
    public const TOKEN_STRING = 5;

    /** @var int */
    public const TOKEN_COMMA = 6;

    /** @var int */
    public const TOKEN_COLON = 7;

    /** @var int */
    public const TOKEN_LEFT_SQUARE_BRACKET = 8;

    /** @var int */
    public const TOKEN_RIGHT_SQUARE_BRACKET = 9;

    /** @var int */
    public const TOKEN_LEFT_CURLY_BRACKET = 10;

    /** @var int */
    public const TOKEN_RIGHT_CURLY_BRACKET = 11;

    /** @var int */
    public const TOKEN_KEYWORD = 12;

    /** @var string[] */
    private const TOKEN_NAMES = [
        self::TOKEN_EOF => 'EOF',
        self::TOKEN_WHITE_SPACE => 'WHITE_SPACE',
        self::TOKEN_NEWLINE => 'NEWLINE',
        self::TOKEN_INT => 'INT',
        self::TOKEN_NUMBER => 'NUMBER',
        self::TOKEN_STRING => 'STRING',
        self::TOKEN_COMMA => 'COMMA',
        self::TOKEN_COLON => 'COLON',
        self::TOKEN_LEFT_SQUARE_BRACKET => 'LEFT_SQUARE_BRACKET',
        self::TOKEN_RIGHT_SQUARE_BRACKET => 'RIGHT_SQUARE_BRACKET',
        self::TOKEN_LEFT_CURLY_BRACKET => 'LEFT_CURLY_BRACKET',
        self::TOKEN_RIGHT_CURLY_BRACKET => 'RIGHT_CURLY_BRACKET',
        self::TOKEN_KEYWORD => 'KEYWORD'
    ];

    /**
     * @param int $tokenType
     * @return string
     * @throws UnexpectedValueException
     */
    public static function getLiteral(int $tokenType): string
    {
        return '<' . self::getNameForTokenType($tokenType) . '(' . $tokenType . ')>';
    }

    /**
     * @param int $tokenType
     * @return string
     * @throws UnexpectedValueException
     */
    public static function getNameForTokenType(int $tokenType): string
    {
        if (!array_key_exists($tokenType, self::TOKEN_NAMES)) {
            throw new UnexpectedValueException(sprintf(
                'Can not get token name for type: %d. The following token types are valid: [%s]',
                $tokenType,
                implode(', ', self::TOKEN_NAMES)
            ));
        }

        return self::TOKEN_NAMES[$tokenType];
    }
}
