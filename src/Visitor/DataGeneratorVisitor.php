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
class DataGeneratorVisitor implements VisitorInterface
{
    /** @var array */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = \array_merge(['assoc' => false], $options);
    }

    /**
     * @param JsonNode $jsonNode
     * @return mixed
     */
    public function visitJsonNode(JsonNode $jsonNode)
    {
        return $jsonNode->getElement()->accept($this);
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
     * @return array|\stdClass
     */
    public function visitObjectNode(ObjectNode $objectNode)
    {
        $result = $this->isAssoc() ? [] : new \stdClass();

        foreach ($objectNode->getMembers() as $member) {
            [$key, $value] = $member->accept($this);
            if ($this->isAssoc()) {
                $result[$key] = $value;
            } else {
                $result->{$key} = $value;
            }
        }

        return $result;
    }

    /**
     * @param ArrayNode $arrayNode
     * @return array
     */
    public function visitArrayNode(ArrayNode $arrayNode): array
    {
        $result = [];
        foreach ($arrayNode->getElements() as $element) {
            $result[] = $element->accept($this);
        }

        return $result;
    }

    /**
     * @param StringNode $stringNode
     * @return string
     */
    public function visitStringNode(StringNode $stringNode): string
    {
        return $stringNode->getValue();
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
        $value = $memberNode->getValue()->accept($this);

        return [$key, $value];
    }

    /**
     * @return bool
     */
    private function isAssoc(): bool
    {
        return $this->options['assoc'];
    }
}