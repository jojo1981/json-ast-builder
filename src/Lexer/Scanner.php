<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JsonAstBuilder\Lexer;

/**
 * @package Jojo1981\JsonAstBuilder\Lexer
 */
class Scanner
{
    /** @var string */
    private $input;

    /** @var int */
    private $length;

    /** @var bool */
    private $empty;

    /** @var int */
    private $lastIndex;

    /** @var int[] */
    private $positionStops;

    /** @var int */
    private $position = 0;

    /** @var int */
    private $lineNumber = 0;

    /** @var int */
    private $linePosition = 0;

    /**
     * @param string $input
     */
    public function __construct(string $input)
    {
        $this->input = $this->prepareInput($input);
        $this->length = \strlen($this->input);
        $this->empty = 0 === $this->length;
        $this->lastIndex = $this->length - 1;
        $this->positionStops = $this->extractPositionStopsFromInput($this->input, $this->lastIndex);
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $length
     * @throws \UnexpectedValueException
     * @return null|string
     */
    public function look(int $length = 1): ?string
    {
        $this->assertPositiveInteger($length);
        if ($this->hasEndReached() || $this->isEmpty()) {
            return null;
        }

        return \substr($this->input, $this->position, $length);
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
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->lineNumber = 0;
        $this->linePosition = 0;
    }

    /**
     * @param int $length
     * @throws \UnexpectedValueException
     * @return null|string
     */
    public function read(int $length = 1): ?string
    {
        $this->assertPositiveInteger($length);
        $buffer = $this->look($length);
        if (null !== $buffer) {
            $this->movePosition($length);
        }

        return $buffer;
    }

    /**
     * @return bool
     */
    public function hasEndReached(): bool
    {
        return $this->position > $this->lastIndex;
    }

    /**
     * @return bool
     */
    public function isAtBegin(): bool
    {
        return 0 === $this->position;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->empty;
    }

    /**
     * @param int $length
     * @return void
     */
    private function movePosition(int $length): void
    {
        $newPosition = $this->position + $length;
        $newPosition = $newPosition < 0 ? 0 : $newPosition;
        $newPosition = ($newPosition > $this->length ? $this->length : $newPosition);

        [$lineNumber, $linePosition] = $this->getLineNumberAndLinePositionForPosition($newPosition);

        $this->lineNumber = $lineNumber;
        $this->linePosition = $linePosition;
        $this->position = $newPosition;
    }

    /**
     * Return a tuple of [lineNumber, linePosition]
     *
     * @param int $position
     * @return int[]
     */
    private function getLineNumberAndLinePositionForPosition(int $position): array
    {
        $result = [0, 0];
        $position = $position > $this->lastIndex ? $this->lastIndex : $position;

        if ($position > 0) {
            foreach ($this->positionStops as $lineNumberZeroBased => $linePositionZeroBased) {
                if ($position <= $linePositionZeroBased) {
                    $result[0] = $lineNumberZeroBased - 1;
                    $previousNewLinePosition = $lineNumberZeroBased - 1 > 0
                        ? $this->positionStops[$lineNumberZeroBased - 1]
                        : 0;
                    $result[1] = $position - ($previousNewLinePosition > 0 ? $previousNewLinePosition + 1 : $previousNewLinePosition);
                    break;
                }
            }
        }

        return $result;
    }


    /**
     * Prepare input and convert all new line characters like carriage returns and line feed characters into the current
     * system used end of line character.
     *
     * @param string $input
     * @return string
     */
    private function prepareInput(string $input): string
    {
        $tmpReplacement = \uniqid('NEWLINE_', true);
        $search = ["\r\n", "\n\r", "\r", "\n", $tmpReplacement];
        $replace = \array_merge(\array_fill(0, 4, $tmpReplacement), [PHP_EOL]);

        return \str_replace($search, $replace, $input);
    }

    /**
     * Extract all new line positions from the input. It will return an indexed array who's values are the found newline
     * positions (zero based) in the input. The first position will be the start and the last position will be the end.
     *
     * @param string $input
     * @param int $lastIndex
     * @return int[]
     */
    private function extractPositionStopsFromInput(string $input, int $lastIndex): array
    {
        $result = [];
        \preg_match_all('/\n/', $input, $matches, PREG_OFFSET_CAPTURE);
        if (\count($matches[0])) {
            $result = \array_merge(
                $result,
                \array_map(
                    static function (array $match): int {
                        return $match[1];
                    },
                    $matches[0]
                )
            );
        }

        return \array_merge([0], $result, [$lastIndex]);
    }

    /**
     * @param int $integer
     * @throws \UnexpectedValueException
     * @return void
     */
    private function assertPositiveInteger(int $integer): void
    {
        if ($integer < 1) {
            throw new \UnexpectedValueException(\sprintf('Expected a positive integer, but got: %d', $integer));
        }
    }
}