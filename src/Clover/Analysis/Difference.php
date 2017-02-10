<?php


namespace TheCodingMachine\WashingMachine\Clover\Analysis;

/**
 * Represents a difference between 2 methods.
 */
class Difference
{

    /**
     * @var Method
     */
    private $newMethod;
    /**
     * @var Method
     */
    private $oldMethod;

    public function __construct(Method $newMethod, Method $oldMethod = null)
    {
        $this->newMethod = $newMethod;
        $this->oldMethod = $oldMethod;
    }

    public function getMethodFullName() : string
    {
        return $this->newMethod->getFullName();
    }

    public function getMethodShortName() : string
    {
        return $this->newMethod->getShortName();
    }

    public function getCrapScore() : float
    {
        return $this->newMethod->getCrap();
    }

    public function isNew() : bool
    {
        return $this->oldMethod === null;
    }

    public function getCrapDifference(): float
    {
        return $this->newMethod->getCrap() - $this->oldMethod->getCrap();
    }

    public function getFile() : string
    {
        return $this->newMethod->getFile();
    }

    public function getLine() : int
    {
        return $this->newMethod->getLine();
    }
}
