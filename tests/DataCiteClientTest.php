<?php

use ANDS\DOI\DataCiteClient;
use ANDS\DOI\Validator\XMLValidator;

class DataCiteClientTest extends PHPUnit_Framework_TestCase
{

    /** @test */
    public function test_construct_object()
    {
        $client = $this->getClient();
        $this->assertEquals(
            $client->getDataciteUrl(), getenv('DATACITE_URL')
        );
    }

    /** @test */
    public function it_should_return_a_doi()
    {
        $client = $this->getClient();
        $get = $client->get("10.5072/00/56610ec83d432");
        $this->assertEquals($get, "https://devl.ands.org.au/minh/");

        $this->assertFalse($client->hasError());
    }

    /** @test */
    public function it_should_return_good_xml_for_a_doi()
    {
        $client = $this->getClient();

        $actual = new DOMDocument;
        $metadata = $client->getMetadata("10.5072/00/56610ec83d432");


        $actual->loadXML($metadata);

        $this->assertFalse($client->hasError());
        $this->assertEquals("resource", $actual->firstChild->tagName);
    }

    /** @test */
    public function test_mint_a_new_doi()
    {
        $client = $this->getClient();
        $xml = file_get_contents(__DIR__ . "/assets/sample.xml");

        $doi = "10.5072/00/".uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $response = $client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml, false
        );
        $this->assertTrue($response);
    }

    /** @test **/
    public function it_should_mint_a_schema_version_4_doi()
    {
        $client = $this->getClient();
        $xml = file_get_contents(__DIR__ . "/assets/datacite-example-full-v4.0.xml");

        $doi = "10.5072/00/".uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $response = $client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml, false
        );
        $this->assertTrue($response);
    }

    /** @test */
    public function it_should_set_datacite_url()
    {
        $client = $this->getClient();
        $client->setDataciteUrl('https://mds.test.datacite.org/');
        $this->assertEquals(
            $client->getDataciteUrl(), 'https://mds.test.datacite.org/'
        );
    }

    /** @test */
    public function it_should_update_a_doi_with_new_xml()
    {
        //run update with new xml
        //get the new xml and make sure it's the same
        //put old xml back

        $client = $this->getClient();

        $metadata = $client->getMetadata("10.5072/00/56610ec83d432");

        $replace= file_get_contents(__DIR__."/assets/replace_sample.xml");

        $client->update($replace);

        $this->assertEquals($client->getMetadata("10.5072/00/56610ec83d432"),$replace);


        //revert back to old xml
        $client->update($metadata);


    }

    /** @test */
    public function it_should_update_a_doi_with_new_url()
    {
        //run update with new url
        //get the new url and make sure it's the same
        //put old url back

        $client = $this->getClient();

        $url =  $client->get("10.5072/00/56610ec83d432");

        $new_url= "https://devl.ands.org.au/minh_replaced/" ;

        $client->updateURL("10.5072/00/56610ec83d432",$new_url);

        $this->assertEquals($client->get("10.5072/00/56610ec83d432"), $new_url);

        //revert back to old url
        $client->updateURL("10.5072/00/56610ec83d432",$url);

    }

    /** @test */
    public function it_should_activate_a_doi_and_then_deactivate()
    {
        //make sure the DOI is activated
        //deactivate it
        //make sure it's deactivated
        //activate it
        //make sure it's activated in the status
    }

    /**
     * Not a test
     * A helper method to return a new client instance
     *
     * @return DataCiteClient
     */
    private function getClient()
    {
        $dotenv = new Dotenv\Dotenv(__DIR__.'/../');
        $dotenv->load();

        $username = getenv('DATACITE_USERNAME');
        $password = getenv('DATACITE_PASSWORD');

        $client = new DataCiteClient($username, $password);
        $client->setDataciteUrl(getenv('DATACITE_URL'));

        return $client;
    }

}