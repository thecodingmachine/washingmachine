<?php


namespace TheCodingMachine\WashingMachine\Gitlab;


class EmojiGeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testGetEmoji()
    {
        $generator = EmojiGenerator::createCrapScoreEmojiGenerator();

        $this->assertSame('', $generator->getEmoji(0));
        $this->assertSame(':innocent:', $generator->getEmoji(1));
        $this->assertSame(':slight_frown:', $generator->getEmoji(80));
        $this->assertSame(':skull_crossbones:', $generator->getEmoji(1000));
    }
}
