<?php


namespace TheCodingMachine\WashingMachine\Clover;


class CloverFileTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCoveragePercentage()
    {
        $cloverFile = CloverFile::fromFile(__DIR__.'/../Fixtures/clover.xml', '/home/david/projects/washing-machine');
        $result = $cloverFile->getCoveragePercentage();

        $this->assertSame(0.81818181818181823, $result);
    }

    public function testBadFile()
    {
        $this->expectException(\RuntimeException::class);
        CloverFile::fromFile(__DIR__.'/../Fixtures/broken.xml', '/');
    }

    public function testNotExistFile()
    {
        $this->expectException(\RuntimeException::class);
        CloverFile::fromFile(__DIR__.'/../Fixtures/notexist.xml', '/');
    }

    public function testGetMethods()
    {
        $cloverFile = CloverFile::fromFile(__DIR__.'/../Fixtures/clover.xml', '/home/david/projects/washing-machine');

        $methods = $cloverFile->getMethods();

        $this->assertCount(2, $methods);
        $construct = $methods['TheCodingMachine\\WashingMachine\\Clover\\CloverFile::__construct'];
        $this->assertSame('__construct', $construct->getMethodName());
        $this->assertSame('src/Clover/CloverFile.php', $construct->getFile());
    }
}
