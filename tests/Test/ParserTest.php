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

use Jojo1981\JsonAstBuilder\Ast\ArrayNode;
use Jojo1981\JsonAstBuilder\Ast\BooleanNode;
use Jojo1981\JsonAstBuilder\Ast\ElementNode;
use Jojo1981\JsonAstBuilder\Ast\IntegerNode;
use Jojo1981\JsonAstBuilder\Ast\JsonNode;
use Jojo1981\JsonAstBuilder\Ast\KeyNode;
use Jojo1981\JsonAstBuilder\Ast\MemberNode;
use Jojo1981\JsonAstBuilder\Ast\NodeWithTokenInterface;
use Jojo1981\JsonAstBuilder\Ast\NullNode;
use Jojo1981\JsonAstBuilder\Ast\NumberNode;
use Jojo1981\JsonAstBuilder\Ast\ObjectNode;
use Jojo1981\JsonAstBuilder\Ast\StringNode;
use Jojo1981\JsonAstBuilder\Ast\TypeNodeInterface;
use Jojo1981\JsonAstBuilder\Ast\ValueNode;
use Jojo1981\JsonAstBuilder\Exception\ParseException;
use Jojo1981\JsonAstBuilder\Lexer;
use Jojo1981\JsonAstBuilder\Lexer\Token;
use Jojo1981\JsonAstBuilder\Parser;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use UnexpectedValueException;
use function file_get_contents;

/**
 * @package tests\Jojo1981\JsonAstBuilder\Test
 */
final class ParserTest extends TestCase
{
    /**
     * @return void
     * @throws UnexpectedValueException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws ParseException
     */
    public function testParser(): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/../Fixtures/test-data.json');
        $parser = new Parser(new Lexer());
        $parser->setInput($jsonContent);
        self::assertEquals(self::getExpectedAst(), $parser->parse());
    }

    /**
     * @return JsonNode
     * @throws UnexpectedValueException
     * @throws UnexpectedValueException
     */
    private static function getExpectedAst(): JsonNode
    {
        $contents = file_get_contents(__DIR__ . '/../Fixtures/expected-ast.ser');
        if ($contents === false) {
            throw new UnexpectedValueException('Could not read expected ast from file');
        }

        return unserialize(
            $contents,
            [
                'allowed_classes' => [
                    JsonNode::class,
                    ObjectNode::class,
                    ArrayNode::class,
                    MemberNode::class,
                    KeyNode::class,
                    ElementNode::class,
                    ValueNode::class,
                    TypeNodeInterface::class,
                    StringNode::class,
                    IntegerNode::class,
                    NumberNode::class,
                    BooleanNode::class,
                    NullNode::class,
                    NodeWithTokenInterface::class,
                    Token::class
                ]
            ]
        );
    }
}
