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
class PlantUmlAstNodesGeneratorVisitor implements VisitorInterface
{
    /** @var string[] */
    private $objects = [];

    /** @var array  */
    private $links = [];

    /** @var int */
    private $level = 0;

    /** @var int[] */
    private $nodeCounts = [];

    /** @var string[] */
    private $parents = [];

    /** @var int */
    private $maxLevel = 20;

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
        $lines[] = 'AST objects diagram';
        $lines[] = '';
        $lines[] = 'end title';
        $lines[] = '';
        $lines[] = \implode(PHP_EOL, $this->objects);
        $lines[] = '';
        $lines[] = \implode(PHP_EOL, $this->links);
        $lines[] = '@enduml';

        return \implode(PHP_EOL, $lines);
    }

    public function visitJsonNode(JsonNode $jsonNode): void
    {
        $this->addObjectForNode('JsonNode', '  element: ElementNode');
        $this->pushParent('JsonNode');
        $jsonNode->getElement()->accept($this);
        $this->popParent();
    }

    public function visitElementNode(ElementNode $elementNode): void
    {
        $this->addObjectForNode('ElementNode', '  value: ValueNode');

        $this->pushParent('ElementNode');
        $elementNode->getValue()->accept($this);
        $this->popParent();

    }

    public function visitValueNode(ValueNode $valueNode): void
    {
        $parts = \explode('\\', \get_class($valueNode->getType()));

        $this->addObjectForNode('ValueNode', '  type: ' . \end($parts));
        $this->pushParent('ValueNode');
        $valueNode->getType()->accept($this);
        $this->popParent();

    }

    public function visitObjectNode(ObjectNode $objectNode): void
    {
        $this->addObjectForNode('ObjectNode', '  members: MemberNode[]');
        $this->pushParent('ObjectNode');
        foreach ($objectNode->getMembers() as $memberNode) {
            $memberNode->accept($this);
        }

        $this->popParent();
    }

    /**
     * @param MemberNode $memberNode
     * @return void
     */
    public function visitMemberNode(MemberNode $memberNode): void
    {
        $this->addObjectForNode('MemberNode', '  key: KeyNode' . PHP_EOL . '  value: ElementNode');
        $this->pushParent('MemberNode');
        $memberNode->getKey()->accept($this);
        $memberNode->getValue()->accept($this);
        $this->popParent();
    }

    /**
     * @param KeyNode $keyNode
     * @return void
     */
    public function visitKeyNode(KeyNode $keyNode): void
    {
        $this->addObjectForNode('KeyNode', '  value: ' . $keyNode->getValue());
    }

    public function visitArrayNode(ArrayNode $arrayNode): void
    {
        $this->addObjectForNode('ArrayNode', '  elements: ElementNode[]');
        $this->pushParent('ArrayNode');
        foreach ($arrayNode->getElements() as $elementNode) {
            $elementNode->accept($this);
        }
        $this->popParent();
    }

    public function visitStringNode(StringNode $stringNode): void
    {
        $this->addObjectForNode('StringNode', '  value: ' . $stringNode->getValue());
    }

    public function visitNumberNode(NumberNode $numberNode): void
    {
        $this->addObjectForNode('NumberNode', '  value: ' . $numberNode->getValue());
    }

    /**
     * @param IntegerNode $integerNode
     * @return void
     */
    public function visitIntegerNode(IntegerNode $integerNode): void
    {
        $this->addObjectForNode('IntegerNode', '  value: ' . $integerNode->getValue());
    }

    /**
     * @param BooleanNode $booleanNode
     * @return void
     */
    public function visitBooleanNode(BooleanNode $booleanNode): void
    {
        $this->addObjectForNode('BooleanNode', '  value: ' . ($booleanNode->getValue() ? 'true' : 'false'));
    }

    /**
     * @param NullNode $nullNode
     * @return void
     */
    public function visitNullNode(NullNode $nullNode): void
    {
        $this->addObjectForNode('NullNode');
    }

    /**
     * @param string $nodeName
     * @param string|null $body
     * @return void
     */
    private function addObjectForNode(string $nodeName, ?string $body = null): void
    {
        $this->incrementNodeCount($nodeName);
        $instanceName = $this->getInstanceName($nodeName);

        $text = 'object "' . $nodeName . '" as ' . $instanceName;
        if (null !== $body) {
            $text .= ' {' . PHP_EOL;
            $text .= $body . PHP_EOL;
            $text .= '}' . PHP_EOL;
        }
        if ($this->level < $this->maxLevel) {
            $this->objects[] = $text;
        }

        $this->linkWithParent($instanceName);
    }

    /**
     * @param string $name
     * @return void
     */
    private function linkWithParent(string $name): void
    {
        if (!empty($this->parents) && $this->level < $this->maxLevel) {
            $this->links[] = \end($this->parents) . ' --* ' . $name;
        }
    }

    /**
     * @param string $nodeName
     * @return void
     */
    private function pushParent(string $nodeName): void
    {
        $this->parents[] = $this->getInstanceName($nodeName);
        $this->level++;
    }

    /**
     * @return void
     */
    private function popParent(): void
    {
        $this->level--;
        \array_pop($this->parents);
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

        $name = $nodeName . $this->level;
        if (\array_key_exists($nodeName, $this->nodeCounts)) {
            $name .= $this->nodeCounts[$nodeName];
        }

        return $name;
    }
}