<?php
namespace TheCodingMachine\WashingMachine\Clover;

use TheCodingMachine\WashingMachine\Clover\Analysis\Method;

interface CloverFileInterface
{
    /**
     * @return float
     */
    public function getCoveragePercentage(): float;

    /**
     * Returns an array of method objects, indexed by method full name.
     *
     * @return Method[]
     */
    public function getMethods(): array;
}