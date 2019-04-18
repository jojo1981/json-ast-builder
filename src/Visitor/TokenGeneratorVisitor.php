<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
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
use Jojo1981\JsonAstBuilder\Lexer;
use Jojo1981\JsonAstBuilder\Lexer\Token;

class TokenGeneratorVisitor implements VisitorInterface
{
    /** @var array */
    private $options;

    /** @var int */
    private $level = 0;

    /** @var string */
    private $indent;

    /** @var int */
    private $position = 0;

    /** @var int */
    private $linePosition = 1;

    /** @var int */
    private $lineNumber = 1;

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
        $this->options = \array_merge($defaults, $options);
        $this->indent = \str_repeat($this->options['useTabs'] ? "\t" : ' ', $this->options['indentSize']);
    }

    public function visitJsonNode(JsonNode $jsonNode)
    {
        $jsonNode->getElement()->accept($this);
    }

    public function visitElementNode(ElementNode $elementNode)
    {
        $elementNode->getValue()->accept($this);
    }

    public function visitValueNode(ValueNode $valueNode)
    {
        $valueNode->getType()->accept($this);
    }

    public function visitObjectNode(ObjectNode $objectNode)
    {
        $this->addText('{');

        $memberCount = \count($objectNode->getMembers());
        if ($memberCount > 0) {
            $this->level++;
            $this->addNewline();
            $this->addIndent();
            for ($i = 0; $i < $memberCount; $i++) {
                $member = $objectNode->getMembers()[$i];
                $this->addIndent();
                $member->accept($this);
                if ($i < $memberCount - 1) {
                    $this->addText(',');

                }
                $this->addNewline();
                $this->addIndent();
            }
            $this->level--;
        }

        $this->addText('}');
    }

    public function visitArrayNode(ArrayNode $arrayNode)
    {
        $this->addText('[');

        $elementCount = \count($arrayNode->getElements());
        if ($elementCount > 0) {
            $this->level++;
            $this->addNewline();
            $this->addIndent();
            for ($i = 0; $i < $elementCount; $i++) {
                $element = $arrayNode->getElements()[$i];
                $element->accept($this);
                if ($i < $elementCount - 1) {
                    $this->addText(',');
                }
                $this->addNewline();
                $this->addIndent();
            }

            $this->level--;
        }
        $this->addText(']');
    }

    public function visitStringNode(StringNode $stringNode)
    {
        $this->addText('"' . $stringNode->getValue() . '"');
    }

    public function visitKeyNode(KeyNode $keyNode): void
    {
        $this->addText('"' . $keyNode->getValue() . '"');
    }

    public function visitNumberNode(NumberNode $numberNode): void
    {
        $this->addText((string) $numberNode->getValue());
    }

    public function visitIntegerNode(IntegerNode $integerNode)
    {
        $this->addText((string) $integerNode->getValue());
    }

    public function visitBooleanNode(BooleanNode $booleanNode)
    {
        $this->addText($booleanNode->getValue() ? 'true' : 'false');
    }

    public function visitNullNode(NullNode $nullNode)
    {
        $this->addText('null');
    }

    public function visitMemberNode(MemberNode $memberNode)
    {
        $memberNode->getKey()->accept($this);
        $spaceBefore = \str_repeat(' ', $this->options['spacesBeforeColon']);
        $spaceAfter = \str_repeat(' ', $this->options['spacesAfterColon']);
        $this->addText($spaceBefore . ':' . $spaceAfter);
        $memberNode->getValue()->accept($this);
        $this->addNewline();
    }

    private function getToken()
    {

    }

    private function addNewline()
    {
        if ($this->options['pretty']) {
//            $this->result[] = new Token(Lexer::TOKEN_NEWLINE, '', )$this->options['lineSeparator'];
        }
    }

    private function addIndent()
    {
        if ($this->options['pretty']) {
            $this->result .= \str_repeat($this->indent, $this->level);
        }
    }

    private function addText(string $text)
    {
        $this->result .= $text;
    }
}