<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2025 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
declare(strict_types=1);

namespace Jojo1981\JsonAstBuilder\Ast;

use Jojo1981\JsonAstBuilder\Lexer\Token;

/**
 * @package Jojo1981\JsonAstBuilder\Ast
 */
interface NodeWithTokenInterface
{
    /**
     * @return Token|null
     */
    public function getToken(): ?Token;

    /**
     * @param Token|null $token
     * @return void
     */
    public function setToken(?Token $token): void;
}
