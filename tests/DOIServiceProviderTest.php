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
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "04b51aa4aa"
        );
        $this->assertTrue($authenticate);
        $this->assertNotNull($sp->getAuthenticatedClient());
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