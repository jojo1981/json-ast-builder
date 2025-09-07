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
final class ElementNode implements NodeInterface
{
    /** @var ValueNode */
    private ValueNode $value;

    public function __construct(ValueNode $valueNode)
    {
        $this->value = $valueNode;
    }

    /**
     * @return ValueNode
     */
    public function getValue(): ValueNode
    {
        return $this->value;
    }

    /**
     * @param ValueNode $value
     * @return void
     */
    public function setValue(ValueNode $value): void
    {
        $this->value = $value;
    }

    /**
     * @param VisitorInterface $visitor
     * @return mixed
     */
    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitElementNode($this);
    }
}
