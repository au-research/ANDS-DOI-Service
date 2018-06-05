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
    public function it_should_get_all_prefixes(){
        $this->fabricaClient->getProviderPrefixes();
    }

    /** @test */
    public function it_should_get_all_Unalocated_prefixes(){
        $this->fabricaClient->getUnalocatedPrefixes();
    }
    
    /** @test */
    public function it_should_get_all_clients()
    {
        $clients = $this->fabricaClient->getClients();
        dd($clients);
    }
    /** @test  **/
    public function it_should_find_client_by_symbol_remote()
    {
        $trustedCient = $this->fabricaClient->getClientByDataCiteSymbol("ANDS.CENTRE82");
        dd($trustedCient);
    }
    /** @test  **/
    public function it_should_create_clientInfo_from_local_client_object()
    {
        $testID = 0;
        $repo = $this->fabricaClient->getClientRepository();
        $client = $repo->getByID($testID);
        
        $clientInfo = $this->fabricaClient->getClientInfo($client);
        dd($clientInfo);
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
