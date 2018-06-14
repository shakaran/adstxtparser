<?php
require __DIR__ . '/../AdsTxtParser.php';

use PHPUnit\Framework\TestCase;
use AdsTxtParser\Parser;
use AdsTxtParser\Exception\AdsFileNotFound;

class ParserTest extends TestCase
{
    public function testCreateInstance()
    {
        $parser = new Parser;
        $this->assertTrue($parser instanceof Parser);
    }

    public function testExternalFile()
    {
        try
        {
            // By default localhost
            $parser = new Parser();
            $parser->readExternalFile();

            $this->assertFalse(TRUE);
        }
        catch(AdsFileNotFound $e)
        {
            $this->setExpectedException('AdsFileNotFound');
            $this->assertEquals($e->getMessage(), 'Error getting ads.txt file for the domain');
        }
    }

    public function testNewspaperExternalFile()
    {
        try
        {
            // By default localhost
            $parser = new Parser();
            $parser->readExternalFile('http://estaticos.elmundo.es');

            // More examples to test:
            // https://elpais.com/ads.txt
            // https://www.elconfidencial.com/ads.txt

            $this->assertTrue(TRUE);
        }
        catch(AdsFileNotFound $e)
        {
            $this->setExpectedException('AdsFileNotFound');
            $this->assertFalse(TRUE);
        }

        $comments = $parser->getComments();
        // var_dump($comments);
        $this->assertTrue(is_array($comments));

        $errors = $parser->getErrors();
        //var_dump($errors);
        $this->assertTrue(is_array($errors));

        $warnings = $parser->getWarnings();
        // var_dump($warnings);
        $this->assertTrue(is_array($warnings));

        $fields = $parser->getFields();
        // var_dump($fields);
        $this->assertTrue(is_array($fields));

        $variables = $parser->getVariables();
        //var_dump($variables);
        $this->assertTrue(is_array($variables));

        $resellers = $parser->getResellers();
        //var_dump(count($resellers));
        $this->assertTrue(is_array($resellers));

        $directs = $parser->getDirects();
        //var_dump(count($directs));
        $this->assertTrue(is_array($directs));
    }
}