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

use function sprintf;

/**
 * @package Jojo1981\JsonAstBuilder\Lexer
 */
final class Token
{
    /** @var int */
    private int $type;

    /** @var string */
    private string $name;

    /** @var int */
    private int $position;

    /** @var int */
    private int $lineNumber;

    /** @var int */
    private int $linePosition;

    /** @var string|null */
    private ?string $lexeme;

    /**
     * @param int $type
     * @param string $name
     * @param int $position
     * @param int $lineNumber
     * @param int $linePosition
     * @param string|null $lexeme
     */
    public function __construct(
        int $type,
        string $name,
        int $position,
        int $lineNumber,
        int $linePosition,
        ?string $lexeme = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->position = $position;
        $this->lineNumber = $lineNumber;
        $this->linePosition = $linePosition;
        $this->lexeme = $lexeme;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    /**
     * @return int
     */
    public function getLinePosition(): int
    {
        return $this->linePosition;
    }

    /**
     * @return string|null
     */
    public function getLexeme(): ?string
    {
        return $this->lexeme;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '<%s[%d]> = \'%s\', [%d] [%d:%d]',
            $this->name,
            $this->type,
            $this->lexeme,
            $this->position,
            $this->lineNumber,
            $this->linePosition
        );
    }
}
