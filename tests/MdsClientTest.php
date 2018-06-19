<?php

use ANDS\DOI\MdsClient;
use ANDS\DOI\Validator\XMLValidator;

class MdsClientTest extends PHPUnit_Framework_TestCase
{

    private $testDoiId;

    /** @var MdsClient */
    private $client;

    /** @test */
    public function test_mint_a_new_doi()
    {
        $xml = file_get_contents(__DIR__ . "/assets/sample.xml");
        $doi = '10.5072/'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $response = $this->client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml, false
        );
//        dd($response);
        $this->assertTrue($response);
    }

    /** @test */
    public function test_construct_object()
    {
        $this->assertEquals(
            $this->client->getDataciteUrl(), getenv('DATACITE_URL')
        );
    }

//    /** @test */
//    public function it_should_return_a_doi()
//    {
//        $get = $this->client->get($this->testDoiId);
//        $this->assertEquals($get, "https://devl.ands.org.au/minh/");
//        $this->assertFalse($this->client->hasError());
//    }

    /** @test */
    public function it_should_return_good_xml_for_a_doi()
    {
        $actual = new DOMDocument;
        $metadata = $this->client->getMetadata($this->testDoiId);
        $actual->loadXML($metadata);
        $this->assertFalse($this->client->hasError());
        $this->assertEquals("resource", $actual->firstChild->tagName);
    }



    /** @test **/
    public function it_should_mint_a_schema_version_4_doi()
    {
        $xml = file_get_contents(__DIR__ . "/assets/datacite-example-full-v4.0.xml");
        $doi = '10.5072/'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $response = $this->client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml, false
        );
        $this->assertTrue($response);
    }

    /** @test */
    public function it_should_set_datacite_url()
    {
        $this->client->setDataciteUrl('https://mds.test.datacite.org/');
        $this->assertEquals(
            $this->client->getDataciteUrl(), 'https://mds.test.datacite.org/'
        );
    }

    /** @test */
    public function it_should_update_a_doi_with_new_xml()
    {
        //run update with new xml
        //get the new xml and make sure it's the same
        //put old xml back
        $metadata = $this->client->getMetadata($this->testDoiId);
        $replace= file_get_contents(__DIR__."/assets/replace_sample.xml");
        $this->client->update($replace);
        $this->assertEquals($this->client->getMetadata($this->testDoiId), $replace);
        //revert back to old xml
        $this->client->update($metadata);
    }
    

//    /** @test */
//    public function it_should_update_a_doi_with_new_url()
//    {
//        //run update with new url
//        //get the new url and make sure it's the same
//        //put old url back
//        $url =  $this->client->get($this->testDoiId);
//
//        $new_url= "https://devl.ands.org.au/minh_replaced/" ;
//
//        $this->client->updateURL($this->testDoiId,$new_url);
//
//        $this->assertEquals($this->client->get($this->testDoiId), $new_url);
//
//        //revert back to old url
//        $this->client->updateURL($this->testDoiId,$url);
//
//    }

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
     * A helper method to setup a new client instance
     */
    private function setUpClient()
    {
        $username = getenv('DATACITE_USERNAME');
        $password = getenv('DATACITE_PASSWORD');
        $this->client = new MdsClient($username, $password);
        $this->client->setDataciteUrl(getenv('DATACITE_URL'));
    }

    public function setUpTestDOI(){

        // set up the metadata
        if(!$this->isTestDOIExists()){
            $this->createTestDOI();
        }

        // setup the url (make sure)
        $this->client->clearResponse();
        $this->client->updateURL($this->testDoiId, "https://devl.ands.org.au/leo/");
    }

    public function isTestDOIExists(){
        $this->client->getMetadata($this->testDoiId);
        return !$this->client->hasError();
    }

    public function createTestDOI(){
        $xml = file_get_contents(__DIR__ . "/assets/sample.xml");

        $doi = $this->testDoiId;
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $this->client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml, false
        );
    }
    
    public function setUp(){
        parent::setUp();

        $dotenv = new Dotenv\Dotenv(__DIR__.'/../');
        $dotenv->load();

        $this->testDoiId = getenv('TEST_DOI_ID');

        $this->setUpClient();
        $this->setUpTestDOI();
    }
}