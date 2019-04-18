<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JsonAstBuilder\Ast;

use Jojo1981\JsonAstBuilder\Visitor\VisitorInterface;

class MemberNode implements NodeInterface
{
    /** @var KeyNode */
    private $key;

    /** @var ElementNode */
    private $value;

    public function __construct(KeyNode $key, ElementNode $element)
    {
        $this->key = $key;
        $this->value = $element;
    }

    public function getKey(): KeyNode
    {
        return $this->key;
    }

    public function setKey(KeyNode $key): void
    {
        $this->key = $key;
    }

    public function getValue(): ElementNode
    {
        return $this->value;
    }

    public function setValue(ElementNode $value): void
    {
        $this->value = $value;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitMemberNode($this);
    }
}