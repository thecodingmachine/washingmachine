<?php
declare(strict_types=1);

namespace TheCodingMachine\WashingMachine\Clover;

use TheCodingMachine\WashingMachine\Clover\Analysis\Method;

final class Crap4JFile implements CrapMethodFetcherInterface
{

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var \SimpleXMLElement
     */
    private $root;

    private function __construct()
    {
    }

    public static function fromFile(string $fileName) : Crap4JFile
    {
        if (!file_exists($fileName)) {
            throw new \RuntimeException('Could not find file "'.$fileName.'". The unit tests did not run or broke before the end, or the file path is incorrect.');
        }

        $crap4JFile = new self();
        $crap4JFile->fileName = $fileName;
        $errorReporting = error_reporting();
        $oldErrorReporting = error_reporting($errorReporting & ~E_WARNING);
        $crap4JFile->root = simplexml_load_file($fileName);
        error_reporting($oldErrorReporting);
        if ($crap4JFile->root === false) {
            throw new \RuntimeException('Invalid XML file passed or unable to load file: "'.$fileName.'": '.error_get_last()['message']);
        }
        return $crap4JFile;
    }

    public static function fromString(string $string) : Crap4JFile
    {
        $cloverFile = new self();
        $errorReporting = error_reporting();
        $oldErrorReporting = error_reporting($errorReporting & ~E_WARNING);
        $cloverFile->root = simplexml_load_string($string);
        error_reporting($oldErrorReporting);
        if ($cloverFile->root === false) {
            throw new \RuntimeException('Invalid XML file passed or unable to load string: '.error_get_last()['message']);
        }
        return $cloverFile;
    }

    /**
     * Returns an array of method objects, indexed by method full name.
     *
     * @return Method[]
     */
    public function getMethods() : array
    {
        $methods = [];
        $methodsElement = $this->root->xpath('/crap_result/methods/method');

        foreach ($methodsElement as $methodElement) {
            $package = (string) $methodElement->package;
            $className = (string) $methodElement->className;
            $methodName = (string) $methodElement->methodName;
            $crap = (int) $methodElement->crap;
            $complexity = (int) $methodElement->complexity;

            $method = new Method($methodName, $className, $package, $complexity, $crap);
            $methods[$method->getFullName()] = $method;
        }

        return $methods;
    }
}
