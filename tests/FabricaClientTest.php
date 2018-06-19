<?php


use Dotenv\Dotenv;


class FabricaClientTest extends PHPUnit_Framework_TestCase
{
    /** @var \ANDS\DOI\FabricaClient */
    private $fabricaClient;
    /** @var  \ANDS\DOI\Model\Client */
    private $trustedClient;
    /** @var  \ANDS\DOI\Repository\ClientRepository */
    private $repo;

    private $trustedClient_symbol;
    private $trustedClient_AppId;

    /** @test */
    public function it_should_get_the_client()
    {
        $this->assertInstanceOf(\ANDS\DOI\FabricaClient::class, $this->fabricaClient);
        $this->assertInstanceOf(\ANDS\DOI\Repository\ClientRepository::class, $this->repo);
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


//    /** @test  **/
//    public function it_should_add_a_new_client_to_datacite()
//    {
//        $this->fabricaClient->addClient($this->trustedClient);
//        $this->assertFalse($this->fabricaClient->hasError());
//    }

//    /** @test **/
//    public function it_should_delete_a_client_on_datacite(){
//        var_dump($this->trustedClient->datacite_symbol);
//        $this->fabricaClient->deleteClient($this->trustedClient);
//        $this->assertFalse($this->fabricaClient->hasError());
//    }

    /** @test  **/
    public function it_should_update_the_client_on_datacite()
    {
        $params = [
            'client_id' => $this->trustedClient->client_id,
            'client_name' => urldecode("UPDATED CLIENT NAME"),
            'client_contact_name' => urldecode("UPDATED CONTACT NAME"),
        ];

        $this->trustedClient = $this->repo->updateClient($params);

        $this->fabricaClient->updateClient($this->trustedClient);
        $this->assertFalse($this->fabricaClient->hasError());
    }

    /** @test  **/
    public function it_should_create_clientInfo_from_local_client_object()
    {
        $clientInfo = $this->fabricaClient->getClientInfo($this->trustedClient);
        $this->assertContains("ANDS.CTEST1", $clientInfo);
    }

    /**
     * @return \ANDS\DOI\FabricaClient
     */
    private function getFabricaClient()
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
        $this->repo = $this->fabricaClient->getClientRepository();

    }

    

    private function getTestClient(){

        $this->trustedClientName = getenv("TEST_DC_CLIENT");
        $this->trustedClient = $this->repo->getByAppID($this->trustedClient_AppId);
        $this->trustedClient_symbol = getenv("TEST_DC_CLIENT_DATACITE_SYMBOL");
        if($this->trustedClient == null) {
            $params = [
                'ip_address' => "8.8.8.8",
                'app_id' => $this->trustedClientName."APP_ID",
                'client_name' => urldecode($this->trustedClientName),
                'client_contact_name' => urldecode($this->trustedClientName),
                'client_contact_email' => urldecode($this->trustedClientName.".@ands.org.au"),
                'shared_secret' => $this->trustedClientName,"SHARED_SECRET"
            ];
            $this->trustedClient = $this->repo->create($params);
        }
        $this->trustedClient->datacite_symbol = getenv("TEST_DC_CLIENT_DATACITE_SYMBOL");
        $this->trustedClient->save();

    }

    private function removeTestClient(){
        $client = $this->repo->getByAppID($this->trustedClientName."APP_ID");
        $this->repo->deleteClientById($client->client_id);
    }
    
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $dotenv = new Dotenv(dirname(__FILE__). '/../');
        $dotenv->load();
        $this->getFabricaClient();
        $this->getTestClient();
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
