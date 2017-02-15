<?php


namespace TheCodingMachine\WashingMachine\Clover;


class EmptyCloverFileTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $empty = EmptyCloverFile::create();
        $this->assertSame(0.0, $empty->getCoveragePercentage());
        $this->assertSame([], $empty->getMethods());
    }
}
