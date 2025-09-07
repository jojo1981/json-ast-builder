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

namespace Jojo1981\JsonAstBuilder\Exception;

use Jojo1981\JsonAstBuilder\Lexer\Token;
use function count;
use function implode;
use function sprintf;

/**
 * @package Jojo1981\JsonAstBuilder\Exception
 */
final class ParseException extends JsonException
{
    /**
     * @param string $char
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @return ParseException
     */
    public static function unknownCharacterFound(
        string $char,
        int $position,
        int $lineNumber,
        int $linePosition
    ): ParseException {
        return new self(sprintf(
            'Unknown character: `%s` found at position: %d [%d:%d].',
            $char,
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @return ParseException
     */
    public static function unexpectedEndOfFileFound(int $position, int $lineNumber, int $linePosition): ParseException
    {
        return new self(sprintf(
            'Unexpected end of file reached at position: %d [%d:%d]',
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @return ParseException
     */
    public static function invalidInput(): ParseException
    {
        return new self('Empty input given which is invalid json content');
    }

    /**
     * @param string $chars
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @param string $expectedKeyWord
     * @return ParseException
     */
    public static function invalidKeywordFound(
        string $chars,
        int $position,
        int $lineNumber,
        int $linePosition,
        string $expectedKeyWord
    ): ParseException {
        return new self(sprintf(
            'Invalid characters: `%s` found at position: %d [%d:%d]. Did you mean: `%s`?',
            $chars,
            $position,
            $lineNumber,
            $linePosition,
            $expectedKeyWord
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @return ParseException
     */
    public static function unterminatedStringFound(int $position, int $lineNumber, int $linePosition): ParseException
    {
        return new self(sprintf(
            'Unterminated string found at position: %d [%d:%d]',
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @param string $char
     * @return ParseException
     */
    public static function illegalCharacterInStringFound(int $position, int $lineNumber, int $linePosition, string $char): ParseException
    {
        return new self(sprintf(
            'Illegal character in string found: %s at position: %d [%d:%d]',
            ord($char) < 32 ? ord($char) : $char,
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @return ParseException
     */
    public static function illegalEscapeUsed(int $position, int $lineNumber, int $linePosition): ParseException
    {
        return new self(sprintf(
            'Illegal escape in string used at position: %d [%d:%d]',
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @param string $char
     * @return ParseException
     */
    public static function illegalOctalLiteral(int $position, int $lineNumber, int $linePosition, string $char): ParseException
    {
        return new self(sprintf(
            'Illegal octal literal: %s at position: %d [%d:%d]',
            $char,
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @return ParseException
     */
    public static function illegalTrailingDecimal(int $position, int $lineNumber, int $linePosition): ParseException
    {
        return new self(sprintf(
            'Illegal trailing decimal at position: %d [%d:%d], a decimal point must be followed with ath least 1 digit',
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @return ParseException
     */
    public static function illegalNegativeSign(int $position, int $lineNumber, int $linePosition): ParseException
    {
        return new self(sprintf(
            'Illegal negative sign at position: %d [%d:%d], a negative sign may only precede numbers.',
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @return ParseException
     */
    public static function illegalEmptyExponent(int $position, int $lineNumber, int $linePosition): ParseException
    {
        return new self(sprintf(
            'Illegal empty exponent at position: %d [%d:%d], an exponent must be followed with ath least 1 digit.',
            $position,
            $lineNumber,
            $linePosition
        ));
    }

    /**
     * @param Token $currentToken
     * @param string[] $expectedTokens
     * @return ParseException
     */
    public static function unexpectedEndOfFile(Token $currentToken, array $expectedTokens): ParseException
    {
        $plural = count($expectedTokens) > 1;

        return new self(sprintf(
            'Unexpected EOF found. Last seen token: `%s`. Expected token %s: %s',
            $currentToken->getName(),
            $plural ? 'to be one of' : 'to be',
            $plural ? '[' . implode(', ', $expectedTokens) . ']' : $expectedTokens
        ));
    }

    /**
     * @param Token $currentToken
     * @param string[] $expectedTokens
     * @return ParseException
     */
    public static function unexpectedToken(Token $currentToken, array $expectedTokens): ParseException
    {
        $plural = count($expectedTokens) > 1;

        return new self(sprintf(
            'Unexpected token: `%s` found. Expected token %s: [%s]',
            $currentToken->getName(),
            $plural ? 'to be one of' : 'to be',
            $plural ? '[' . implode(', ', $expectedTokens) . ']' : $expectedTokens
        ));
    }

    /**
     * @param Token $commaToken
     * @return ParseException
     */
    public static function illegalTrailingComma(Token $commaToken): ParseException
    {
        return new self(sprintf(
            'Illegal trailing comma found at position: %d [%d:%d]',
            $commaToken->getPosition(),
            $commaToken->getLineNumber(),
            $commaToken->getLinePosition()
        ));
    }
}
