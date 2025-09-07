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
use function array_merge;
use function count;
use function str_repeat;

/**
 * @package Jojo1981\JsonAstBuilder\Visitor
 */
final class JsonStringGeneratorVisitor implements VisitorInterface
{
    /** @var array */
    private array $options;

    /** @var string */
    private string $result = '';

    /** @var int */
    private int $level = 0;

    /** @var string */
    private string $indent;

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
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @param JsonNode $jsonNode
     * @return mixed
     */
    public function visitJsonNode(JsonNode $jsonNode): mixed
    {
        return $jsonNode->getElement()->accept($this);
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
     */
    public function visitObjectNode(ObjectNode $objectNode): mixed
    {
        $this->addText('{');

        $memberCount = count($objectNode->getMembers());
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

        return null;
    }

    /**
     * @param string $text
     * @return void
     */
    private function addText(string $text): void
    {
        $this->result .= $text;
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
            $this->result .= str_repeat($this->indent, $this->level);
        }
    }

    /**
     * @param ArrayNode $arrayNode
     * @return mixed
     */
    public function visitArrayNode(ArrayNode $arrayNode): mixed
    {
        $this->addText('[');

        $elementCount = count($arrayNode->getElements());
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

        return null;
    }

    /**
     * @param StringNode $stringNode
     * @return mixed
     */
    public function visitStringNode(StringNode $stringNode): mixed
    {
        $this->addText('"' . $stringNode->getValue() . '"');

        return null;
    }

    /**
     * @param KeyNode $keyNode
     * @return mixed
     */
    public function visitKeyNode(KeyNode $keyNode): mixed
    {
        $this->addText('"' . $keyNode->getValue() . '"');

        return null;
    }

    /**
     * @param NumberNode $numberNode
     * @return mixed
     */
    public function visitNumberNode(NumberNode $numberNode): mixed
    {
        $this->addText((string) $numberNode->getValue());

        return null;
    }

    /**
     * @param IntegerNode $integerNode
     * @return mixed
     */
    public function visitIntegerNode(IntegerNode $integerNode): mixed
    {
        $this->addText((string) $integerNode->getValue());

        return null;
    }

    /**
     * @param BooleanNode $booleanNode
     * @return mixed
     */
    public function visitBooleanNode(BooleanNode $booleanNode): mixed
    {
        $this->addText($booleanNode->getValue() ? 'true' : 'false');

        return null;
    }

    /**
     * @param NullNode $nullNode
     * @return mixed
     */
    public function visitNullNode(NullNode $nullNode): mixed
    {
        $this->addText('null');

        return null;
    }

    /**
     * @param MemberNode $memberNode
     * @return mixed
     */
    public function visitMemberNode(MemberNode $memberNode): mixed
    {
        $memberNode->getKey()->accept($this);
        $spaceBefore = str_repeat(' ', $this->options['spacesBeforeColon']);
        $spaceAfter = str_repeat(' ', $this->options['spacesAfterColon']);
        $this->addText($spaceBefore . ':' . $spaceAfter);
        $memberNode->getValue()->accept($this);

        return null;
    }
}
