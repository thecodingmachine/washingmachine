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

    public function __construct(string $methodName, string $className, string $namespace, float $complexity, float $crap, string $visibility = null, int $count = null, string $file = null, int $line = null)
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
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Merges the method passed in parameter in this method.
     * Any non null property will override this object property.
     * Return a NEW object.
     *
     * @param Method $method
     */
    public function merge(Method $method): Method
    {
        $clone = clone $this;
        $clone->visibility = $method->visibility ?? $this->visibility;
        $clone->complexity = $method->complexity ?? $this->complexity;
        $clone->crap = $method->crap ?? $this->crap;
        $clone->count = $method->count ?? $this->count;
        $clone->file = $method->file ?? $this->file;
        $clone->line = $method->line ?? $this->line;
        return $clone;
    }
}
