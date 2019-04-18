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

class ArrayNode implements TypeNodeInterface
{
    use TokenAwareTrait;

    /** @var ElementNode[] */
    private $elements = [];

    /**
     * @param ElementNode[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->setElements($elements);
    }

    /**
     * @return ElementNode[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param ElementNode[] $elements
     * @return void
     */
    public function setElements(array $elements): void
    {
        $this->elements = [];
        \array_walk($elements, [$this, 'addElement']);
    }

    /**
     * @param ElementNode $element
     * @return void
     */
    private function addElement(ElementNode $element): void
    {
        $this->elements[] = $element;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitArrayNode($this);
    }
}