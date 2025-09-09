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

use UnexpectedValueException;
use function array_filter;
use function array_map;
use function array_merge;
use function count;
use function max;
use function min;
use function preg_match_all;
use function sprintf;
use function str_replace;
use function strlen;
use function substr;

/**
 * @package Jojo1981\JsonAstBuilder\Lexer
 */
final class Scanner
{
    /** @var string */
    private string $input;

    /** @var int */
    private int $length;

    /** @var bool */
    private bool $empty;

    /** @var int */
    private int $lastIndex;

    /** @var int[] */
    private array $positionStops;

    /** @var int */
    private int $position = 0;

    /** @var int */
    private int $lineNumber = 0;

    /** @var int */
    private int $linePosition = 0;

    /**
     * @param string $input
     */
    public function __construct(string $input)
    {
        $this->input = $this->prepareInput($input);
        $this->length = strlen($this->input);
        $this->empty = 0 === $this->length;
        $this->lastIndex = $this->length - 1;
        $this->positionStops = $this->extractPositionStopsFromInput($this->input, $this->lastIndex);
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
        $otherLineSeparators = array_filter(
            ["\r\n", "\n\r", "\r", "\n"],
            static function (string $lineSeparators): bool {
                return PHP_EOL !== $lineSeparators;
            }
        );

        return str_replace($otherLineSeparators, PHP_EOL, $input);
    }

    /**
     * Extract all new line positions from the input. It will return an indexed array whose values are the found newline
     * positions (zero based) in the input. The first position will be the start and the last position will be the end.
     *
     * @param string $input
     * @param int $lastIndex
     * @return int[]
     */
    private function extractPositionStopsFromInput(string $input, int $lastIndex): array
    {
        $result = [];
        preg_match_all('/\n/', $input, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches[0])) {
            $result = array_merge(
                $result,
                array_map(
                    static function (array $match): int {
                        return $match[1];
                    },
                    $matches[0]
                )
            );
        }

        return array_merge([0], $result, [$lastIndex]);
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
     * @return string|null
     * @throws UnexpectedValueException
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
     * @param int $integer
     * @return void
     * @throws UnexpectedValueException
     */
    private function assertPositiveInteger(int $integer): void
    {
        if ($integer < 1) {
            throw new UnexpectedValueException(sprintf('Expected a positive integer, but got: %d', $integer));
        }
    }

    /**
     * @param int $length
     * @return string|null
     * @throws UnexpectedValueException
     */
    public function look(int $length = 1): ?string
    {
        $this->assertPositiveInteger($length);
        if ($this->hasEndReached() || $this->isEmpty()) {
            return null;
        }

        return substr($this->input, $this->position, $length);
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
        $newPosition = max($newPosition, 0);
        $newPosition = (min($newPosition, $this->length));

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
        $position = min($position, $this->lastIndex);

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
     * @return bool
     */
    public function isAtBegin(): bool
    {
        return 0 === $this->position;
    }
}
