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
use Jojo1981\JsonAstBuilder\Ast\NodeInterface;
use Jojo1981\JsonAstBuilder\Ast\NullNode;
use Jojo1981\JsonAstBuilder\Ast\NumberNode;
use Jojo1981\JsonAstBuilder\Ast\ObjectNode;
use Jojo1981\JsonAstBuilder\Ast\StringNode;
use Jojo1981\JsonAstBuilder\Ast\ValueNode;

/**
 * @package Jojo1981\JsonAstBuilder\Visitor
 */
class AnalyzerVisitor implements VisitorInterface
{
    /** @var int */
    private $level = 0;

    /** @var int[] */
    private $levelCounts = [];

    /** @var int[] */
    private $nodeTypeCounts = [];

    /** @var string[] */
    private $parents = [];

    /**
     * @param JsonNode $jsonNode
     * @return mixed
     */
    public function visitJsonNode(JsonNode $jsonNode)
    {
        $nodeTree = [];
        $this->pushNode($jsonNode);
        $nodeTree['JsonNode'] = $jsonNode->getElement()->accept($this);
        $this->popNode();

        $maxLevel = \count($this->levelCounts);
        $nodeCount = \array_sum($this->levelCounts);
        $maxSiblings = \max($this->levelCounts);
        $levelsWithMaxSiblings = \array_keys(\array_filter(
            $this->levelCounts,
            static function (int $count) use ($maxSiblings): bool {
                return $count === $maxSiblings;
            }
        ));

        return [
            'maxLevel' => $maxLevel,
            'nodeCount' => $nodeCount,
            'maxSiblings' => $maxSiblings,
            'levelsWithMaxSiblings' => $levelsWithMaxSiblings,
            'averageSiblings' => $nodeCount / $maxLevel,
            'nodeTree' => $nodeTree
        ];
    }

    /**
     * @param ElementNode $elementNode
     * @return mixed
     */
    public function visitElementNode(ElementNode $elementNode)
    {
        $result = [];
        $this->pushNode($elementNode);
        $result['ElementNode'] = $elementNode->getValue()->accept($this);
        $this->popNode();

        return $result;
    }

    /**
     * @param ValueNode $valueNode
     * @return mixed
     */
    public function visitValueNode(ValueNode $valueNode)
    {
        $result = [];
        $this->pushNode($valueNode);
        $result['ValueNode'] = $valueNode->getType()->accept($this);
        $this->popNode();

        return $result;
    }

    /**
     * @param ObjectNode $objectNode
     * @return mixed
     */
    public function visitObjectNode(ObjectNode $objectNode)
    {
        $result = [];

        $this->pushNode($objectNode);
        foreach ($objectNode->getMembers() as $memberNode) {
            $result[] = $memberNode->accept($this);
        }
        $this->popNode();

        return [
            'ObjectNode' => $result
        ];
    }

    /**
     * @param MemberNode $memberNode
     * @return array
     */
    public function visitMemberNode(MemberNode $memberNode): array
    {
        $this->pushNode($memberNode);
        $result = [
            $memberNode->getKey()->accept($this),
            $memberNode->getValue()->accept($this)
        ];
        $this->popNode();

        return [
            'MemberNode' => $result
        ];
    }

    /**
     * @param ArrayNode $arrayNode
     * @return array
     */
    public function visitArrayNode(ArrayNode $arrayNode): array
    {
        $this->pushNode($arrayNode);
        $result = [];
        foreach ($arrayNode->getElements() as $elementNode) {
            $result[] = $elementNode->accept($this);
        }
        $this->popNode();

        return [
            'ArrayNode' => $result
        ];
    }

    /**
     * @param StringNode $stringNode
     * @return string
     */
    public function visitStringNode(StringNode $stringNode): string
    {
        $this->pushNode($stringNode);
        $this->popNode();

        return 'StringNode';
    }

    /**
     * @param NumberNode $numberNode
     * @return string
     */
    public function visitNumberNode(NumberNode $numberNode): string
    {
        $this->pushNode($numberNode);
        $this->popNode();

        return 'NumberNode';
    }

    /**
     * @param IntegerNode $integerNode
     * @return string
     */
    public function visitIntegerNode(IntegerNode $integerNode): string
    {
        $this->pushNode($integerNode);
        $this->popNode();

        return 'IntegerNode';
    }

    /**
     * @param BooleanNode $booleanNode
     * @return string
     */
    public function visitBooleanNode(BooleanNode $booleanNode): string
    {
        $this->pushNode($booleanNode);
        $this->popNode();

        return 'BooleanNode';
    }

    /**
     * @param NullNode $nullNode
     * @return string
     */
    public function visitNullNode(NullNode $nullNode): string
    {
        $this->pushNode($nullNode);
        $this->popNode();

        return 'NullNode';
    }

    /**
     * @param KeyNode $keyNode
     * @return string
     */
    public function visitKeyNode(KeyNode $keyNode): string
    {
        $this->pushNode($keyNode);
        $this->popNode();

        return 'KeyNode';
    }

    /**
     * @param NodeInterface $node
     * @return void
     */
    private function pushNode(NodeInterface $node): void
    {
        $this->level++;
        if (!\array_key_exists($this->level, $this->levelCounts)) {
            $this->levelCounts[$this->level] = 0;
        }
        $this->levelCounts[$this->level]++;
        $nodeName = $this->incrementNodeCount($node);

        $this->parents[] = $nodeName;
    }

    /**
     * @return void
     */
    private function popNode(): void
    {
        $this->level--;
        \array_pop($this->parents);
    }

    /**
     * @param NodeInterface $node
     * @return string
     */
    private function incrementNodeCount(NodeInterface $node): string
    {
        $nodeName = $this->getNodeNameFromNode($node);
        if (!\array_key_exists($nodeName, $this->nodeTypeCounts)) {
            $this->nodeTypeCounts[$nodeName] = 0;
        }

        return $nodeName . ++$this->nodeTypeCounts[$nodeName];
    }

    /**
     * @param NodeInterface $node
     * @return string
     */
    private function getNodeNameFromNode(NodeInterface $node): string
    {
        $parts = \explode('\\', \get_class($node));

        return \end($parts);
    }
}