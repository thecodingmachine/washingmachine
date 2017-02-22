<?php
namespace TheCodingMachine\WashingMachine\Clover;

interface CoverageDetectorInterface
{
    /**
     * @return float
     */
    public function getCoveragePercentage(): float;
}