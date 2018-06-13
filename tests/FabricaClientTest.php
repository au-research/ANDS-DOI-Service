<?php


use Dotenv\Dotenv;


class FabricaClientTest extends PHPUnit_Framework_TestCase
{
    /** @var \ANDS\DOI\FabricaClient */
    private $fabricaClient;

    /** @test */
    public function it_should_get_the_client()
    {
        $this->assertInstanceOf(\ANDS\DOI\FabricaClient::class, $this->fabricaClient);
        $this->assertInstanceOf(\ANDS\DOI\Repository\ClientRepository::class, $this->fabricaClient->getClientRepository());
    }

    /** @test */
    public function it_should_add_a_client()
    {
        // given we have a client
        $testID = 0;
        $repo = $this->fabricaClient->getClientRepository();
        $client = $repo->getByID($testID);

        // we add it to datacite
        $this->client->addClient($client);

        // then we can see it on datacite
//        $fetch = $this->client->fetchClient($client->id);

        // compare
    }

    /** @test */
    public function it_should_get_all_UnAssigned_prefixes(){
        $unAssignedPrefixes = $this->fabricaClient->getUnAssignedPrefixes();
        //var_dump(sizeof($unAssignedPrefixes['data']));
        $this->assertGreaterThan(24, sizeof($unAssignedPrefixes['data']));

    }

    /** @test */
    public function it_should_get_all_Unalocated_prefixes(){
        $unAllocatedPrefixes = $this->fabricaClient->getUnalocatedPrefixes();
        $unAllocatedPrefixeArray = [];
        foreach($unAllocatedPrefixes['data'] as $data){
            $unAllocatedPrefixeArray[] = $data['relationships']['prefix']['data']['id'];
        }
        $this->assertGreaterThan(2, sizeof($unAllocatedPrefixeArray));
    }
    
    /** @test */
    public function it_should_get_all_clients()
    {
        $clients = $this->fabricaClient->getClients();
        $this->assertGreaterThan(10, sizeof($clients['data']));
    }

    /** @test  **/
    public function it_should_find_client_by_symbol_remote()
    {
        $trustedCient = $this->fabricaClient->getClientByDataCiteSymbol("ANDS.CENTRE82");
        $this->assertEquals("ands.centre82", $trustedCient['data']['id']);
    }

    /** @test  **/
    public function it_should_create_clientInfo_from_local_client_object()
    {
        $testID = 0;
        $repo = $this->fabricaClient->getClientRepository();
        $client = $repo->getByID($testID);
        
        $clientInfo = $this->fabricaClient->getClientInfo($client);
        $this->assertContains("ANDS.CENTRE-0", $clientInfo);
    }

    /**
     * @return \ANDS\DOI\FabricaClient
     */
    private function getClient()
    {
        $username = getenv("DATACITE_FABRICA_USERNAME");
        $password = getenv("DATACITE_FABRICA_PASSWORD");
        $this->fabricaClient = new \ANDS\DOI\FabricaClient($username, $password);

        $this->fabricaClient->setClientRepository(new \ANDS\DOI\Repository\ClientRepository(
            getenv("DATABASE_URL"),
            getenv("DATABASE"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        ));

        $this->fabricaClient->setDataciteUrl(getenv("DATACITE_FABRICA_URL"));

    }

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $dotenv = new Dotenv(dirname(__FILE__). '/../');
        $dotenv->load();
        $this->getClient();
    }
}
