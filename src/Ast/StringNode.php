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
final class StringNode implements TypeNodeInterface
{
    use TokenAwareTrait;

    /** @var string */
    private string $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @param VisitorInterface $visitor
     * @return mixed
     */
    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitStringNode($this);
    }
}
