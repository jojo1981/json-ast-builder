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

namespace tests\Jojo1981\JsonAstBuilder\Test;

use Jojo1981\JsonAstBuilder\Exception\LogicalException;
use Jojo1981\JsonAstBuilder\Exception\ParseException;
use Jojo1981\JsonAstBuilder\Lexer;
use Jojo1981\JsonAstBuilder\Lexer\Token;
use Jojo1981\JsonAstBuilder\Lexer\TokenType;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use UnexpectedValueException;
use function end;
use function file_get_contents;
use function unserialize;

/**
 * @package tests\Jojo1981\JsonAstBuilder\Test
 */
final class LexerTest extends TestCase
{
    /**
     * @return void
     * @throws ParseException
     * @throws UnexpectedValueException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws LogicalException
     */
    public function testLexer(): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/../Fixtures/test-data.json');
        $lexer = new Lexer();
        $lexer->setInput($jsonContent);
        /** @var Token[] $tokes */
        $tokens = [];
        do {
            $tokens[] = $lexer->getNext();
        } while (TokenType::TOKEN_EOF !== end($tokens)->getType());

        self::assertCount(432, $tokens);
        self::assertEquals($this->expectedTokens(), $tokens);
    }

    /**
     * @return Token[]
     * @throws UnexpectedValueException
     * @throws UnexpectedValueException
     */
    private function expectedTokens(): array
    {
        $contents = file_get_contents(__DIR__ . '/../Fixtures/expected-tokens.ser');
        if ($contents === false) {
            throw new UnexpectedValueException('Could not read expected tokens from file');
        }

        return unserialize($contents, ['allowed_classes' => [Token::class]]);
    }
}
