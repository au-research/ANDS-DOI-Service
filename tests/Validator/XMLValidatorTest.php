<?php

use ANDS\DOI\Validator\XMLValidator;
use Dotenv\Dotenv;

class XMLValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_return_xml_version()
    {
        $xml = file_get_contents(__DIR__."/../assets/sample.xml");
        $this->assertEquals(XMLValidator::getSchemaVersion($xml),"/kernel-3/metadata.xsd");
    }

    /** @test **/
    public function it_should_return_xml_schema_validation()
    {
        $xml = file_get_contents(__DIR__."/../assets/sample.xml");
        $this->assertTrue(XMLValidator::create()->validateSchemaVersion($xml));
    }

    /** @test **/
    public function it_should_return_xml_schema_invalid()
    {
        $xml = file_get_contents(__DIR__."/../assets/sample_invalid.xml");
        $invalid = XMLValidator::create()->validateSchemaVersion($xml);
        $this->assertFalse($invalid);
    }

    /** @test **/
    public function it_should_validate_schema_version_4()
    {
        $xml = file_get_contents(__DIR__.'/../assets/datacite-example-fundingReference-v.4.0.xml');
        $this->assertTrue(XMLValidator::create()->validateSchemaVersion($xml));
    }

    public function setUp()
    {
        $dotenv = new Dotenv('./');
        $dotenv->load();
    }

}