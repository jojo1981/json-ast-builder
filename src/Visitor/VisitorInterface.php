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

/**
 * @package Jojo1981\JsonAstBuilder\Visitor
 */
interface VisitorInterface
{
    /**
     * @param JsonNode $jsonNode
     * @return mixed
     */
    public function visitJsonNode(JsonNode $jsonNode): mixed;

    /**
     * @param ElementNode $elementNode
     * @return mixed
     */
    public function visitElementNode(ElementNode $elementNode): mixed;

    /**
     * @param ValueNode $valueNode
     * @return mixed
     */
    public function visitValueNode(ValueNode $valueNode): mixed;

    /**
     * @param ObjectNode $objectNode
     * @return mixed
     */
    public function visitObjectNode(ObjectNode $objectNode): mixed;

    /**
     * @param ArrayNode $arrayNode
     * @return mixed
     */
    public function visitArrayNode(ArrayNode $arrayNode): mixed;

    /**
     * @param StringNode $stringNode
     * @return mixed
     */
    public function visitStringNode(StringNode $stringNode): mixed;

    /**
     * @param NumberNode $numberNode
     * @return mixed
     */
    public function visitNumberNode(NumberNode $numberNode): mixed;

    /**
     * @param IntegerNode $integerNode
     * @return mixed
     */
    public function visitIntegerNode(IntegerNode $integerNode): mixed;

    /**
     * @param BooleanNode $booleanNode
     * @return mixed
     */
    public function visitBooleanNode(BooleanNode $booleanNode): mixed;

    /**
     * @param NullNode $nullNode
     * @return mixed
     */
    public function visitNullNode(NullNode $nullNode): mixed;

    /**
     * @param MemberNode $memberNode
     * @return mixed
     */
    public function visitMemberNode(MemberNode $memberNode): mixed;

    /**
     * @param KeyNode $keyNode
     * @return mixed
     */
    public function visitKeyNode(KeyNode $keyNode): mixed;
}
