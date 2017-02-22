<?php


namespace TheCodingMachine\WashingMachine\Clover;


class Crap4JTest extends \PHPUnit_Framework_TestCase
{
    public function testBadFile()
    {
        $this->expectException(\RuntimeException::class);
        Crap4JFile::fromFile(__DIR__.'/../Fixtures/broken.xml', '/');
    }

    public function testNotExistFile()
    {
        $this->expectException(\RuntimeException::class);
        Crap4JFile::fromFile(__DIR__.'/../Fixtures/notexist.xml', '/');
    }

    public function testGetMethods()
    {
        $crap4JFile = Crap4JFile::fromFile(__DIR__.'/../Fixtures/crap4j.xml');

        $methods = $crap4JFile->getMethods();

        $this->assertCount(2, $methods);
        $construct = $methods['Test\\Controllers\\TestController::getProjectsForClient'];
        $this->assertSame('getProjectsForClient', $construct->getMethodName());
        $this->assertSame(12.0, $construct->getCrap());
    }
}
