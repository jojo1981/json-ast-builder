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

class ElementNode implements NodeInterface
{
    /** @var ValueNode */
    private $value;

    public function __construct(ValueNode $valueNode)
    {
        $this->value = $valueNode;
    }

    public function getValue(): ValueNode
    {
        return $this->value;
    }

    public function setValue(ValueNode $value): void
    {
        $this->value = $value;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitElementNode($this);
    }
}