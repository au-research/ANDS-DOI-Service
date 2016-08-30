<?php

use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Formatter\XMLFormatter;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\DOI\Validator\XMLValidator;
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

    /** @test * */
    public function it_should_not_authenticate_a_fake_user()
    {
        $sp = $this->getServiceProvider();
        $authenticate = $sp->authenticate("asdf");
        $this->assertFalse($authenticate);
        $this->assertNull($sp->getAuthenticatedClient());
        $this->assertFalse($sp->isClientAuthenticated());
    }

    /** @test * */
    public function it_should_allow_a_client_to_mint()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        $this->assertTrue($service->isClientAuthenticated());

        $result = $service->mint(
            "http://devl.ands.org.au/minh/", $this->getTestXML()
        );
        $this->assertTrue($result);
    }

    /** @test * */
    public function it_should_allow_minting_a_new_doi_and_return_good_message()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $this->getTestXML()
        );

        $this->assertTrue($result);

        $response = $service->getResponse();

        $this->assertEquals("MT001", $response['responsecode']);

        // test formater as well
        $formatter = new XMLFormatter();
        $message = $formatter->format($response);

        $sxml = new SimpleXMLElement($message);
        $this->assertEquals("MT001", (string)$sxml->responsecode);
        $this->assertEquals($response['doi'], (string)$sxml->doi);
    }

    /** @test * */
    public function it_should_disallow_minting_if_not_authorized()
    {
        $service = $this->getServiceProvider();
        $result = $service->authenticate(uniqid());
        $this->assertFalse($result);

        $response = $service->getResponse();
        $formatter = new XMLFormatter();
        $message = $formatter->format($response);
        $sxml = new SimpleXMLElement($message);

        $this->assertEquals("MT009", (string)$sxml->responsecode);
        $this->assertEquals("failure", (string)$sxml->attributes()->type);
    }


    /** @test **/
    public function it_should_not_allow_minting_a_new_doi_and_return_error_message()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $this->getInvalidTestXML()
        );

        $this->assertFalse($result);

        $response = $service->getResponse();

        $this->assertEquals("MT006", $response['responsecode']);
    }

    /** @test * */
    public function it_should_disallow_minting_of_url_not_in_top_domain()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $result = $service->mint(
            "https://google.com/", $this->getTestXML()
        );
        $this->assertFalse($result);

        $response = $service->getResponse();
        $formatter = new XMLFormatter();
        $message = $formatter->format($response);
        $sxml = new SimpleXMLElement($message);

        $this->assertEquals("MT014", (string)$sxml->responsecode);
        $this->assertEquals("failure", (string)$sxml->attributes()->type);


    }

    /** @test * */
    public function it_should_not_activate_an_active_doi()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $this->assertFalse($service->activate('10.5072/00/53ED646B7A9A6'));

    }

    /** @test * */
    public function it_should_activate_an_inactive_doi()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $this->assertTrue($service->activate('10.5072/00/57BB9A544C048'));

    }

    /** @test * */
    public function it_should_deactivate_an_active_doi()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $this->assertTrue($service->deactivate('10.5072/00/57BB9A544C048'));

    }

    /** @test * */
    public function it_should_allow_current_client_doi_access()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $this->assertTrue($service->isDoiAuthenticatedClients('10.5072/00/53ED646B7A9A6'));

    }

    /** @test * */
    public function it_should_not_allow_current_client_doi_access()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $this->assertFalse($service->isDoiAuthenticatedClients('10.5072/11/53ED646B7A9A6'));

    }

    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getTestXML()
    {
        return file_get_contents(__DIR__ . "/sample.xml");
    }


    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getInvalidTestXML()
    {
        return file_get_contents(__DIR__."/sample_invalid.xml");
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
    private function getServiceProvider()
    {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $clientRepository = new ClientRepository(
            getenv("DATABASE_URL"),
            'dbs_dois',
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );

        $doiRepository = new DoiRepository(
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
            $clientRepository, $doiRepository, $dataciteClient
        );

        return $serviceProvider;
    }
}