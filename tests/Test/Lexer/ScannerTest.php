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

namespace tests\Jojo1981\JsonAstBuilder\Test\Lexer;

use Jojo1981\JsonAstBuilder\Lexer\Scanner;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use UnexpectedValueException;

/**
 * @package tests\Jojo1981\JsonAstBuilder\Test\Lexer;
 */
class ScannerTest extends TestCase
{
    /**
     * @test
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws ExpectationFailedException
     * @return void
     */
    public function scannerWithEmptyInput(): void
    {
        $scanner = new Scanner('');
        $this->assertTrue($scanner->isEmpty());
        $this->assertTrue($scanner->isAtBegin());
        $this->assertTrue($scanner->hasEndReached());
        $this->assertEquals(0, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());
        $this->assertNull($scanner->look());
        $this->assertEquals(0, $scanner->getPosition());
        $this->assertNull($scanner->read());
        $this->assertNull($scanner->read(5));
        $this->assertEquals(0, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());
        $this->assertTrue($scanner->isEmpty());
        $this->assertTrue($scanner->isAtBegin());
        $this->assertTrue($scanner->hasEndReached());
    }

    /**
     * @test
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws ExpectationFailedException
     * @return void
     */
    public function scannerWithSingleLine(): void
    {
        $scanner = new Scanner('This is a single line');
        $this->assertFalse($scanner->isEmpty());
        $this->assertTrue($scanner->isAtBegin());
        $this->assertFalse($scanner->hasEndReached());
        $this->assertEquals(0, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());
        $this->assertEquals('T', $scanner->look());
        $this->assertEquals(0, $scanner->getPosition());

        $this->assertEquals('T', $scanner->read());
        $this->assertEquals('h', $scanner->look());
        $this->assertFalse($scanner->isEmpty());
        $this->assertFalse($scanner->isAtBegin());
        $this->assertFalse($scanner->hasEndReached());
        $this->assertEquals(1, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(1, $scanner->getLinePosition());

        $this->assertEquals('his i', $scanner->read(5));
        $this->assertEquals('s a', $scanner->look(3));
        $this->assertFalse($scanner->isEmpty());
        $this->assertFalse($scanner->isAtBegin());
        $this->assertFalse($scanner->hasEndReached());
        $this->assertEquals(6, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(6, $scanner->getLinePosition());
    }

    /**
     * @test
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws ExpectationFailedException
     * @return void
     */
    public function scannerWithMultiLine(): void
    {
        $scanner = new Scanner("This is line 1\nThis is line 2\n\nThis is line 4");
        $this->assertEquals(45, $scanner->getLength());
        $this->assertFalse($scanner->isEmpty());
        $this->assertTrue($scanner->isAtBegin());
        $this->assertFalse($scanner->hasEndReached());
        $this->assertEquals(0, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());
        $this->assertEquals('T', $scanner->look());
        $this->assertEquals(0, $scanner->getPosition());

        $this->assertEquals('T', $scanner->read());
        $this->assertEquals(1, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(1, $scanner->getLinePosition());

        $this->assertEquals('his is line', $scanner->read(11));
        $this->assertEquals(12, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(12, $scanner->getLinePosition());

        $this->assertEquals(" 1\n", $scanner->read(3));
        $this->assertEquals(15, $scanner->getPosition());
        $this->assertEquals(1, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());

        $this->assertEquals("This is line 2\n", $scanner->read(15));
        $this->assertEquals(30, $scanner->getPosition());
        $this->assertEquals(2, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());

        $this->assertEquals("\n", $scanner->read());
        $this->assertEquals(31, $scanner->getPosition());
        $this->assertEquals(3, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());

        $this->assertFalse($scanner->isAtBegin());
        $this->assertFalse($scanner->hasEndReached());

        $this->assertEquals('This is line 4', $scanner->read(50));
        $this->assertEquals(45, $scanner->getPosition());
        $this->assertEquals(3, $scanner->getLineNumber());
        $this->assertEquals(13, $scanner->getLinePosition());

        $this->assertFalse($scanner->isAtBegin());
        $this->assertTrue($scanner->hasEndReached());

        $scanner->rewind();
        $this->assertTrue($scanner->isAtBegin());
        $this->assertFalse($scanner->hasEndReached());
        $this->assertEquals(0, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(0, $scanner->getLinePosition());

        $this->assertEquals('T', $scanner->read());
        $this->assertEquals(1, $scanner->getPosition());
        $this->assertEquals(0, $scanner->getLineNumber());
        $this->assertEquals(1, $scanner->getLinePosition());
    }

    /**
     * @test
     * @throws UnexpectedValueException
     * @return void
     */
    public function positiveIntegerForLookAhead(): void
    {
        $exception = new UnexpectedValueException('Expected a positive integer, but got: -5');
        $this->expectExceptionObject($exception);

        $scanner = new Scanner('');
        $scanner->look(-5);
    }

    /**
     * @test
     *
     * @throws UnexpectedValueException
     * @return void
     */
    public function positiveIntegerForReadForward(): void
    {
        $exception = new UnexpectedValueException('Expected a positive integer, but got: -2');
        $this->expectExceptionObject($exception);

        $scanner = new Scanner('');
        $scanner->read(-2);
    }
}
