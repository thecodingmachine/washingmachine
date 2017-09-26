<?php


namespace TheCodingMachine\WashingMachine\Clover;


class DiffServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMeaningfulDifferences()
    {
        $service = new DiffService(1, 10, 10);

        $cloverFile = CloverFile::fromFile(__DIR__.'/../Fixtures/clover.xml', '/home/david/projects/washing-machine');
        $oldCloverFile = CloverFile::fromFile(__DIR__.'/../Fixtures/oldClover.xml', '/home/david/projects/washing-machine');

        $differences = $service->getMeaningfulDifferences($cloverFile, $oldCloverFile);

        $this->assertCount(1, $differences);

        $difference = $differences[0];
        $this->assertSame($difference->getCrapScore(), 2.03);
        $this->assertSame($difference->getCrapDifference(), -40.27);
    }
}
