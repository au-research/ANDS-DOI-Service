<?php

use ANDS\DOI\Model\Client;
use ANDS\DOI\Repository\ClientRespository;
use Dotenv\Dotenv;

class ClientRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_able_to_get_a_client()
    {
        $repo = $this->getClientRepository();
        $this->assertNotNull($repo->getFirst());
    }

    /** @test */
    public function it_should_be_able_to_get_a_client_via_id()
    {
        $repo = $this->getClientRepository();
        $client = $repo->getByID(0);
        $this->assertNotNull($client);
        $this->assertEquals($client->client_id, 0);
        $this->assertEquals($client->client_name, "Testing Auto Data Centre");
    }

    /** @test */
    public function it_should_authenticate_the_right_user_if_has_shared_secret()
    {
        $repo = $this->getClientRepository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "04b51aa4aa"
        );
        $this->assertNotFalse($authenticate);
    }

    /** @test */
    public function it_does_not_authenticate_if_wrong_shared_secret()
    {
        $repo = $this->getClientRepository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "asdfasdfasdf"
        );
        $this->assertFalse($authenticate);
    }

    /** @test */
    public function it_authenticates_user_if_ip_match_and_no_shared_secret_provided()
    {
        $repo = $this->getClientRepository();
        $client = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", null, "130.56.111.120"
        );
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it__does_notauthenticates_user_if_ip_match_fail()
    {
        $repo = $this->getClientRepository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", null, "130.56.111.11"
        );
        $this->assertFalse($authenticate);
    }

    /**
     * Helper method to return a new ClientRepository for each test
     *
     * @return ClientRespository
     */
    private function getClientRepository() {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $repo = new ClientRespository(
            getenv("DATABASE_URL"),
            'dbs_dois',
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );
        return $repo;
    }
}