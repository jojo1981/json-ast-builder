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

class JsonNode implements NodeInterface
{
    /** @var ElementNode */
    private $element;

    public function __construct(ElementNode $element)
    {
        $this->element = $element;
    }

    public function getElement(): ElementNode
    {
        return $this->element;
    }

    public function setElement(ElementNode $element): void
    {
        $this->element = $element;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitJsonNode($this);
    }
}