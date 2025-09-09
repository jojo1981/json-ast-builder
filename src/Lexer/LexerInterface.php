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

namespace Jojo1981\JsonAstBuilder\Lexer;

use Jojo1981\JsonAstBuilder\Exception\ParseException;
use UnexpectedValueException;

/**
 * The interface for all lexer classes. There responsible for the lexical analysis and should produce a token stream.
 * When the input is not valid a syntax error exception should be thrown.
 *
 * @package Jojo1981\JsonAstBuilder\Lexer
 */
interface LexerInterface
{
    /**
     * @param string $input
     * @return void
     * @throws ParseException
     */
    public function setInput(string $input): void;

    /**
     * Reset the lexer to teh beginning of the input
     *
     * @return void
     */
    public function reset(): void;

    /**
     * @return Token
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    public function getCurrent(): Token;

    /**
     * @return Token
     * @throws UnexpectedValueException
     * @throws ParseException
     */
    public function getNext(): Token;
}
