<?php
declare(strict_types=1);

namespace TheCodingMachine\WashingMachine\Clover;

use TheCodingMachine\WashingMachine\Clover\Analysis\Method;

final class EmptyCloverFile implements CloverFileInterface
{
    private function __construct()
    {
    }

    public static function create() : EmptyCloverFile
    {
        return new self();
    }

    /**
     * @return float
     */
    public function getCoveragePercentage() : float
    {
        return 0.0;
    }

    /**
     * Returns an array of method objects, indexed by method full name.
     *
     * @return Method[]
     */
    public function getMethods() : array
    {
        return [];
    }
}
