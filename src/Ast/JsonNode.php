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
final class JsonNode implements NodeInterface
{
    /** @var ElementNode */
    private ElementNode $element;

    /**
     * @param ElementNode $element
     */
    public function __construct(ElementNode $element)
    {
        $this->element = $element;
    }

    /**
     * @return ElementNode
     */
    public function getElement(): ElementNode
    {
        return $this->element;
    }

    /**
     * @param ElementNode $element
     * @return void
     */
    public function setElement(ElementNode $element): void
    {
        $this->element = $element;
    }

    /**
     * @param VisitorInterface $visitor
     * @return mixed
     */
    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitJsonNode($this);
    }
}
