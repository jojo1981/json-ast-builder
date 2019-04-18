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

class ValueNode implements NodeInterface
{
    /** @var TypeNodeInterface */
    private $type;

    public function __construct(TypeNodeInterface $typeNode)
    {
        $this->type = $typeNode;
    }

    public function getType(): TypeNodeInterface
    {
        return $this->type;
    }

    public function setType(TypeNodeInterface $type): void
    {
        $this->type = $type;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitValueNode($this);
    }
}