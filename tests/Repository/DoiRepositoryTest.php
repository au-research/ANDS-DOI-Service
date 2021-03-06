<?php

use ANDS\DOI\MdsClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use Dotenv\Dotenv;

class DoiRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_able_to_get_a_doi()
    {
        $repo = $this->getDoiRepository();

        $this->assertNotNull($repo->getFirst());
    }

    /** @test */
    public function it_should_be_able_to_get_a_doi_via_id()
    {
        // mint a DOI, make sure it exists in the database
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $this->getTestXML()
        );
        $this->assertTrue($result);

        // get the DOI
        $response = $service->getResponse();
        $doiID = $response['doi'];

        // check repository
        $repo = $this->getDoiRepository();
        $doi = $repo->getByID($doiID);

        $this->assertNotNull($doi);
        $this->assertSame($doi->doi_id, $doiID);
        $this->assertEquals($doi->publisher, "ANDS");
    }

    /** @test **/
    public function it_should_create_and_update_doi_correctly()
    {
        $repo = $this->getDoiRepository();

        $doiID = '10.70131/TEST_DOI_'.uniqid();
        $doi = $repo->doiCreate([
            'doi_id' => $doiID,
            'publisher' => 'ANDS',
            'publication_year' => 2016,
            'status' => 'REQUESTED',
            'identifier_type' => 'DOI',
            'created_who' => 'SYSTEM',
            'url' => 'http://devl.ands.org.au/minh'
        ]);

        $this->assertTrue($doi);

        // update it
        $doi = $repo->getByID($doiID);
        $repo->doiUpdate($doi, ['url' => 'http://devl.ands.org.au/minh2']);

        // check that it's updated
        $doi = $repo->getByID($doiID);
        $this->assertEquals($doi->url, 'http://devl.ands.org.au/minh2');

        // delete the DOI
        $doi = $repo->getByID($doiID);
        $doi->delete();

        // assert it's gone
        $doi = $repo->getByID($doiID);
        $this->assertNull($doi);
    }

    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getTestXML()
    {
        return file_get_contents(__DIR__ . "/../assets/sample.xml");
    }

    /**
     * Helper method for getting the test DOI Client for fast authentication
     *
     * @return mixed
     */
    private function getTestClient()
    {
        $dotenv = new Dotenv('./');
        $dotenv->load();

        $client = Client::where('app_id', getenv('TEST_CLIENT_APPID'))->first();
        $client->addClientPrefix("10.70131");
        return $client;
    }


    /**
     * Helper method to return a new DoiRepository for each test
     *
     * @return DoiRepository
     */
    private function getDoiRepository() {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $repo = new DoiRepository(
            getenv("DATABASE_URL"),
            getenv("DATABASE"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );
        return $repo;
    }

    /**
     * Helper method to create a DOIManager for every test
     *
     * @return DOIServiceProvider
     */
    private function getServiceProvider()
    {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $clientRepository = new ClientRepository(
            getenv("DATABASE_URL"),
            getenv("DATABASE"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );

        $doiRepository = new DoiRepository(
            getenv("DATABASE_URL"),
            getenv("DATABASE"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );

        $dataciteClient = new MdsClient(
            getenv("DATACITE_USERNAME"),
            getenv("DATACITE_PASSWORD"),
            getenv("DATACITE_TEST_PASSWORD")
        );
        $dataciteClient->setDataciteUrl(getenv("DATACITE_TEST_URL"));

        $serviceProvider = new DOIServiceProvider(
            $clientRepository, $doiRepository, $dataciteClient
        );

        return $serviceProvider;
    }
}