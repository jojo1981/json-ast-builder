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

namespace Jojo1981\JsonAstBuilder\Visitor;

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
use Jojo1981\JsonAstBuilder\Lexer\Token;
use Jojo1981\JsonAstBuilder\Lexer\TokenType;
use UnexpectedValueException;
use function array_merge;
use function count;
use function str_repeat;
use function strlen;

/**
 * @package Jojo1981\JsonAstBuilder\Visitor
 */
final class TokenGeneratorVisitor implements VisitorInterface
{
    /** @var array */
    private array $options;

    /** @var int */
    private int $level = 0;

    /** @var string */
    private string $indent;

    /** @var int */
    private int $position = 0;

    /** @var int */
    private int $linePosition = 1;

    /** @var int */
    private int $lineNumber = 1;

    /** @var Token[] */
    private array $result = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'useTabs' => false,
            'pretty' => true,
            'indentSize' => 2,
            'spacesBeforeColon' => 0,
            'spacesAfterColon' => 1,
            'lineSeparator' => PHP_EOL
        ];
        $this->options = array_merge($defaults, $options);
        $this->indent = str_repeat($this->options['useTabs'] ? "\t" : ' ', $this->options['indentSize']);
    }

    /**
     * @return Token[]
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param JsonNode $jsonNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitJsonNode(JsonNode $jsonNode): mixed
    {
        $result = $jsonNode->getElement()->accept($this);
        $this->addToken(TokenType::TOKEN_EOF, null);

        return $result;
    }

    /**
     * @param int $tokenType
     * @param string|null $lexeme
     * @return void
     * @throws UnexpectedValueException
     */
    private function addToken(int $tokenType, ?string $lexeme): void
    {
        $tokenName = TokenType::getNameForTokenType($tokenType);
        $this->result[] = new Token($tokenType, $tokenName, $this->position, $this->lineNumber, $this->linePosition, $lexeme);
        if ($lexeme !== null) {
            $this->position += strlen($lexeme);
        }
    }

    /**
     * @param ElementNode $elementNode
     * @return mixed
     */
    public function visitElementNode(ElementNode $elementNode): mixed
    {
        return $elementNode->getValue()->accept($this);
    }

    /**
     * @param ValueNode $valueNode
     * @return mixed
     */
    public function visitValueNode(ValueNode $valueNode): mixed
    {
        return $valueNode->getType()->accept($this);
    }

    /**
     * @param ObjectNode $objectNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitObjectNode(ObjectNode $objectNode): mixed
    {
        $this->addToken(TokenType::TOKEN_LEFT_CURLY_BRACKET, '{');
        $memberCount = count($objectNode->getMembers());
        if ($memberCount > 0) {
            $this->level++;
            $this->addNewline();
            $this->addIndent();
            for ($i = 0; $i < $memberCount; $i++) {
                $member = $objectNode->getMembers()[$i];
                $this->addIndent();
                $member->accept($this);
                if ($i < $memberCount - 1) {
                    $this->addToken(TokenType::TOKEN_COMMA, ',');

                }
                $this->addNewline();
                $this->addIndent();
            }
            $this->level--;
        }
        $this->addToken(TokenType::TOKEN_RIGHT_CURLY_BRACKET, '}');

        return null;
    }

    /**
     * @return void
     * @throws UnexpectedValueException
     */
    private function addNewline(): void
    {
        if ($this->options['pretty']) {
            $type = TokenType::TOKEN_NEWLINE;
            $name = TokenType::getNameForTokenType($type);
            $this->result[] = new Token($type, $name, $this->position, $this->lineNumber, $this->linePosition, $this->options['lineSeparator']);
            $this->position += strlen($this->options['lineSeparator']);
            $this->linePosition = 1;
            $this->lineNumber++;
        }
    }

    /**
     * @return void
     * @throws UnexpectedValueException
     */
    private function addIndent(): void
    {
        if ($this->options['pretty']) {
            $this->addToken(TokenType::TOKEN_WHITE_SPACE, str_repeat($this->indent, $this->level));
        }
    }

    /**
     * @param ArrayNode $arrayNode
     * @return mixed
     * @throws UnexpectedValueException
     * @throws UnexpectedValueException
     */
    public function visitArrayNode(ArrayNode $arrayNode): mixed
    {
        $this->addToken(TokenType::TOKEN_LEFT_CURLY_BRACKET, '[');

        $elementCount = count($arrayNode->getElements());
        if ($elementCount > 0) {
            $this->level++;
            $this->addNewline();
            $this->addIndent();
            for ($i = 0; $i < $elementCount; $i++) {
                $element = $arrayNode->getElements()[$i];
                $element->accept($this);
                if ($i < $elementCount - 1) {
                    $this->addToken(TokenType::TOKEN_COMMA, ',');
                }
                $this->addNewline();
                $this->addIndent();
            }

            $this->level--;
        }
        $this->addToken(TokenType::TOKEN_RIGHT_CURLY_BRACKET, ']');

        return null;
    }

    /**
     * @param StringNode $stringNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitStringNode(StringNode $stringNode): mixed
    {
        $this->addToken(TokenType::TOKEN_STRING, '"' . $stringNode->getValue() . '"');

        return null;
    }

    /**
     * @param KeyNode $keyNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitKeyNode(KeyNode $keyNode): mixed
    {
        $this->addToken(TokenType::TOKEN_STRING, '"' . $keyNode->getValue() . '"');

        return null;
    }

    /**
     * @param NumberNode $numberNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitNumberNode(NumberNode $numberNode): mixed
    {
        $this->addToken(TokenType::TOKEN_NUMBER, (string) $numberNode->getValue());

        return null;
    }

    /**
     * @param IntegerNode $integerNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitIntegerNode(IntegerNode $integerNode): mixed
    {
        $this->addToken(TokenType::TOKEN_INT, (string) $integerNode->getValue());

        return null;
    }

    /**
     * @param BooleanNode $booleanNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitBooleanNode(BooleanNode $booleanNode): mixed
    {
        $this->addToken(TokenType::TOKEN_KEYWORD, $booleanNode->getValue() ? 'true' : 'false');

        return null;
    }

    /**
     * @param NullNode $nullNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitNullNode(NullNode $nullNode): mixed
    {
        $this->addToken(TokenType::TOKEN_KEYWORD, 'null');

        return null;
    }

    /**
     * @param MemberNode $memberNode
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function visitMemberNode(MemberNode $memberNode): mixed
    {
        $memberNode->getKey()->accept($this);
        $spaceBefore = '';
        $spaceAfter = '';
        if ($this->options['pretty']) {
            $spaceBefore = str_repeat(' ', $this->options['spacesBeforeColon']);
            $spaceAfter = str_repeat(' ', $this->options['spacesAfterColon']);
        }
        if ($spaceBefore !== '') {
            $this->addToken(TokenType::TOKEN_WHITE_SPACE, $spaceBefore);
        }
        $this->addToken(TokenType::TOKEN_COLON, ':');
        if ($spaceAfter !== '') {
            $this->addToken(TokenType::TOKEN_WHITE_SPACE, $spaceAfter);
        }
        $memberNode->getValue()->accept($this);
        $this->addNewline();

        return null;
    }
}
