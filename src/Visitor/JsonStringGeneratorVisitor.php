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

/**
 * @package Jojo1981\JsonAstBuilder\Visitor
 */
class JsonStringGeneratorVisitor implements VisitorInterface
{
    /** @var array */
    private $options;

    /** @var string */
    private $result = '';

    /** @var int */
    private $level = 0;

    /** @var string */
    private $indent;

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

    public function getResult(): string
    {
        return $this->result;
    }

    public function visitJsonNode(JsonNode $jsonNode): void
    {
        $jsonNode->getElement()->accept($this);
    }

    public function visitElementNode(ElementNode $elementNode): void
    {
        $elementNode->getValue()->accept($this);
    }

    public function visitValueNode(ValueNode $valueNode): void
    {
        $valueNode->getType()->accept($this);
    }

    public function visitObjectNode(ObjectNode $objectNode): void
    {
        $this->addText('{');

        $memberCount = \count($objectNode->getMembers());
        if ($memberCount > 0) {
            $this->level++;
            $this->addNewline();
            $this->addIndent();
            for ($i = 0; $i < $memberCount; $i++) {
                $member = $objectNode->getMembers()[$i];
                $member->accept($this);
                if ($i < $memberCount - 1) {
                    $this->addText(',');
                    $this->addNewline();
                    $this->addIndent();
                }
            }
            $this->level--;
            $this->addNewline();
            $this->addIndent();
        }

        $this->addText('}');
    }

    public function visitArrayNode(ArrayNode $arrayNode): void
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
                    $this->addNewline();
                    $this->addIndent();
                }
            }

            $this->level--;
            $this->addNewline();
            $this->addIndent();
        }
        $this->addText(']');
    }

    public function visitStringNode(StringNode $stringNode): void
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

    /**
     * @param IntegerNode $integerNode
     * @return void
     */
    public function visitIntegerNode(IntegerNode $integerNode): void
    {
        $this->addText((string) $integerNode->getValue());
    }

    /**
     * @param BooleanNode $booleanNode
     * @return void
     */
    public function visitBooleanNode(BooleanNode $booleanNode): void
    {
        $this->addText($booleanNode->getValue() ? 'true' : 'false');
    }

    /**
     * @param NullNode $nullNode
     * @return void
     */
    public function visitNullNode(NullNode $nullNode): void
    {
        $this->addText('null');
    }

    /**
     * @param MemberNode $memberNode
     * @return void
     */
    public function visitMemberNode(MemberNode $memberNode): void
    {
        $memberNode->getKey()->accept($this);
        $spaceBefore = \str_repeat(' ', $this->options['spacesBeforeColon']);
        $spaceAfter = \str_repeat(' ', $this->options['spacesAfterColon']);
        $this->addText($spaceBefore . ':' . $spaceAfter);
        $memberNode->getValue()->accept($this);
    }

    /**
     * @return void
     */
    private function addNewline(): void
    {
        if ($this->options['pretty']) {
            $this->result .= $this->options['lineSeparator'];
        }
    }

    /**
     * @return void
     */
    private function addIndent(): void
    {
        if ($this->options['pretty']) {
            $this->result .= \str_repeat($this->indent, $this->level);
        }
    }

    /**
     * @param string $text
     * @return void
     */
    private function addText(string $text): void
    {
        $this->result .= $text;
    }
}