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

namespace Jojo1981\JsonAstBuilder\Ast;

use Jojo1981\JsonAstBuilder\Visitor\VisitorInterface;

/**
 * @package Jojo1981\JsonAstBuilder\Ast
 */
final class MemberNode implements NodeInterface
{
    /** @var KeyNode */
    private KeyNode $key;

    /** @var ElementNode */
    private ElementNode $value;

    public function __construct(KeyNode $key, ElementNode $element)
    {
        $this->key = $key;
        $this->value = $element;
    }

    /**
     * @return KeyNode
     */
    public function getKey(): KeyNode
    {
        return $this->key;
    }

    /**
     * @param KeyNode $key
     * @return void
     */
    public function setKey(KeyNode $key): void
    {
        $this->key = $key;
    }

    /**
     * @return ElementNode
     */
    public function getValue(): ElementNode
    {
        return $this->value;
    }

    /**
     * @param ElementNode $value
     * @return void
     */
    public function setValue(ElementNode $value): void
    {
        $this->value = $value;
    }

    /**
     * @param VisitorInterface $visitor
     * @return mixed
     */
    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitMemberNode($this);
    }
}
