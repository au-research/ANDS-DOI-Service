<?php


use ANDS\DOI\SchemaProvider;

class SchemaProviderTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_cache_file()
    {
        $schema = SchemaProvider::getSchema("/kernel-3/metadata.xsd");
        $this->assertNotNull($schema);
        $this->assertTrue(file_exists($schema));
        $this->assertFalse(true);
    }
}