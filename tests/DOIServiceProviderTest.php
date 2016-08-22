<?php

use ANDS\DOI\DOIServiceProvider;
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

    /**
     * Helper method to create a DOIManager for every test
     *
     * @return DOIManager
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

        $serviceProvider = new DOIServiceProvider($clientRepository);

        return $serviceProvider;
    }
}