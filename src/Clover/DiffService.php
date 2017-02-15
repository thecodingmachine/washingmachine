<?php


namespace TheCodingMachine\WashingMachine\Clover;

use TheCodingMachine\WashingMachine\Clover\Analysis\Difference;

/**
 * Service in charge of analyzing the differences between 2 clover files.
 */
class DiffService
{
    /**
     * @var float
     */
    private $meaningfulCrapChange;
    /**
     * @var int
     */
    private $maxReturnedMethods;

    public function __construct(float $meaningfulCrapChange, int $maxReturnedMethods)
    {

        $this->meaningfulCrapChange = $meaningfulCrapChange;
        $this->maxReturnedMethods = $maxReturnedMethods;
    }

    /**
     * @param CloverFileInterface $newCloverFile
     * @param CloverFileInterface $oldCloverFile
     * @return Difference[]
     */
    public function getMeaningfulDifferences(CloverFileInterface $newCloverFile, CloverFileInterface $oldCloverFile)
    {
        $newMethods = $newCloverFile->getMethods();
        $oldMethods = $oldCloverFile->getMethods();

        // Let's keep only methods that are in both files:
        $inCommonMethods = array_intersect(array_keys($newMethods), array_keys($oldMethods));

        // New methods in the new file:
        $createdMethods = array_diff(array_keys($newMethods), $inCommonMethods);

        $differences = [];

        foreach ($inCommonMethods as $methodName) {
            $change = abs($newMethods[$methodName]->getCrap() - $oldMethods[$methodName]->getCrap());
            if ($change > $this->meaningfulCrapChange) {
                $differences[] = new Difference($newMethods[$methodName], $oldMethods[$methodName]);
            }
        }

        foreach ($createdMethods as $methodName) {
            $method = $newMethods[$methodName];
            if ($method->getCrap() > $this->meaningfulCrapChange) {
                $differences[] = new Difference($method, null);
            }
        }

        // Now, let's order the differences by crap order.
        usort($differences, function(Difference $d1, Difference $d2) {
           return $d2->getCrapScore() <=> $d1->getCrapScore();
        });

        // Now, let's limit the number of returned differences
        $differences = array_slice(array_values($differences), 0, $this->maxReturnedMethods);

        return $differences;
    }
}
