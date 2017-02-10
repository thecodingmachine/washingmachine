<?php


namespace TheCodingMachine\WashingMachine\Clover\Analysis;

/**
 * Represents a method analyzed by clover.
 */
class Method
{
    // visibility="public" complexity="2" crap="2.03" count="1"
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var string
     */
    private $visibility;
    /**
     * @var float
     */
    private $complexity;
    /**
     * @var float
     */
    private $crap;
    /**
     * @var int
     */
    private $count;
    /**
     * @var string
     */
    private $file;
    /**
     * @var int
     */
    private $line;

    public function __construct(string $methodName, string $className, string $namespace, string $visibility, float $complexity, float $crap, int $count, string $file, int $line)
    {

        $this->methodName = $methodName;
        $this->className = $className;
        $this->namespace = $namespace;
        $this->visibility = $visibility;
        $this->complexity = $complexity;
        $this->crap = $crap;
        $this->count = $count;
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @return float
     */
    public function getComplexity(): float
    {
        return $this->complexity;
    }

    /**
     * @return float
     */
    public function getCrap(): float
    {
        return $this->crap;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    public function getFullName() : string
    {
        return $this->namespace.'\\'.$this->className.'::'.$this->methodName;
    }

    public function getShortName() : string
    {
        return $this->className.'::'.$this->methodName;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getLine(): string
    {
        return $this->line;
    }
}
