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

class ObjectNode implements TypeNodeInterface
{
    use TokenAwareTrait;

    /** @var MemberNode[] */
    private $members = [];

    /**
     * @param MemberNode[] $members
     */
    public function __construct(array $members = [])
    {
        $this->setMembers($members);
    }

    /**
     * @return MemberNode[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @param MemberNode[] $members
     * @return void
     */
    public function setMembers(array $members): void
    {
        $this->members = [];
        \array_walk($members, [$this, 'addMember']);
    }

    /**
     * @param MemberNode $member
     * @return void
     */
    private function addMember(MemberNode $member): void
    {
        $this->members[] = $member;
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitObjectNode($this);
    }
}