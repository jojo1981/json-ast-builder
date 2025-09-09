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
final class ValueNode implements NodeInterface
{
    /** @var TypeNodeInterface */
    private TypeNodeInterface $type;

    /**
     * @param TypeNodeInterface $typeNode
     */
    public function __construct(TypeNodeInterface $typeNode)
    {
        $this->type = $typeNode;
    }

    /**
     * @return TypeNodeInterface
     */
    public function getType(): TypeNodeInterface
    {
        return $this->type;
    }

    /**
     * @param TypeNodeInterface $type
     * @return void
     */
    public function setType(TypeNodeInterface $type): void
    {
        $this->type = $type;
    }

    /**
     * @param VisitorInterface $visitor
     * @return mixed
     */
    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitValueNode($this);
    }
}
