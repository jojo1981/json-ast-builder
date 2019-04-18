<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JsonAstBuilder\Lexer;

use Jojo1981\JsonAstBuilder\Exception\LogicalException;
use Jojo1981\JsonAstBuilder\Exception\ParseException;

/**
 * The lexer is responsible for generating a token stream and perform the lexical analysis and check the syntax.
 *
 * @package Jojo1981\JsonAstBuilder\Lexer
 */
class Lexer implements LexerInterface
{
    /** @var string */
    private const KEYWORD_TRUE = 'true';

    /** @var string */
    private const KEYWORD_FALSE = 'false';

    /** @var string */
    private const KEYWORD_NULL = 'null';

    /** @var string[] */
    private const KEYWORDS = [
        self::KEYWORD_TRUE,
        self::KEYWORD_FALSE,
        self::KEYWORD_NULL
    ];

    /** @var int[] */
    private const CHAR_TOKEN_MAP = [
        PHP_EOL => TokenType::TOKEN_NEWLINE,
        "\t" => TokenType::TOKEN_WHITE_SPACE,
        ' ' => TokenType::TOKEN_WHITE_SPACE,
        '[' => TokenType::TOKEN_LEFT_SQUARE_BRACKET,
        ']' => TokenType::TOKEN_RIGHT_SQUARE_BRACKET,
        '{' => TokenType::TOKEN_LEFT_CURLY_BRACKET,
        '}' => TokenType::TOKEN_RIGHT_CURLY_BRACKET,
        ':' => TokenType::TOKEN_COLON,
        ',' => TokenType::TOKEN_COMMA,
        self::KEYWORD_TRUE => TokenType::TOKEN_KEYWORD,
        self::KEYWORD_FALSE => TokenType::TOKEN_KEYWORD,
        self::KEYWORD_NULL => TokenType::TOKEN_KEYWORD
    ];

    /** @var string[] */
    private const VALID_ESCAPE_CHARS = ['"', '\\', '/', 'b', 'n', 'r', 't', 'u'];

    /** @var Scanner */
    private $scanner;

    /** @var Token */
    private $currentToken;

    /** @var int */
    private $position;

    /** @var int */
    private $lineNumber;

    /** @var int */
    private $linePosition;

    /**
     * @param string $input
     * @throws ParseException
     * @throws LogicalException
     * @return void
     */
    public function setInput(string $input): void
    {
        $this->scanner = new Scanner($input);
        if ($this->scanner->isEmpty()) {
            throw ParseException::invalidInput();
        }
        $this->reset();
    }

    /**
     * @throws LogicalException
     * @return void
     */
    public function reset(): void
    {
        $this->assertScanner();
        $this->scanner->rewind();
        $this->currentToken = null;
    }

    /**
     * @throws LogicalException
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return Token
     */
    public function getCurrent(): Token
    {
        if (null === $this->currentToken) {
            return $this->getNext();
        }

        return $this->currentToken;
    }

    /**
     * @throws LogicalException
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return Token
     */
    public function getNext(): Token
    {
        $this->assertScanner();
        $this->assertEndOfFile();

        return $this->currentToken = $this->scan();
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return Token
     */
    private function scan(): Token
    {
        $this->lockPosition();

        if ($this->scanner->hasEndReached()) {
            return $this->createTokenType(TokenType::TOKEN_EOF);
        }

        $current = $this->scanner->look();
        switch (true) {
            case '"' === $current: {
                return $this->scanString();
            }
            case \array_key_exists($current, self::CHAR_TOKEN_MAP): {
                return $this->createTokenForLexeme($this->scanner->read());
            }
            case '-' === $current || \is_numeric($current): {
                return $this->readNumber();
            }
        }

        if (null !== $token = $this->scanKeyword()) {
            return $token;
        }

        throw ParseException::unknownCharacterFound(
            $this->scanner->look(),
            $this->scanner->getPosition(),
            $this->scanner->getLineNumber() + 1,
            $this->scanner->getLinePosition() + 1
        );
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return Token|null
     */
    private function scanKeyword(): ?Token
    {
        foreach (self::KEYWORDS as $keyword) {
            $current = $this->scanner->look(\strlen($keyword));
            if (\array_key_exists($current, self::CHAR_TOKEN_MAP)) {
                return $this->createTokenForLexeme($this->scanner->read(\strlen($keyword)));
            }
            if (\strtolower($current) === $keyword) {
                throw ParseException::invalidKeywordFound(
                    $current,
                    $this->scanner->getPosition(),
                    $this->scanner->getLineNumber() + 1,
                    $this->scanner->getLinePosition() + 1,
                    $keyword
                );
            }
        }

        return null;
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return Token
     */
    private function scanString(): Token
    {
        $this->lockPosition();

        $buffer = $this->scanner->read();
        while (!$this->scanner->hasEndReached()) {
            $current = $this->scanner->look();
            if (\ord($current) < 32) {
                if (PHP_EOL === $current) {
                    throw ParseException::unterminatedStringFound(
                        $this->scanner->getPosition(),
                        $this->scanner->getLineNumber() + 1,
                        $this->scanner->getLinePosition() + 1
                    );
                }
                throw ParseException::illegalCharacterInStringFound(
                    $current,
                    $this->scanner->getPosition(),
                    $this->scanner->getLineNumber() + 1,
                    $this->scanner->getLinePosition() + 1
                );
            }
            switch ($current) {
                case '"': {
                    return $this->createTokenType(TokenType::TOKEN_STRING, $buffer . $this->scanner->read());
                }
                case '\\': {
                    $buffer .= $this->scanEscape();
                    break;
                }
                default:
                    $buffer .= $this->scanner->read();
            }
        }

        throw ParseException::unexpectedEndOfFileFound(
            $this->scanner->getPosition(),
            $this->scanner->getLineNumber() + 1,
            $this->scanner->getLinePosition() + 1
        );
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return string
     */
    private function scanEscape(): string
    {
        $buffer = $this->scanner->read();
        $this->assertEndIsNotReachedYet();

        $current = $this->scanner->look();
        if (!\in_array($current, self::VALID_ESCAPE_CHARS, true)) {
            throw ParseException::illegalEscapeUsed(
                $this->scanner->getPosition(),
                $this->scanner->getLineNumber() + 1,
                $this->scanner->getLinePosition() + 1
            );
        }

        $buffer .= $this->scanner->read();
        if ('u' === $current) {
            for ($i = 0; $i < 4; $i++) {
                $this->assertEndIsNotReachedYet();
                $current = $this->scanner->look();
                if (!\ctype_xdigit($current)) {
                    throw ParseException::illegalEscapeUsed(
                        $this->scanner->getPosition(),
                        $this->scanner->getLineNumber() + 1,
                        $this->scanner->getLinePosition() + 1
                    );
                }
                $buffer .= $this->scanner->read();
            }
        }

        return $buffer;
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return Token
     */
    private function readNumber(): Token
    {
        $this->lockPosition();

        $buffer = '';
        if ('-' === $this->scanner->look()) {
            $buffer .= $this->scanner->read();
            $this->assertEndIsNotReachedYet();
            if (!\ctype_digit($this->scanner->look())) {
                throw ParseException::illegalNegativeSign(
                    $this->scanner->getPosition(),
                    $this->scanner->getLineNumber() + 1,
                    $this->scanner->getLinePosition() + 1
                );
            }
        }

        if ('0' === $this->scanner->look()) {
            $buffer .= $this->scanner->read();
            $this->assertEndIsNotReachedYet();
            $current = $this->scanner->look();
            if (null !== $current && \ctype_digit($current)) {
                throw ParseException::illegalOctalLiteral(
                    $this->scanner->getPosition(),
                    $this->scanner->getLineNumber() + 1,
                    $this->scanner->getLinePosition() + 1,
                    $current
                );
            }

            return $this->createTokenType(TokenType::TOKEN_INT);
        }

        $decimalFound = false;
        while (!$this->scanner->hasEndReached()) {
            $char = $this->scanner->look();
            if (\ctype_digit($char)) {
                $buffer .= $this->scanner->read();
            } elseif ('.' === $char && !$decimalFound) {
                return $this->createTokenType(TokenType::TOKEN_NUMBER, $buffer . $this->scanFraction());
            } elseif ('e' === $char || 'E' === $char) {
                return $this->createTokenType(TokenType::TOKEN_NUMBER, $buffer . $this->scanExponent());
            } else {
                return $this->createTokenType(TokenType::TOKEN_INT, $buffer);
            }
        }

        throw ParseException::unexpectedEndOfFileFound(
            $this->scanner->getPosition(),
            $this->scanner->getLineNumber() + 1,
            $this->scanner->getLinePosition() + 1
        );
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return string
     */
    private function scanExponent(): string
    {
        $buffer = $this->scanner->read();
        if (!\ctype_digit($this->scanner->look())) {
            throw ParseException::illegalEmptyExponent(
                $this->scanner->getPosition(),
                $this->scanner->getLineNumber() + 1,
                $this->scanner->getLinePosition() + 1
            );
        }

        while (!$this->scanner->hasEndReached()) {
            if (\ctype_digit($this->scanner->look())) {
                $buffer .= $this->scanner->read();
            } else {
                return $buffer;
            }
        }

        return $buffer;
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return string
     */
    private function scanFraction(): string
    {
        $buffer = $this->scanner->read();
        if (!\ctype_digit($this->scanner->look())) {
            throw ParseException::illegalTrailingDecimal(
                $this->scanner->getPosition(),
                $this->scanner->getLineNumber() + 1,
                $this->scanner->getLinePosition() + 1
            );
        }

        while (!$this->scanner->hasEndReached()) {
            $char = $this->scanner->look();
            if (\ctype_digit($char)) {
                $buffer .= $this->scanner->read();
            } elseif ('e' === $char || 'E' === $char) {
                return $buffer . $this->scanExponent();
            } else {
                return $buffer;
            }
        }

        return $buffer;
    }

    /**
     * @param string $lexeme
     * @throws \UnexpectedValueException
     * @return Token
     */
    private function createTokenForLexeme(string $lexeme): Token
    {
        return $this->createTokenType(self::CHAR_TOKEN_MAP[$lexeme], $lexeme);
    }

    /**
     * @param int $type
     * @param string|null $lexeme
     * @throws \UnexpectedValueException
     * @return Token
     */
    private function createTokenType(int $type, ?string $lexeme = null): Token
    {
        return new Token(
            $type,
            TokenType::getNameForTokenType($type),
            $this->position,
            $this->lineNumber,
            $this->linePosition,
            $lexeme
        );
    }

    /**
     * @throws LogicalException
     * @return void
     */
    private function assertScanner(): void
    {
        if (null === $this->scanner) {
            throw LogicalException::noInputGiven();
        }
    }

    /**
     * @throws LogicalException
     * @return void
     */
    private function assertEndOfFile(): void
    {
        if (null !== $this->currentToken && TokenType::TOKEN_EOF === $this->currentToken->getType()) {
            throw LogicalException::alreadyAtTheEndOfFile();
        }
    }

    /**
     * @throws ParseException
     * @return void
     */
    private function assertEndIsNotReachedYet(): void
    {
        if ($this->scanner->hasEndReached()) {
            throw ParseException::unexpectedEndOfFileFound(
                $this->scanner->getPosition(),
                $this->scanner->getLineNumber() + 1,
                $this->scanner->getLinePosition() + 1
            );
        }
    }

    /**
     * @return void
     */
    private function lockPosition(): void
    {
        $this->position = $this->scanner->getPosition();
        $this->lineNumber = $this->scanner->getLineNumber() + 1;
        $this->linePosition = $this->scanner->getLinePosition() + 1;
    }
}