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
use Jojo1981\JsonAstBuilder\Helper\PlantUmlHelper;
use function array_key_exists;
use function array_pop;
use function end;
use function explode;
use function get_class;

/**
 * @package Jojo1981\JsonAstBuilder\Visitor
 */
final class PlantUmlAstNodesGeneratorVisitor implements VisitorInterface
{
    /** @var string[] */
    private array $objects = [];

    /** @var string[] */
    private array $links = [];

    /** @var int */
    private int $level = 0;

    /** @var int[] */
    private array $nodeCounts = [];

    /** @var string[] */
    private array $parents = [];

    /** @var int */
    private int $maxLevel = 20;

    /**
     * @return string
     */
    public function getResult(): string
    {
        return PlantUmlHelper::generateDocument($this->objects, $this->links);
    }

    public function visitJsonNode(JsonNode $jsonNode): mixed
    {
        $this->addObjectForNode('JsonNode', '  element: ElementNode');
        $this->pushParent('JsonNode');
        $jsonNode->getElement()->accept($this);
        $this->popParent();

        return null;
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
     * @param string $nodeName
     * @return void
     */
    private function incrementNodeCount(string $nodeName): void
    {
        if (!array_key_exists($nodeName, $this->nodeCounts)) {
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
        if (array_key_exists($nodeName, $this->nodeCounts)) {
            $name .= $this->nodeCounts[$nodeName];
        }

        return $name;
    }

    /**
     * @param string $name
     * @return void
     */
    private function linkWithParent(string $name): void
    {
        if (!empty($this->parents) && $this->level < $this->maxLevel) {
            $this->links[] = end($this->parents) . ' --* ' . $name;
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
        array_pop($this->parents);
    }

    public function visitElementNode(ElementNode $elementNode): mixed
    {
        $this->addObjectForNode('ElementNode', '  value: ValueNode');
        $this->pushParent('ElementNode');
        $elementNode->getValue()->accept($this);
        $this->popParent();

        return null;

    }

    public function visitValueNode(ValueNode $valueNode): mixed
    {
        $parts = explode('\\', get_class($valueNode->getType()));
        $this->addObjectForNode('ValueNode', '  type: ' . end($parts));
        $this->pushParent('ValueNode');
        $valueNode->getType()->accept($this);
        $this->popParent();

        return null;
    }

    public function visitObjectNode(ObjectNode $objectNode): mixed
    {
        $this->addObjectForNode('ObjectNode', '  members: MemberNode[]');
        $this->pushParent('ObjectNode');
        foreach ($objectNode->getMembers() as $memberNode) {
            $memberNode->accept($this);
        }
        $this->popParent();

        return null;
    }

    /**
     * @param MemberNode $memberNode
     * @return mixed
     */
    public function visitMemberNode(MemberNode $memberNode): mixed
    {
        $this->addObjectForNode('MemberNode', '  key: KeyNode' . PHP_EOL . '  value: ElementNode');
        $this->pushParent('MemberNode');
        $memberNode->getKey()->accept($this);
        $memberNode->getValue()->accept($this);
        $this->popParent();

        return null;
    }

    /**
     * @param KeyNode $keyNode
     * @return mixed
     */
    public function visitKeyNode(KeyNode $keyNode): mixed
    {
        $this->addObjectForNode('KeyNode', '  value: ' . $keyNode->getValue());

        return null;
    }

    public function visitArrayNode(ArrayNode $arrayNode): mixed
    {
        $this->addObjectForNode('ArrayNode', '  elements: ElementNode[]');
        $this->pushParent('ArrayNode');
        foreach ($arrayNode->getElements() as $elementNode) {
            $elementNode->accept($this);
        }
        $this->popParent();

        return null;
    }

    public function visitStringNode(StringNode $stringNode): mixed
    {
        $this->addObjectForNode('StringNode', '  value: ' . $stringNode->getValue());

        return null;
    }

    public function visitNumberNode(NumberNode $numberNode): mixed
    {
        $this->addObjectForNode('NumberNode', '  value: ' . $numberNode->getValue());

        return null;
    }

    /**
     * @param IntegerNode $integerNode
     * @return mixed
     */
    public function visitIntegerNode(IntegerNode $integerNode): mixed
    {
        $this->addObjectForNode('IntegerNode', '  value: ' . $integerNode->getValue());

        return null;
    }

    /**
     * @param BooleanNode $booleanNode
     * @return mixed
     */
    public function visitBooleanNode(BooleanNode $booleanNode): mixed
    {
        $this->addObjectForNode('BooleanNode', '  value: ' . ($booleanNode->getValue() ? 'true' : 'false'));

        return null;
    }

    /**
     * @param NullNode $nullNode
     * @return mixed
     */
    public function visitNullNode(NullNode $nullNode): mixed
    {
        $this->addObjectForNode('NullNode');

        return null;
    }
}
