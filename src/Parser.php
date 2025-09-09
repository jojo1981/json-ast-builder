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

namespace Jojo1981\JsonAstBuilder;

use Jojo1981\JsonAstBuilder\Ast\ArrayNode;
use Jojo1981\JsonAstBuilder\Ast\BooleanNode;
use Jojo1981\JsonAstBuilder\Ast\ElementNode;
use Jojo1981\JsonAstBuilder\Ast\IntegerNode;
use Jojo1981\JsonAstBuilder\Ast\JsonNode;
use Jojo1981\JsonAstBuilder\Ast\KeyNode;
use Jojo1981\JsonAstBuilder\Ast\MemberNode;
use Jojo1981\JsonAstBuilder\Ast\NullNode;
use Jojo1981\JsonAstBuilder\Ast\NumberNode;
use Jojo1981\JsonAstBuilder\Ast\ObjectNode;
use Jojo1981\JsonAstBuilder\Ast\StringNode;
use Jojo1981\JsonAstBuilder\Ast\ValueNode;
use Jojo1981\JsonAstBuilder\Exception\ParseException;
use Jojo1981\JsonAstBuilder\Lexer\LexerInterface;
use Jojo1981\JsonAstBuilder\Lexer\Token;
use Jojo1981\JsonAstBuilder\Lexer\TokenType;
use UnexpectedValueException;
use function chr;
use function hexdec;
use function in_array;
use function preg_match;
use function strlen;
use function substr;

/**
 * The parser is responsible for generating an AST from the tokens it will get from the lexer and performs the
 * semantic analysis.
 *
 * @package Jojo1981\JsonAstBuilder
 */
final class Parser
{
    /** @var int[] */
    private const WHITE_SPACE_TOKENS = [
        TokenType::TOKEN_WHITE_SPACE,
        TokenType::TOKEN_NEWLINE
    ];

    /** @var LexerInterface */
    private LexerInterface $lexer;

    /**
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @param string $input
     * @return void
     * @throws ParseException
     */
    public function setInput(string $input): void
    {
        $this->lexer->setInput($input);
    }

    /**
     * @return JsonNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    public function parse(): JsonNode
    {
        $this->lexer->reset();

        return $this->json();
    }

    /**
     * @return JsonNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function json(): JsonNode
    {
        $jsonNode = new JsonNode($this->element());
        $this->eatToken(TokenType::TOKEN_EOF);

        return $jsonNode;
    }

    /**
     * @return ElementNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function element(): ElementNode
    {
        $this->eatWhiteSpace();
        $valueNode = $this->value();
        $this->eatWhiteSpace();

        return new ElementNode($valueNode);
    }

    /**
     * @return void
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function eatWhiteSpace(): void
    {
        while (in_array($this->lexer->getCurrent()->getType(), self::WHITE_SPACE_TOKENS, true)) {
            $this->lexer->getNext();
        }
    }

    /**
     * @return ValueNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function value(): ValueNode
    {
        $expectedTokens = [
            TokenType::getLiteral(TokenType::TOKEN_LEFT_CURLY_BRACKET),
            TokenType::getLiteral(TokenType::TOKEN_LEFT_SQUARE_BRACKET),
            TokenType::getLiteral(TokenType::TOKEN_STRING),
            TokenType::getLiteral(TokenType::TOKEN_NUMBER),
            TokenType::getLiteral(TokenType::TOKEN_INT),
            TokenType::getLiteral(TokenType::TOKEN_KEYWORD)
        ];

        $currentToken = $this->lexer->getCurrent();
        switch ($currentToken->getType()) {
            case (TokenType::TOKEN_EOF):
            {
                throw ParseException::unexpectedEndOfFile($this->lexer->getCurrent(), $expectedTokens);
            }
            case (TokenType::TOKEN_LEFT_CURLY_BRACKET):
            {
                $typeNode = $this->object();
                break;
            }
            case (TokenType::TOKEN_LEFT_SQUARE_BRACKET):
            {
                $typeNode = $this->array();
                break;
            }
            case (TokenType::TOKEN_STRING):
            {
                $typeNode = $this->string();
                break;
            }
            case (TokenType::TOKEN_NUMBER):
            {
                $typeNode = new NumberNode((float) $currentToken->getLexeme());
                $typeNode->setToken($currentToken);
                $this->eatToken(TokenType::TOKEN_NUMBER);
                break;
            }
            case (TokenType::TOKEN_INT):
            {
                $typeNode = new IntegerNode((int) $currentToken->getLexeme());
                $typeNode->setToken($currentToken);
                $this->eatToken(TokenType::TOKEN_INT);
                break;
            }
            case (TokenType::TOKEN_KEYWORD && in_array($currentToken->getLexeme(), ['true', 'false'], true)):
            {
                $typeNode = new BooleanNode('true' === $currentToken->getLexeme());
                $typeNode->setToken($currentToken);
                $this->eatToken(TokenType::TOKEN_KEYWORD);
                break;
            }
            case (TokenType::TOKEN_KEYWORD && 'null' === $currentToken->getLexeme()):
            {
                $typeNode = new NullNode();
                $typeNode->setToken($currentToken);
                $this->eatToken(TokenType::TOKEN_KEYWORD);
                break;
            }
            default:
                throw ParseException::unexpectedToken($currentToken, $expectedTokens);
        }

        return new ValueNode($typeNode);
    }

    /**
     * @return ObjectNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function object(): ObjectNode
    {
        $currentToken = $this->lexer->getCurrent();
        $this->eatToken(TokenType::TOKEN_LEFT_CURLY_BRACKET);
        $this->eatWhiteSpace();
        $members = $this->members();
        $this->eatWhiteSpace();
        $this->eatToken(TokenType::TOKEN_RIGHT_CURLY_BRACKET);

        $objectNode = new ObjectNode($members);
        $objectNode->setToken($currentToken);

        return $objectNode;
    }

    /**
     * @param int $tokenType
     * @return void
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function eatToken(int $tokenType): void
    {
        if ($this->lexer->getCurrent()->getType() !== $tokenType) {
            throw ParseException::unexpectedToken($this->lexer->getCurrent(), [TokenType::getLiteral($tokenType)]);
        }
        if (TokenType::TOKEN_EOF !== $tokenType) {
            $this->lexer->getNext();
        }
    }

    /**
     * @return MemberNode[]
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function members(): array
    {
        $members = [];
        while ($this->lexer->getCurrent()->getType() !== TokenType::TOKEN_RIGHT_CURLY_BRACKET) {
            $members[] = $this->member();
            if ($this->lexer->getCurrent()->getType() !== TokenType::TOKEN_RIGHT_CURLY_BRACKET) {
                $commaToken = $this->lexer->getCurrent();
                $this->eatToken(TokenType::TOKEN_COMMA);
                $this->eatWhiteSpace();
                if ($this->lexer->getCurrent()->getType() === TokenType::TOKEN_RIGHT_CURLY_BRACKET) {
                    ParseException::illegalTrailingComma($commaToken);
                }
            }
        }

        return $members;
    }

    /**
     * @return MemberNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function member(): MemberNode
    {
        $keyNode = $this->key();
        $this->eatWhiteSpace();
        $this->eatToken(TokenType::TOKEN_COLON);
        $this->eatWhiteSpace();
        $element = $this->element();

        return new MemberNode($keyNode, $element);
    }

    /**
     * @return KeyNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function key(): KeyNode
    {
        $currentToken = $this->lexer->getCurrent();
        $this->eatToken(TokenType::TOKEN_STRING);

        $keyNode = new KeyNode(self::parseStringValue($currentToken));
        $keyNode->setToken($currentToken);

        return $keyNode;
    }

    /**
     * @return ArrayNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function array(): ArrayNode
    {
        $token = $this->lexer->getCurrent();
        $this->eatToken(TokenType::TOKEN_LEFT_SQUARE_BRACKET);
        $this->eatWhiteSpace();
        $elements = $this->elements();
        $this->eatToken(TokenType::TOKEN_RIGHT_SQUARE_BRACKET);

        $arrayNode = new ArrayNode($elements);
        $arrayNode->setToken($token);

        return $arrayNode;
    }

    /**
     * @return ElementNode[]
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function elements(): array
    {
        $elements = [];
        while ($this->lexer->getCurrent()->getType() !== TokenType::TOKEN_RIGHT_SQUARE_BRACKET) {
            $elements[] = $this->element();
            if ($this->lexer->getCurrent()->getType() !== TokenType::TOKEN_RIGHT_SQUARE_BRACKET) {
                $commaToken = $this->lexer->getCurrent();
                $this->eatToken(TokenType::TOKEN_COMMA);
                $this->eatWhiteSpace();
                if ($this->lexer->getCurrent()->getType() === TokenType::TOKEN_RIGHT_SQUARE_BRACKET) {
                    throw ParseException::illegalTrailingComma($commaToken);
                }
            }
        }

        return $elements;
    }

    /**
     * @return StringNode
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    private function string(): StringNode
    {
        $currentToken = $this->lexer->getCurrent();
        $this->eatToken(TokenType::TOKEN_STRING);

        $stringNode = new StringNode(self::parseStringValue($currentToken));
        $stringNode->setToken($currentToken);

        return $stringNode;
    }

    /**
     * @param Token $token
     * @return string
     * @throws UnexpectedValueException
     */
    private static function parseStringValue(Token $token): string
    {
        if ($token->getType() !== TokenType::TOKEN_STRING) {
            throw new UnexpectedValueException('Token is not a string');
        }

        $result = '';
        $rawString = substr($token->getLexeme(), 1, -1);
        $length = strlen($rawString);
        for ($i = 0; $i < $length; $i++) {
            $char = $rawString[$i];
            if ('\\' === $char) {
                $nextChar = $rawString[$i + 1] ?? '';
                if (in_array($nextChar, ['"', '\\', '/', 'b', 'f', 'n', 'r', 't'], true)) {
                    $result .= self::parseControlChar($char . $nextChar);
                    $i++;
                } elseif ('u' === $nextChar) {
                    $unicodeSequence = substr($rawString, $i, 6);
                    if (strlen($unicodeSequence) !== 6 || !preg_match('/\\\\u[0-9a-fA-F]{4}/', $unicodeSequence)) {
                        throw new UnexpectedValueException('Invalid Unicode escape sequence: ' . $unicodeSequence);
                    }
                    $result .= self::parseUnicode($unicodeSequence);
                    $i += 5;
                } else {
                    throw new UnexpectedValueException('Invalid escape sequence: \\' . $nextChar);
                }
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * @param string $sequence
     * @return string
     * @throws UnexpectedValueException
     */
    private static function parseControlChar(string $sequence): string
    {
        return match ($sequence) {
            '\\"' => '"',
            '\\\\' => '\\',
            '\\/' => '/',
            '\\b' => "\b",
            '\\f' => "\f",
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            default => throw new UnexpectedValueException('Invalid escape sequence: ' . $sequence)
        };
    }

    /**
     * @param string $sequence
     * @return string
     * @throws UnexpectedValueException
     */
    private static function parseUnicode(string $sequence): string
    {
        $codePoint = hexdec(substr($sequence, 2));
        if ($codePoint <= 0x7F) {
            return chr($codePoint);
        } elseif ($codePoint <= 0x7FF) {
            return chr(0xC0 | ($codePoint >> 6)) .
                   chr(0x80 | ($codePoint & 0x3F));
        } elseif ($codePoint <= 0xFFFF) {
            return chr(0xE0 | ($codePoint >> 12)) .
                   chr(0x80 | (($codePoint >> 6) & 0x3F)) .
                   chr(0x80 | ($codePoint & 0x3F));
        } elseif ($codePoint <= 0x10FFFF) {
            return chr(0xF0 | ($codePoint >> 18)) .
                   chr(0x80 | (($codePoint >> 12) & 0x3F)) .
                   chr(0x80 | (($codePoint >> 6) & 0x3F)) .
                   chr(0x80 | ($codePoint & 0x3F));
        } else {
            throw new UnexpectedValueException('Invalid Unicode code point: ' . $sequence);
        }
    }
}
