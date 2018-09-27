<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 4/6/18
 * Time: 11:22 AM
 */

namespace ANDS\DOI;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Model\Prefix as Prefix;
use ANDS\DOI\Model\Client as TrustedClient;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;


class FabricaClient implements DataCiteClient
{

    private $username;
    private $password;
    private $dataciteUrl = 'https://app.test.datacite.org/';

    private $errors = array();
    private $messages = array();
    public $responseCode;
    /** @var  ClientRepository */
    private $clientRepository;

    /** @var GuzzleClient */
    private $http;

    /**
     * DataCiteClient constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * get the URL content of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function get($doiId)
    {
        return "not implemented yet";
    }

    /**
     * get the Metadata of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function getMetadata($doiId)
    {
        return "not implemented yet";
    }


    public function mint($doiId, $doiUrl, $xmlBody = false)
    {
        return "not implemented yet";
    }

    /**
     * Update XML
     * @param bool|false $xmlBody
     * @return mixed
     */
    public function update($xmlBody = false)
    {
        return "not implemented yet";
    }

    /**
     * UpdateURL
     * @param string $doiUrl, string $doiId
     * @return bool
     */

    public function updateURL($doiId,$doiUrl)
    {
        return "not implemented yet";
    }


    //Don't have an activate function...updating the xml activates a deactivated doi...
    public function activate($xmlBody = false)
    {
        return "not implemented yet";
    }

    public function deActivate($doiId)
    {
        return "not implemented yet";
    }

    /**
     * @return string
     */
    public function getDataciteUrl()
    {
        return $this->dataciteUrl;
    }

    /**
     * @param string $dataciteUrl
     * @return $this
     */
    public function setDataciteUrl($dataciteUrl)
    {
        $this->dataciteUrl = $dataciteUrl;
        $this->http = new GuzzleClient($this->dataciteUrl, [
            'auth' => [ $this->username, $this->password ]
        ]);
        return $this;
    }


    /**
     * @param ClientRepository $clientRepository
     */
    public function setClientRepository($clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * @return mixed
     */
    public function getClientRepository()
    {
        return $this->clientRepository;
    }


    private function log($content, $context = "info")
    {
        if ($content === "" || !$content) {
            return;
        }
        if ($context == "error") {
            $this->errors[] = $content;
        } else {
            if ($context == "info") {
                $this->messages[] = $content;
            }
        }
    }

    public function getResponse()
    {
        return [
            'errors' => $this->getErrors(),
            'messages' => $this->getMessages()
        ];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return String
     */
    public function getErrorMessage()
    {
        $errorMsg = "";
        if(sizeof($this->errors) > 0){
            foreach ($this->errors as $e){
                if(is_array($e)){
                    foreach ($e as $message)
                    {
                        $errorMsg .= isset($message[0]['source']) ? $message[0]['source']." : " : "";
                        $errorMsg .= isset($message[0]['status']) ? $message[0]['status']." : " : "";
                        $errorMsg .= isset($message[0]['title']) ? $message[0]['title'] : " ";
                    }
                }else{
                    $errorMsg = $e;
                }

            }
        }
        return $errorMsg;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return count($this->getErrors()) > 0 ? true : false;
    }

    /**
     * clears messages
     * this function should be called after each request to avoid messages being combined
     */
    public function clearMessages()
    {
        $this->responseCode = 0;
        $this->errors = [];
        $this->messages = [];
    }

    /**
     * @param TrustedClient $client
     * adds a new client to DataCite using a POST request
     */
    public function addClient(TrustedClient $client)
    {
        // clientinfo is fabrica's JSON representation of a client metadata
        $clientInfo = $this->getClientInfo($client);
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
        ];
        $response = "";
        $request = $this->http->post('/clients', $headers, $clientInfo);

        try {
            $response = $request->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors = $e->getResponse()->json();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getResponse()->json();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /*
     * @param Trustedclient
     * same as addclient but PATCH request to url containing the datacite_symbol of the client
     *
     */
    public function updateClient(TrustedClient $client)
    {
        $clientInfo = $this->getClientInfo($client);
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
        ];
        $response = "";
        $request = $this->http->patch('/clients/'.$client->datacite_symbol, $headers, $clientInfo);
        try {
            $response = $request->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /**
     * @param TrustedClient $client
     * client prefixes added in a separate request
     * make sure the request is not called if prefix already given to the client at datacite
     * or it will result a 500 error response
     */
    public function updateClientPrefixes(TrustedClient $client)
    {
        // a JSON representation of the client's prefix relationship
        $clientInfo = $this->getClientPrefixInfo($client);

        if(!$clientInfo){
            $this->messages[] = "No Active Prefix assigned!";
            return;
        }

        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
        ];

        $response = "";

        $request = $this->http->post('/client-prefixes', $headers, $clientInfo);

        try {
            $response = $request->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /**
     * @param TrustedClient $client
     * a simple DELETE request containing the datacite-symbol of the client
     * it was tested and it works but we shouldn't delete a client unless it was created in error
     * datacite keeps client symbols (datacite's client's primary key) even after deletion.
     */
    public function deleteClient(TrustedClient $client)
    {
        $response= "";
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
        ];
        try {
            $response = $this->http->delete('/clients/'.$client->datacite_symbol, $headers)->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /**
     * @param $datacite_symbol
     * @return array|bool|float|int|string
     * we rely on our Database for this data
     * this endpint is not used but tested and can be used to sync datacite information
     */
    public function getClientByDataCiteSymbol($datacite_symbol)
    {
        $response = "";
        try{
            $response = $this->http->get("/clients/$datacite_symbol")->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @param $datacite_symbol
     * @return array|bool|float|int|string
     * we rely on our Database for this data
     * not used but can return the prefixes a trusted client is assigned to at datacite
     */
    public function getClientPrefixesByDataciteSymbol($datacite_symbol){
        $response = "";
        try{
            $response = $this->http->get("/client-prefixes?client-id=".$datacite_symbol)->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @return array|bool|float|int|string
     * return all of our clients and their details from datacite
     * also not used
     * we rely on our Database for this data
     */
    public function getClients()
    {       
        $response = "";
        try{
            $response = $this->http->get('/clients', [], ["query" => ['provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @return array
     * return prefixes assigned to ANDS that is not allocated to any clients
     * is used in loading the available prefixes in our Database
     */
    public function syncUnallocatedPrefixes(){
        $newPrefixes = [];
        $result = $this->getUnalocatedPrefixes();
        foreach($result['data'] as $data){

            $pValue = $data['relationships']['prefix']['data']['id'];
                $newPrefix = array("prefix_value" => $pValue,
                    "datacite_id" => $data['id'],
                    "created" => $data['attributes']['created']);
                $this->clientRepository->addOrUpdatePrefix($newPrefix);
                $newPrefixes[] = $pValue;
        }
        return $newPrefixes;
    }

    /**
     * @return array
     *
     * also not used
     */
    public function syncProviderPrefixes(){
        $newPrefixes = [];
        $result = $this->getProviderPrefixes();
        foreach($result['data'] as $data){

            $pValue = $data['relationships']['prefix']['data']['id'];
            $newPrefix = array("prefix_value" => $pValue,
                "datacite_id" => $data['id'],
                "created" => $data['attributes']['created']);
            $this->clientRepository->addOrUpdatePrefix($newPrefix);
            $newPrefixes[] = $pValue;
        }
        return $newPrefixes;
    }

    /**
     * @return array|bool|float|int|string
     *
     * NOT used but can have future usage if syncing prefixes from datacite ever gets implemented
     * return ALL prefixes ANDS owns
     */
    public function getProviderPrefixes()
    {
        $response = "";
        try {
            $response = $this->http->get('/provider-prefixes',[], ["query" => ['provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();

    }

    /**
     * @param TrustedClient $client
     * @return array|bool|float|int|string
     * also not used currently
     * return ALL prefixes a client is assigned to
     */
    public function getClientPrefixes(TrustedClient $client)
    {
        try {
            $response = $this->http->get('/provider-prefixes',[], ["query" =>
            ['client_id'=>$client->datacite_symbol,
            'provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }


    /**
     * @param $prefix_value
     * @return mixed
     * claim ownership of prefixes for future usage
     */
    private function claimUnassignedPrefix($prefix_value){
        $prefixInfo = $this->getPrefixInfo($prefix_value);

        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
        ];
        $response = "";
        try {
            $response = $this->http->post('/provider-prefixes', $headers, $prefixInfo)->send();
            $result = $response->json();
            $this->responseCode = $response->getStatusCode();
            if($this->responseCode == 201){
                $newPrefix = array("prefix_value" => $prefix_value,
                    "datacite_id" => $result['data']['id'],
                    "created" => $result['data']['attributes']['created']);
                // add the prefix to our registry if successfully claimed
                $this->clientRepository->addOrUpdatePrefix($newPrefix);
            }
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $prefix_value;
    }


    /**
     * @param int $count
     * @return array
     * used to claim prefixes for new trusted clients if we are low or have none
     *
     */
    public function claimNumberOfUnassignedPrefixes($count = 3){
        // finds all unassigned prefixes on Fabrica
        $unallocatedPrefixes = $this->getUnAssignedPrefixes();
        $newPrefixes = [];

        foreach($unallocatedPrefixes['data'] as $prefix)
        {
            // claim only the required number of prefixes
            $newPrefixes[] = $this->claimUnassignedPrefix($prefix['id']);
            if(--$count == 0)
                break;
        }
        return $newPrefixes;
    }
    /*
     *
     Unassigned Prefix means that a Prefix is not given to any Allocator (eg ANDS) on DataCite
     *
     */

    /**
     * @return array|bool|float|int|string
     * get information for all unassigned prefixes from Fabrica
     * that can be claimed by allocators such as ANDS
     */
    public function getUnAssignedPrefixes()
    {
        $response = "";
        try {
            $response = $this->http->get('/prefixes',[], ["query" => ['state'=>'unassigned']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /*
     *
     UnAllocated Prefix means that a Prefix is taken by ANDS but not assigned to datacenters eg one of our trusted client
     *
     */

    /**
     * @return array|bool|float|int|string
     *
     * get the information of all claimed but unallocated prefixes from Fabrica
     * this is used to store the prefix metadata in our database
     * the prefixes then picked up by the registry to populate the drop down of prefixes
     * when new client is created or existing ones are modified
     *
     */
    public function getUnalocatedPrefixes()
    {
        $response = "";
        try {
            $response =  $this->http->get('/provider-prefixes',[], ["query" => ['provider-id'=>'ands','state'=>'without-client']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }


    /*
     *
     *
     * The following functions are used to generate client information that is sent to datacite
     *
     *
     */




    /**
     * @param TrustedClient $client
     * generates a JSON representation of a trusted client
     * @return string
     */
    public function getClientInfo(TrustedClient $client)
    {
        $attributes = [
            "name" => $client->client_name,
            "symbol" => $client->datacite_symbol,
            "domains" => $this->getClientDomains($client),
            "is-active" => true,
            "contact-name" => $client->client_name,
            "contact-email" => getenv("DATACITE_CONTACT_EMAIL")
        ];
        $provider = ["data" => ["type" => "providers",
            "id" => "ands"]];
        $prefixes = $this->getPrefixes($client);
        $relationships = ["provider" => $provider, "prefixes" => $prefixes];
        $clientInfo = ["data" => ["attributes" => $attributes, "relationships" => $relationships, "type" => "client"]];
        return json_encode($clientInfo);
    }

    /**
     *
     * generates a JSON representation of a client and it's active prefix
     * note: only active prefix since datacite
     * rejects adding prefixes with a 500 response if prefix already given to the client
     *
     * @param TrustedClient $tClient
     * @return string
     */
    public function getClientPrefixInfo(TrustedClient $tClient)
    {
        $attributes = ["created" => null];
        $client = ["data" => ["type" => "clients",
            "id" => strtolower($tClient->datacite_symbol)]];
        $prefix = $this->getActivePrefix($tClient);
        if (!$prefix) {
            return false;
        }
        $relationships = ["client" => $client, "prefix" => $prefix];
        $clientInfo = ["data" => ["attributes" => $attributes, "relationships" => $relationships, "type" => "client-prefixes"]];
        return json_encode($clientInfo);
    }


    public function getPrefixInfo($prefix_value){
        $attributes = ["created" => null,];
        $provider = ["data" => ["type" => "providers", "id" => "ands"]];
        $prefix = ["data" => ["type" => "prefixes", "id" => $prefix_value]];
        $relationships = ["provider" => $provider, "prefix" => $prefix];
        $prefixInfo = ["data" => ["attributes" => $attributes, "relationships" => $relationships, "type" => "provider-prefixes"]];
        return json_encode($prefixInfo);
    }


    /**
     * @param TrustedClient $client
     * @return string
     * returns a comma separated string of the client's domains
     *
     */
    public function getClientDomains(TrustedClient $client){
        $domains_str = "";
        $first = true;
        foreach ($client->domains as $domain) {
            if(!$first)
                $domains_str .= ",";
            $domains_str .= $domain->client_domain;
            $first = false;
        }
        return $domains_str;
    }

    /**
     * @param TrustedClient $client
     * @return array returns all prefixes of the given client
     */
    public function getPrefixes(TrustedClient $client){
        $prefixes = array();
        foreach ($client->prefixes as $clientPrefix) {
                $prefixes[] = array("id" => trim($clientPrefix->prefix->prefix_value, "/"),
                    "type" => "prefixes");
        }
        return array("data" => $prefixes);
    }

    /**
     * @param TrustedClient $client
     * @return array returns the active prefix of the given client
     */
    public function getActivePrefix(TrustedClient $client){
        foreach ($client->prefixes as $clientPrefix) {
            if($clientPrefix->active)
                return array("data" => array("type" => "prefixes","id" => $clientPrefix->prefix->prefix_value));
        }
    }
}