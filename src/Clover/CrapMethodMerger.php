<?php


namespace TheCodingMachine\WashingMachine\Clover;
use TheCodingMachine\WashingMachine\Clover\Analysis\Method;

/**
 * Merges crap methods from Clover and Crap4J
 */
class CrapMethodMerger implements CrapMethodFetcherInterface
{
    /**
     * @var CrapMethodFetcherInterface
     */
    private $file1;
    /**
     * @var CrapMethodFetcherInterface
     */
    private $file2;

    /**
     * Merges methods from file 2 into methods from file 1
     *
     * @param CrapMethodFetcherInterface $file1
     * @param CrapMethodFetcherInterface $file2
     */
    public function __construct(CrapMethodFetcherInterface $file1, CrapMethodFetcherInterface $file2)
    {

        $this->file1 = $file1;
        $this->file2 = $file2;
    }

    /**
     * Returns an array of method objects, indexed by method full name.
     *
     * @return Method[]
     */
    public function getMethods(): array
    {
        $methods = $this->file1->getMethods();
        $toMergeMethods = $this->file2->getMethods();

        foreach ($toMergeMethods as $name => $toMergeMethod) {
            $methods[$name] = isset($methods[$name]) ? $methods[$name]->merge($toMergeMethod) : $toMergeMethod;
        }

        return $methods;
    }
}
