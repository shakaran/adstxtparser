<?php
require __DIR__ . '/../AdsTxtParser.php';

use PHPUnit\Framework\TestCase;
use AdsTxtParser\Parser;
use AdsTxtParser\Exception\AdsFileNotFound;

class ParserTest extends TestCase
{
    public function testExternalFile()
    {
        try
        {
            // By default localhost
            $parser = new Parser();
            $parser->readExternalFile();

            $this->assertFalse();
        }
        catch(AdsFileNotFound $e)
        {
            $this->assertEquals($e->getMessage(), 'Error getting ads.txt file for the domain');
        }
    }
}