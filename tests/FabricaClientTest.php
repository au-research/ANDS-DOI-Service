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
    private $trustedClientName;
    private $trustedClientContactName;
    private $trustedClientContactEmail;

    /** @test */
    public function it_should_get_the_client()
    {
        $this->assertInstanceOf(\ANDS\DOI\FabricaClient::class, $this->fabricaClient);
        $this->assertInstanceOf(\ANDS\DOI\Repository\ClientRepository::class, $this->repo);
    }

    /** @test */
    public function it_should_get_all_UnAssigned_prefixes(){
        $unAssignedPrefixes = $this->fabricaClient->getUnAssignedPrefixes();
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $this->assertGreaterThan(4, sizeof($unAssignedPrefixes['data']));

    }

    /** @test */
    public function it_should_get_more_from_unassigned_prefixes(){
        $cc = 1;
        $unAssignedPrefixes = $this->fabricaClient->claimNumberOfUnassignedPrefixes($cc);
        $this->assertEquals(201, $this->fabricaClient->responseCode);
        $this->assertEquals($cc, sizeof($unAssignedPrefixes));

    }

    /** @test */
    public function it_should_get_all_Unalocated_prefixes(){
        $unAllocatedPrefixes = $this->fabricaClient->getUnalocatedPrefixes();
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $unAllocatedPrefixeArray = [];
        foreach($unAllocatedPrefixes['data'] as $data){
            $unAllocatedPrefixeArray[] = $data['relationships']['prefix']['data']['id'];
        }
        $this->assertGreaterThan(2, sizeof($unAllocatedPrefixeArray));
    }


    /** @test */
    public function it_should_get_all_provider_prefixes(){
        $providerPrefixes = $this->fabricaClient->getProviderPrefixes();
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $providerPrefixesArray = [];
        foreach($providerPrefixes['data'] as $data){
            $providerPrefixesArray[] = $data['relationships']['prefix']['data']['id'];
        }
        $this->assertGreaterThan(0, sizeof($providerPrefixes));
    }

    /** @test */
    public function it_should_sync_unallocated_prefixes()
    {
        $resultArray = $this->fabricaClient->syncUnallocatedPrefixes();
        $this->assertGreaterThan(2, sizeof($resultArray));
    }

    /** @test */
    public function it_should_sync_all_provider_prefixes()
    {
        $resultArray = $this->fabricaClient->syncProviderPrefixes();
        $this->assertGreaterThan(0, sizeof($resultArray));
    }

    /** @test */
    public function it_should_claim_1_and_sync_unalocated_prefixes_in_db(){
        $cc = 1;
        $oldUnalloc = $this->repo->getUnalocatedPrefixes();
        
        $unAssignedPrefixes = $this->fabricaClient->claimNumberOfUnassignedPrefixes($cc);
        $this->assertEquals(201, $this->fabricaClient->responseCode);
        $this->assertEquals($cc, sizeof($unAssignedPrefixes));
        $this->fabricaClient->syncUnallocatedPrefixes();
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $newUnalloc = $this->repo->getUnalocatedPrefixes();
        //$this->assertGreaterThan(sizeof($oldUnalloc), sizeof($newUnalloc));
    }
    
    /** @test */
    public function it_should_get_all_clients()
    {
        $clients = $this->fabricaClient->getClients();
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $this->assertGreaterThan(10, sizeof($clients['data']));
    }
    
    /** @test */
    public function it_should_assign_a_non_assigned_prefix_to_a_client()
    {
        $unAllocatedPrefix = $this->repo->getOneUnallocatedPrefix();
        if($unAllocatedPrefix){
            $newPrefix = $unAllocatedPrefix->prefix_value;
            $this->trustedClient->addClientPrefix($newPrefix);
            $this->fabricaClient->updateClientPrefixes($this->trustedClient);
            $fabricaInfo = $this->fabricaClient->getClientPrefixesByDataciteSymbol($this->trustedClient->datacite_symbol);
            $this->assertEquals(200, $this->fabricaClient->responseCode);
            $this->assertContains($newPrefix, json_encode($fabricaInfo));
        }
    }

    /** @test */
    public function it_should_get_prefix_info_from_dataite_for_a_client()
    {
        $fabricaInfo = $this->fabricaClient->getClientPrefixesByDataciteSymbol($this->trustedClient->datacite_symbol);
        $this->assertContains("prefixes", json_encode($fabricaInfo));
        $this->assertEquals(200, $this->fabricaClient->responseCode);

    }

    /** @test  **/
    public function it_should_find_client_by_symbol_remote()
    {
        $trustedCient = $this->fabricaClient->getClientByDataCiteSymbol($this->trustedClient->datacite_symbol);
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $this->assertEquals("ands.centre-0", $trustedCient['data']['id']);
    }


//    /** @test  **/
//    public function it_should_add_a_new_client_to_datacite()
//    {
//        $this->fabricaClient->addClient($this->trustedClient);
//        $this->assertEquals(201, $this->fabricaClient->responseCode);
//        $this->assertFalse($this->fabricaClient->hasError());
//    }
// WE SHOULD'T DELETE CLIENTS ON DATACITE (IT WORKS THOUGH)
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
        $params = [
            'client_id' => $this->trustedClient->client_id,
            'client_name' => $this->trustedClientName,
            'client_contact_name' => $this->trustedClientContactName,
        ];

        $this->trustedClient = $this->repo->updateClient($params);

        $this->fabricaClient->updateClient($this->trustedClient);
        $this->assertFalse($this->fabricaClient->hasError());
    }

    /** @test  **/
    public function it_should_create_clientInfo_from_local_client_object()
    {
        $clientInfo = $this->fabricaClient->getClientInfo($this->trustedClient);
        $this->assertContains($this->trustedClient->datacite_symbol, $clientInfo);
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

        $this->fabricaClient->setDataciteUrl(getenv("DATACITE_FABRICA_API_URL"));
        $this->repo = $this->fabricaClient->getClientRepository();

    }

    

    private function getTestClient(){

        $this->trustedClientContactName = getenv("DATACITE_CONTACT_NAME");
        $this->trustedClientName = getenv("TEST_DC_CLIENT");
        $this->trustedClientContactEmail = getenv("DATACITE_CONTACT_EMAIL");
        $this->trustedClient_AppId = getenv("TEST_CLIENT_APPID");

        $this->trustedClient = $this->repo->getByAppID($this->trustedClient_AppId);
        $this->trustedClient_symbol = $this->trustedClient->datacite_symbol;
        if($this->trustedClient == null) {
            $params = [
                'ip_address' => "8.8.8.8",
                'app_id' => $this->trustedClient_AppId,
                'client_name' => urldecode($this->trustedClientName),
                'client_contact_name' => urldecode( $this->trustedClientContactName),
                'client_contact_email' => urldecode($this->trustedClientContactEmail),
                'shared_secret' => getenv("TEST_CLIENT_SHAREDSECRET")
            ];
            var_dump("add_new_client");
            $this->trustedClient = $this->repo->create($params);
        }
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

//    /**
//     *
//     */
//    public function tearDown()
//    {
//        parent::tearDown();
//        $this->removeTestClient();
//    }
}
