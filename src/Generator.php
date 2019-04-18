<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JsonAstBuilder;

use Jojo1981\JsonAstBuilder\Ast\JsonNode;
use Jojo1981\JsonAstBuilder\Visitor\AnalyzerVisitor;
use Jojo1981\JsonAstBuilder\Visitor\DataGeneratorVisitor;
use Jojo1981\JsonAstBuilder\Visitor\PlantUmlAstNodesGeneratorVisitor;
use Jojo1981\JsonAstBuilder\Visitor\JsonStringGeneratorVisitor;
use Jojo1981\JsonAstBuilder\Visitor\PlantUmlDataGeneratorVisitor;

/**
 * @package Jojo1981\JsonAstBuilder
 */
class Generator
{
    /**
     * @param JsonNode $jsonNode
     * @param array $options
     * @return string
     */
    public function generateJsonString(JsonNode $jsonNode, array $options = []): string
    {
        $visitor = new JsonStringGeneratorVisitor($options);
        $jsonNode->accept($visitor);

        return $visitor->getResult();
    }

    /**
     * @param JsonNode $jsonNode
     * @param array $options
     * @return mixed
     */
    public function generateData(JsonNode $jsonNode, array $options = [])
    {
        $visitor = new DataGeneratorVisitor($options);

        return $jsonNode->accept($visitor);
    }

    /**
     * @param JsonNode $jsonNode
     * @return string
     */
    public function generatePlantUmlAstNodes(JsonNode $jsonNode): string
    {
        $visitor = new PlantUmlAstNodesGeneratorVisitor();
        $jsonNode->accept($visitor);

        return $visitor->getResult();
    }

    /**
     * @param JsonNode $jsonNode
     * @return string
     */
    public function generatePlantUmlData(JsonNode $jsonNode): string
    {
        $visitor = new PlantUmlDataGeneratorVisitor();
        $jsonNode->accept($visitor);

        return $visitor->getResult();
    }

    /**
     * @param JsonNode $jsonNode
     * @return array
     */
    public function getStatistics(JsonNode $jsonNode): array
    {
        $visitor = new AnalyzerVisitor();

        return $jsonNode->accept($visitor);
    }
}
