<?php

use ANDS\DOI\Model\Client;
use ANDS\DOI\Repository\ClientRespository;

class ClientRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_able_to_get_a_client()
    {
        $repo = new ClientRespository();
        $this->assertNotNull($repo->getFirst());
    }

    /** @test */
    public function it_should_be_able_to_get_a_client_via_id()
    {
        $repo = new ClientRespository();
        $client = $repo->getByID(0);
        $this->assertNotNull($client);
        $this->assertEquals($client->client_id, 0);
        $this->assertEquals($client->client_name, "Testing Auto Data Centre");
    }

    /** @test */
    public function it_should_show_a_client_is_authenticated_when_set()
    {
        $repo = new ClientRespository();
        $repo->setAuthenticatedClient($repo->getFirst());
        $this->assertNotNull($repo->isClientAuthenticated());
    }

    /** @test */
    public function it_should_authenticate_the_right_user_if_has_shared_secret()
    {
        $repo = new ClientRespository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "04b51aa4aa"
        );
        $this->assertTrue($authenticate);
        $this->assertNotNull($repo->getAuthenticatedClient());
    }

    /** @test */
    public function it_does_not_authenticate_if_wrong_shared_secret()
    {
        $repo = new ClientRespository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", "asdfasdfasdf"
        );
        $this->assertFalse($authenticate);
        $this->assertNull($repo->getAuthenticatedClient());
    }

    /** @test */
    public function it_authenticates_user_if_ip_match_and_no_shared_secret_provided()
    {
        $repo = new ClientRespository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", null, "130.56.111.120"
        );
        $this->assertTrue($authenticate);
        $this->assertNotNull($repo->getAuthenticatedClient());
    }

    /** @test */
    public function it__does_notauthenticates_user_if_ip_match_fail()
    {
        $repo = new ClientRespository();
        $authenticate = $repo->authenticate(
            "94c5cdfc4183eca7f836f06f1ec5b85a4932758b", null, "130.56.111.11"
        );
        $this->assertFalse($authenticate);
        $this->assertNull($repo->getAuthenticatedClient());
    }
}