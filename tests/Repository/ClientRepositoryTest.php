<?php

use ANDS\DOI\Model\Client;
use ANDS\DOI\Repository\ClientRepository;
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


    /** @test **/
    public function it_should_be_able_to_get_a_client_via_appid() {
        $repo = $this->getClientRepository();
        $client = $repo->getByAppID(getenv("TEST_CLIENT_APPID"));
        $this->assertNotNull($client);
        $this->assertEquals($client->client_id, 0);
        $this->assertEquals($client->client_name, "Testing Auto Data Centre");
    }

    /** @test */
    public function it_should_authenticate_the_right_user_if_has_shared_secret()
    {
        $repo = $this->getClientRepository();
        $authenticate = $repo->authenticate(
            getenv("TEST_CLIENT_APPID"), getenv("TEST_CLIENT_SHAREDSECRET")
        );
        $this->assertNotFalse($authenticate);
    }

    /** @test */
    public function it_does_not_authenticate_if_wrong_shared_secret()
    {
        $repo = $this->getClientRepository();
        $authenticate = $repo->authenticate(
            getenv("TEST_CLIENT_APPID"), "randompasswordthatdoesnotmatch"
        );
        $this->assertFalse($authenticate);
    }

    /** @test */
    public function it_authenticates_user_if_ip_match_and_no_shared_secret_provided()
    {
        $repo = $this->getClientRepository();
        $client = $repo->authenticate(
            getenv("TEST_CLIENT_APPID"), null, "130.56.111.120"
        );
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_does_not_authenticates_user_if_ip_match_fail()
    {
        $repo = $this->getClientRepository();
        $authenticate = $repo->authenticate(
            getenv("TEST_CLIENT_APPID"), null, "130.56.111.11"
        );
        $this->assertFalse($authenticate);
    }

    /** @test **/
    public function it_should_authenticate_user_if_sharedsecret_match_and_ip_mismatch()
    {
        $repo = $this->getClientRepository();
        $client = $repo->authenticate(
            getenv("TEST_CLIENT_APPID"), getenv("TEST_CLIENT_SHAREDSECRET"), "130.56.111.1"
        );
        $this->assertInstanceOf(Client::class, $client);
        $this->assertTrue(true);
    }

    /**
     * Helper method to return a new ClientRepository for each test
     *
     * @return ClientRepository
     */
    private function getClientRepository() {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $repo = new ClientRepository(
            getenv("DATABASE_URL"),
            'dbs_dois',
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );
        return $repo;
    }
}