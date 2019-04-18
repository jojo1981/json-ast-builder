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
class PlantUmlDataGeneratorVisitor implements VisitorInterface
{
    /** @var string[] */
    private $objects = [];

    /** @var string[] */
    private $links = [];

    /** @var int */
    private $level = 0;

    /** @var int[] */
    private $nodeCounts = [];

    /** @var string[] */
    private $parents = [];

    /** @var string[] */
    private $keys = [];

    /** @var string[] */
    private $states = [];

    /**
     * @return string
     */
    public function getResult(): string
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
        $lines[] = \implode(PHP_EOL, $this->objects);
        $lines[] = '';
        $lines[] = \implode(PHP_EOL, $this->links);
        $lines[] = '@enduml';

        return \implode(PHP_EOL, $lines);
    }

    /**
     * @param JsonNode $jsonNode
     * @return void
     */
    public function visitJsonNode(JsonNode $jsonNode): void
    {
        $jsonNode->getElement()->accept($this);
    }

    /**
     * @param ElementNode $elementNode
     * @return mixed
     */
    public function visitElementNode(ElementNode $elementNode)
    {
        return $elementNode->getValue()->accept($this);
    }

    /**
     * @param ValueNode $valueNode
     * @return mixed
     */
    public function visitValueNode(ValueNode $valueNode)
    {
        return $valueNode->getType()->accept($this);
    }

    /**
     * @param ObjectNode $objectNode
     * @return string
     */
    public function visitObjectNode(ObjectNode $objectNode): string
    {
        $lastState = !empty($this->states) ? \end($this->states) : null;
        $this->states[] = 'OBJECT';
        $this->level++;
        $this->incrementNodeCount('ObjectNode');
        $name = $this->getInstanceName('ObjectNode');
        $this->parents[] = $name;

        $objectName = 'ObjectNode';
        if (!empty($this->keys)) {
            $objectName = \end($this->keys);
        }

        $text = 'object "**' . $objectName . '**" as ' . $name;
        if (\count($objectNode->getMembers()) > 0) {
            $text .= ' {' . PHP_EOL;
            foreach ($objectNode->getMembers() as $memberNode) {
                [$key, $value] = $memberNode->accept($this);
                $text .= '  **' . $key . '**: ' . $value . PHP_EOL;
            }
            $text .= '}';
        }
        $text .= PHP_EOL;
        $this->objects[] = $text;

        \array_pop($this->parents);

        if (!empty($this->parents)) {
            $parent = \end($this->parents);
            $linkName = null;
            $arrow = $lastState === 'ARRAY' ? '--o' : '--*';
            $this->links[] = $parent . (null !== $linkName ? ' "' . $linkName .  '"' : '') . ' ' . $arrow . ' ' . $name;
        }

        $this->level--;

        \array_pop($this->states);

        return 'object';
    }

    /**
     * @param ArrayNode $arrayNode
     * @return string
     */
    public function visitArrayNode(ArrayNode $arrayNode): string
    {
        $lastState = !empty($this->states) ? \end($this->states) : null;
        $this->states[] = 'ARRAY';
        $this->level++;
        $this->incrementNodeCount('ArrayNode');
        $name = $this->getInstanceName('ArrayNode');
        $this->parents[] = $name;

        $objectName = 'ArrayNode';
        if (!empty($this->keys)) {
            $objectName = \end($this->keys);
        }

        $keys = $this->keys;
        $this->keys = [];

        $text = 'object "' . $objectName . '" as ' . $name;
        if (\count($arrayNode->getElements()) > 0) {
            $text .= ' {' . PHP_EOL;
            $text .= '  element count: ' . \count($arrayNode->getElements()) . PHP_EOL;
            foreach ($arrayNode->getElements() as $index => $elementNode) {
                //$text .= '  ' . $elementNode->accept($this) . PHP_EOL;
                $this->keys[] = 'element ' . ++$index;
                $elementNode->accept($this);
                \array_pop($this->keys);
            }
            $text .= '}';
        }
        $text .= PHP_EOL;
        $this->objects[] = $text;

        \array_pop($this->parents);

        $this->keys = $keys;

        if (!empty($this->parents)) {
            $parent = \end($this->parents);
            $linkName = null;
            $arrow = $lastState === 'ARRAY' ? '--o' : '--*';
            $this->links[] = $parent . (null !== $linkName ? ' "' . $linkName .  '"' : '') . ' ' . $arrow . ' ' . $name;
        }

        $this->level--;

        \array_pop($this->states);

        return 'array';
    }

    /**
     * @param StringNode $stringNode
     * @return string
     */
    public function visitStringNode(StringNode $stringNode): string
    {
        $result = $stringNode->getValue();
        if (\strlen($result) > 25) {
            $result = \substr($result, 0, 25) . '...';
        }

        return $result;
    }

    /**
     * @param KeyNode $keyNode
     * @return string
     */
    public function visitKeyNode(KeyNode $keyNode): string
    {
        return $keyNode->getValue();
    }

    /**
     * @param NumberNode $numberNode
     * @return float
     */
    public function visitNumberNode(NumberNode $numberNode): float
    {
        return $numberNode->getValue();
    }

    /**
     * @param IntegerNode $integerNode
     * @return int
     */
    public function visitIntegerNode(IntegerNode $integerNode): int
    {
        return $integerNode->getValue();
    }

    /**
     * @param BooleanNode $booleanNode
     * @return bool
     */
    public function visitBooleanNode(BooleanNode $booleanNode): bool
    {
        return $booleanNode->getValue();
    }

    /**
     * @param NullNode $nullNode
     * @return null
     */
    public function visitNullNode(NullNode $nullNode)
    {
        return null;
    }

    /**
     * @param MemberNode $memberNode
     * @return array
     */
    public function visitMemberNode(MemberNode $memberNode): array
    {
        $key = $memberNode->getKey()->accept($this);
        $this->keys[] = $key;
        $value = $memberNode->getValue()->accept($this);
        \array_pop($this->keys);

        return [$key, $value];
    }

    /**
     * @param string $nodeName
     * @return void
     */
    private function incrementNodeCount(string $nodeName): void
    {
        if (!\array_key_exists($nodeName, $this->nodeCounts)) {
            $this->nodeCounts[$nodeName] = 0;
        }

        $this->nodeCounts[$nodeName]++;
    }

    /**
     * @param string $nodeName
     * @return string
     */
    private function getInstanceName(string $nodeName): string
    {
        if (0 === $this->level) {
            return $nodeName;
        }

        $name = $nodeName; // . $this->level;
        if (\array_key_exists($nodeName, $this->nodeCounts)) {
            $name .= $this->nodeCounts[$nodeName];
        }

        return $name;
    }
}