<?php

use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Prefix;
use ANDS\DOI\Repository\ClientRepository;
use Dotenv\Dotenv;

class ClientRepositoryTest extends PHPUnit_Framework_TestCase
{

    private $repo;

    /** @test */
    public function it_should_be_able_to_get_a_client_via_id()
    {
        $client = $this->repo->getByID(0);
        $this->assertNotNull($client);
        $this->assertEquals($client->client_id, 0);
        $this->assertEquals($client->client_name, "Testing Auto Data Centre");
    }


    /** @test **/
    public function it_should_be_able_to_get_a_client_via_appid() {
        $client = $this->repo->getByAppID(getenv("TEST_CLIENT_APPID"));
        $this->assertNotNull($client);
        $this->assertEquals($client->client_id, 0);
        $this->assertEquals($client->client_name, "Testing Auto Data Centre");
    }

    /** @test */
    public function it_should_authenticate_the_right_user_if_has_shared_secret()
    {
        $authenticate = $this->repo->authenticate(
            getenv("TEST_CLIENT_APPID"), getenv("TEST_CLIENT_SHAREDSECRET")
        );
        $this->assertNotFalse($authenticate);
    }

    /** @test */
    public function it_does_not_authenticate_if_wrong_shared_secret()
    {
        $authenticate = $this->repo->authenticate(
            getenv("TEST_CLIENT_APPID"), "randompasswordthatdoesnotmatch"
        );
        $this->assertFalse($authenticate);
    }

    /** @test */
    public function it_authenticates_user_if_ip_match_and_no_shared_secret_provided()
    {
        $client = $this->repo->authenticate(
            getenv("TEST_CLIENT_APPID"), null, "130.56.111.120"
        );
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_does_not_authenticates_user_if_ip_match_fail()
    {
        $authenticate = $this->repo->authenticate(
            getenv("TEST_CLIENT_APPID"), null, "130.56.111.11"
        );
        $this->assertFalse($authenticate);
    }

    /** @test **/
    public function it_should_authenticate_user_if_sharedsecret_match_and_ip_mismatch()
    {
        $client = $this->repo->authenticate(
            getenv("TEST_CLIENT_APPID"), getenv("TEST_CLIENT_SHAREDSECRET"), "130.56.111.1"
        );
        $this->assertInstanceOf(Client::class, $client);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_generate_new_client_with_datacite_symbol()
    {
        $client = $this->repo->create([
            "client_name" => "test client"
        ]);
        $this->assertNotNull($client->datacite_symbol);
        $client->delete();
    }

    /** @test **/
    public function it_should_generate_datacite_symbol_for_test_client()
    {
        $testID = 0;
        $client = $this->repo->getByID($testID);
        $this->assertNotNull($client);

        $client->datacite_symbol = "";
        $client->save();

        $client = $this->repo->getByID($testID);
        $this->assertEquals("", $client->datacite_symbol);

        $this->repo->generateDataciteSymbol($client);
        $this->assertNotNull($client->datacite_symbol);
    }

    /** @test **/
    public function it_should_create_a_new_test_client()
    {
        $client = $this->createTestClient();
        $this->assertNotNull($client->datacite_symbol);
    }

    /** @test **/
    public function it_should_add_a_prefix_to_test_client()
    {
        $client = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        $client->removeClientPrefixes();
        $this->assertFalse($client->hasPrefix("10.5072"));
        $client->addClientPrefix("10.5072", true);
        $this->assertTrue($client->hasPrefix("10.5072"));
    }

    /** @test **/
    public function it_should_add_a_domain_to_test_client()
    {
        $client = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        $client->removeClientDomains();
        $client->addDomains("fish.com, apple.tree, ands.org");
        $client->addDomain("coinbit.io");
        $client->addDomain("ands.org.au");
        $client->addDomain("catfish.com");
        $first = false;
        $domains_str = "";
        foreach ($client->domains as $domain) {
            if(!$first)
                $domains_str .= ",";
            $domains_str .= $domain->client_domain;
            $first = false;
        }

        $this->assertContains("fish.com", $domains_str);
        $this->assertContains("ands.org.au", $domains_str);
        $this->assertContains("catfish.com", $domains_str);
        $this->assertContains("coinbit.io", $domains_str);
    }

    /** @test  **/

    public function it_should_update_a_client(){


        $client = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        $params = [
            'client_id' => urldecode($client->client_id),
            'client_name' => urldecode("UPDATED"),
            'client_contact_name' => urldecode("UPDATED"),
            'client_contact_email' => urldecode("UPDATED"),
        ];

        $this->repo->updateClient($params);
        $client = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        $this->assertEquals("UPDATED", $client->client_name);
        $this->assertEquals("UPDATED", $client->client_contact_name);
        $this->assertEquals("UPDATED", $client->client_contact_email);
    }

    /** @test  **/
    public function should_return_unallocated_prefixes()
    {
        $unalloc = $this->repo->getUnalocatedPrefixes();
        $this->assertNotEmpty($unalloc);
    }

    /**
     * Helper method to return a new ClientRepository for each test
     *
     * @return ClientRepository
     */
    private function getClientRepository() {
        $dotenv = new Dotenv(dirname(__FILE__). '/../../');
        $dotenv->load();
        $this->repo = new ClientRepository(
            getenv("DATABASE_URL"),
            getenv("DATABASE"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );
    }

    private function createTestClient(){

        $client = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        if($client == null) {
            $params = [
                'ip_address' => "8.8.8.8",
                'app_id' => "PHPUNIT_TEST_APP_ID",
                'client_name' => urldecode("PHPUNIT_TEST"),
                'client_contact_name' => urldecode("PHPUNIT_TEST"),
                'client_contact_email' => urldecode("PHPUNIT_TEST@PHPUNIT_TEST"),
                'shared_secret' => "PHPUNIT_TEST_SHARED_SECRET"
            ];
            $client = $this->repo->create($params);
        }
        return $client;
    }

    private function removeTestClient(){
        $client = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        $this->repo->deleteClientById($client->client_id);
    }


    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->getClientRepository();
        $this->createTestClient();
    }

    /**
     *
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->removeTestClient();
    }
}