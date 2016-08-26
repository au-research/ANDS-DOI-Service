<?php

use ANDS\DOI\Validator\XMLValidator;

class XMLValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_return_xml_version()
    {
        $xml = file_get_contents(__DIR__."/.."."/sample.xml");
        $this->assertEquals(XMLValidator::getSchemaVersion($xml),"/kernel-3/metadata.xsd");
    }

    /** @test **/
    public function it_should_return_xml_schema_validation()
    {
        $xml = file_get_contents(__DIR__."/.."."/sample.xml");
        $this->assertTrue(XMLValidator::validateSchemaVersion($xml));
    }

    /** @test **/
    public function it_should_return_xml_schema_invalid()
    {
        $xml = file_get_contents(__DIR__."/.."."/sample_invalid.xml");
        $invalid = XMLValidator::validateSchemaVersion($xml);
        $this->assertFalse($invalid);
    }

}