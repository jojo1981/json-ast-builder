<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JsonAstBuilder\Exception;

/**
 * @package Jojo1981\JsonAstBuilder\Exception
 */
class LogicalException extends JsonException
{
    /**
     * @return LogicalException
     */
    public static function noInputGiven(): LogicalException
    {
        return new static(
            'Can not parse json string because no input is given. Set input first by calling the `setInput` method'
        );
    }

    /**
     * @return LogicalException
     */
    public static function alreadyAtTheEndOfFile(): LogicalException
    {
        return new static(<<<EOT
The Lexer is already at the end of the input stream and an EOF token has already been retrieved by an earlier invocation the: `getNext method.
You'll need to reset the Lexer first calling the `reset` method or set a new input by calling the `setInput` method.
When still parsing the token stream some logic in your parser needs to be fixed and do not call the `getNext` method when a EOF token already has been returned
EOT
        );
    }
}