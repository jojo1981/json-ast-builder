<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
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
use Jojo1981\JsonAstBuilder\Lexer\TokenType;

/**
 * The parser is responsible for generating an AST from the tokens it will get from the lexer and performs the
 * semantic analysis.
 *
 * @package Jojo1981\JsonAstBuilder
 */
class Parser
{
    /** @var int[] */
    private const WHITE_SPACE_TOKENS = [
        TokenType::TOKEN_WHITE_SPACE,
        TokenType::TOKEN_NEWLINE
    ];

    /** @var LexerInterface */
    private $lexer;

    /**
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @param string $input
     * @throws ParseException
     * @return void
     */
    public function setInput(string $input): void
    {
        $this->lexer->setInput($input);
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return JsonNode
     */
    public function parse(): JsonNode
    {
        $this->lexer->reset();

        return $this->json();
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return JsonNode
     */
    private function json(): JsonNode
    {
        $jsonNode = new JsonNode($this->element());
        $this->eatToken(TokenType::TOKEN_EOF);

        return $jsonNode;
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return ElementNode
     */
    private function element(): ElementNode
    {
        $this->eatWhiteSpace();
        $valueNode = $this->value();
        $this->eatWhiteSpace();

        return new ElementNode($valueNode);
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return ValueNode
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
            case (TokenType::TOKEN_EOF): {
                throw ParseException::unexpectedEndOfFile($this->lexer->getCurrent(), $expectedTokens);
            }
            case (TokenType::TOKEN_LEFT_CURLY_BRACKET): {
                $typeNode = $this->object();
                break;
            }
            case (TokenType::TOKEN_LEFT_SQUARE_BRACKET): {
                $typeNode = $this->array();
                break;
            }
            case (TokenType::TOKEN_STRING): {
                $typeNode = $this->string();
                break;
            }
            case (TokenType::TOKEN_NUMBER): {
                $typeNode = new NumberNode((float) $currentToken->getLexeme());
                $typeNode->setToken($currentToken);
                $this->eatToken(TokenType::TOKEN_NUMBER);
                break;
            }
            case (TokenType::TOKEN_INT): {
                $typeNode = new IntegerNode((int) $currentToken->getLexeme());
                $typeNode->setToken($currentToken);
                $this->eatToken(TokenType::TOKEN_INT);
                break;
            }
            case (TokenType::TOKEN_KEYWORD && \in_array($currentToken->getLexeme(), ['true', 'false'], true)): {
                $typeNode = new BooleanNode('true' === $currentToken->getLexeme());
                $typeNode->setToken($currentToken);
                $this->eatToken(TokenType::TOKEN_KEYWORD);
                break;
            }
            case (TokenType::TOKEN_KEYWORD && 'null' === $currentToken->getLexeme()): {
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
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return StringNode
     */
    private function string(): StringNode
    {
        $currentToken = $this->lexer->getCurrent();
        $this->eatToken(TokenType::TOKEN_STRING);

        $stringNode = new StringNode(\substr($currentToken->getLexeme(), 1, -1));
        $stringNode->setToken($currentToken);

        return $stringNode;
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return KeyNode
     */
    private function key(): KeyNode
    {
        $currentToken = $this->lexer->getCurrent();
        $this->eatToken(TokenType::TOKEN_STRING);

        $keyNode = new KeyNode(\substr($currentToken->getLexeme(), 1, -1));
        $keyNode->setToken($currentToken);

        return $keyNode;
    }

    /**
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return ObjectNode
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
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return MemberNode[]
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
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return MemberNode
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
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return ArrayNode
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
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return ElementNode[]
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
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return void
     */
    private function eatWhiteSpace(): void
    {
        while (\in_array($this->lexer->getCurrent()->getType(), self::WHITE_SPACE_TOKENS, true)) {
            $this->lexer->getNext();
        }
    }

    /**
     * @param int $tokenType
     * @throws ParseException
     * @throws \UnexpectedValueException
     * @return void
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
}