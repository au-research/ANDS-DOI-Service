<?php

use ANDS\DOI\DOIManager;
use ANDS\DOI\Repository\ClientRespository;
use Dotenv\Dotenv;

class DOIManagerTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_able_to_create_a_manager()
    {
        $manager = $this->getManager();
        $this->assertNotNull($manager);
    }

    /** @test */
    public function it_should_authenticate_a_real_user()
    {
        $manager = $this->getManager();
        $authenticate = $manager->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "04b51aa4aa"
        );
        $this->assertTrue($authenticate);
        $this->assertNotNull($manager->getAuthenticatedClient());
    }

    /**
     * Helper method to create a DOIManager for every test
     *
     * @return DOIManager
     */
    private function getManager() {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $clientRepository = new ClientRespository(
            getenv("DATABASE_URL"),
            'dbs_dois',
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );

        $doiManager = new DOIManager($clientRepository);

        return $doiManager;
    }
}