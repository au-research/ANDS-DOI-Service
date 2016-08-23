<?php

use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Repository\ClientRepository;
use Dotenv\Dotenv;

class DOIServiceProviderTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_able_to_create_a_service_provider()
    {
        $sp = $this->getServiceProvider();
        $this->assertNotNull($sp);
    }

    /** @test */
    public function it_should_authenticate_a_real_user()
    {
        $sp = $this->getServiceProvider();
        $authenticate = $sp->authenticate(
            getenv("TEST_CLIENT_APPID"), getenv("TEST_CLIENT_SHAREDSECRET")
        );
        $this->assertTrue($authenticate);
        $this->assertNotNull($sp->getAuthenticatedClient());
        $this->assertTrue($sp->isClientAuthenticated());
    }

    /** @test **/
    public function it_should_not_authenticate_a_fake_user()
    {
        $sp = $this->getServiceProvider();
        $authenticate = $sp->authenticate("asdf");
        $this->assertFalse($authenticate);
        $this->assertNull($sp->getAuthenticatedClient());
        $this->assertFalse($sp->isClientAuthenticated());
    }

    /** @test **/
    public function it_should_allow_a_client_to_mint()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        $this->assertTrue($service->isClientAuthenticated());

        $result = $service->mint("http://devl.ands.org.au/minh/", $this->getTestXML());
        $this->assertTrue($result);
    }

    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getTestXML()
    {
        return file_get_contents(__DIR__."/sample.xml");
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
        return $client;
    }

    /**
     * Helper method to create a DOIManager for every test
     *
     * @return DOIServiceProvider
     */
    private function getServiceProvider() {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $clientRepository = new ClientRepository(
            getenv("DATABASE_URL"),
            'dbs_dois',
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );

        $dataciteClient = new \ANDS\DOI\DataCiteClient(
            getenv("DATACITE_USERNAME"),
            getenv("DATACITE_PASSWORD")
        );
        $dataciteClient->setDataciteUrl(getenv("DATACITE_URL"));

        $serviceProvider = new DOIServiceProvider(
            $clientRepository, $dataciteClient
        );

        return $serviceProvider;
    }
}