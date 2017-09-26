<?php


namespace TheCodingMachine\WashingMachine\Gitlab;


use TheCodingMachine\WashingMachine\Clover\CloverFile;
use TheCodingMachine\WashingMachine\Clover\Crap4JFile;
use TheCodingMachine\WashingMachine\Clover\DiffService;
use TheCodingMachine\WashingMachine\Clover\EmptyCloverFile;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testNoMeaningfulDifferences()
    {
        $cloverFile = CloverFile::fromFile(__DIR__.'/../Fixtures/clover.xml', '/home/david/projects/washing-machine');

        $message = new Message();
        $message->addDifferencesHtml($cloverFile, $cloverFile, new DiffService(5, 5, 20), 42, 'http://gitlab', 'my_group/my_project');

        $this->assertSame("No meaningful differences in code complexity detected.\n", (string) $message);
    }

    public function testFromCrap4J()
    {
        $crap4JFile = Crap4JFile::fromFile(__DIR__.'/../Fixtures/crap4j.xml');

        $message = new Message();
        $message->addDifferencesHtml($crap4JFile, EmptyCloverFile::create(), new DiffService(0, 0, 20), 42, 'http://gitlab', 'my_group/my_project');

        $msg = (string) $message;

        $this->assertContains('TestController::getProjectsForClient', $msg);
        $this->assertNotContains('<a', $msg);
    }

    public function testAddSmallMessage()
    {
        $message = new Message();

        $message->addFile(new \SplFileInfo(__DIR__.'/../Fixtures/small.txt'), 'http://gitlab.example.com', 'foo/bar', 42);

        $str = (string) $message;

        $this->assertContains('small.txt', $str);
        $this->assertContains('smalltext', $str);
    }

    public function testAddLargeMessage()
    {
        $message = new Message();

        $message->addFile(new \SplFileInfo(__DIR__.'/../Fixtures/large.txt'), 'http://gitlab.example.com', 'foo/bar', 42);

        $str = (string) $message;

        $this->assertContains('large.txt', $str);
        $this->assertNotContains('largetext', $str);
        $this->assertContains('http://gitlab.example.com/', $str);
    }
}
