<?php


namespace TheCodingMachine\WashingMachine\Clover\Analysis;


class MethodTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $method1 = new Method('toto', 'Foo', '\\', 42, 42, 'public', 42);
        $method2 = new Method('toto', 'Foo', '\\', 42, 42, null, null, 'file', 42);
        $mergedMethod = $method1->merge($method2);

        $this->assertSame('public', $mergedMethod->getVisibility());
        $this->assertSame(42, $mergedMethod->getCount());
        $this->assertSame('file', $mergedMethod->getFile());
        $this->assertSame(42, $mergedMethod->getLine());
    }
}
