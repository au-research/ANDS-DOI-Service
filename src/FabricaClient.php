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

class FabricaClient implements DataCiteClient
{

    private $username;
    private $password;
    private $dataciteUrl = 'https://app.test.datacite.org/';

    private $errors = array();
    private $messages = array();
    public $responseCode;
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


    ///Don't have an activate function...updating the xml activates a deactivated doi...
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
     * @return bool
     */
    public function hasError()
    {
        return count($this->getErrors()) > 0 ? true : false;
    }



    public function addClient(TrustedClient $client)
    {
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
        } catch (ClientErrorResponseException $e) {
            $this->errors = $e->getResponse()->json();
        }
        $this->messages[] = $response;
    }

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
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
    }

    public function updateClientPrefixes(TrustedClient $client)
    {
        $clientInfo = $this->getClientPrefixInfo($client);
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
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;

    }

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
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
    }
    
    public function getClientByDataCiteSymbol($datacite_symbol)
    {
        $response = "";
        try{
            $response = $this->http->get("/clients/$datacite_symbol")->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    public function getClientPrefixesByDataciteSymbol($datacite_symbol){
        $response = "";
        try{
            $response = $this->http->get("/client-prefixes?client-id=".$datacite_symbol)->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
        return $response->json();
    }
    
    
    public function getClients()
    {       
        $response = "";
        try{
            $response = $this->http->get('/clients', [], ["query" => ['provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
        return $response->json();
    }


    public function syncUnallocatedPrefixes(){
        $newPrefixes = [];
        $result = $this->getUnalocatedPrefixes();
        foreach($result['data'] as $data){
            $pValue = $data['relationships']['prefix']['data']['id'];
            $prefix = Prefix::where(["prefix_value" => $pValue])->first();
            if($prefix == null) {
                $newPrefixes[] = $pValue;
                $prefix = new Prefix(["prefix_value" => $pValue]);
                $prefix->save();
            }
        }
        return $newPrefixes;
    }
    
    public function getProviderPrefixes()
    {
        $response = "";
        try {
            $response = $this->http->get('/provider-prefixes',[], ["query" => ['provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
        return $response->json();

    }

    public function getClientPrefixes(TrustedClient $client)
    {
        try {
            $response = $this->http->get('/provider-prefixes',[], ["query" =>
            ['client_id'=>$client->datacite_symbol,
            'provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
        return $response->json();
    }

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
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
    }

    public function claimNumberOfUnassignedPrefixes($count = 3){
        $unallocatedPrefixes = $this->getUnAssignedPrefixes();
        $newPrefixes = [];
        foreach($unallocatedPrefixes['data'] as $prefix)
        {
            $this->claimUnassignedPrefix($prefix['id']);
            $newPrefixes[] = $prefix['id'];
            if(sizeof($this->errors) || --$count == 0);
                break;
        }
        return $newPrefixes;
    }
    /*
     *
     Unassigned Prefix means that a Prefix is not given to any Allocator (eg ANDS) on DataCite
     *
     */

    public function getUnAssignedPrefixes()
    {
        $response = "";
        try {
            $response = $this->http->get('/prefixes',[], ["query" => ['state'=>'unassigned']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /*
     *
     UnAllocated Prefix means that a Prefix is taken by ANDS but not assigned to datacenters eg one of our trusted client
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
            $this->errors[] = $e->getResponse()->json();
        }
        catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @param TrustedClient $client
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
     * @param TrustedClient $client
     * @return string
     */
    public function getClientPrefixInfo(TrustedClient $tClient)
    {
        $attributes = ["created" => null];
        $client = ["data" => ["type" => "clients",
            "id" => strtolower($tClient->datacite_symbol)]];
        $prefix = $this->getActivePrefix($tClient);
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

    public function getPrefixes(TrustedClient $client){
        $prefixes = array();
        foreach ($client->prefixes as $clientPrefix) {
                $prefixes[] = array("id" => trim($clientPrefix->prefix->prefix_value, "/"),
                    "type" => "prefixes");
        }
        return array("data" => $prefixes);
    }

    public function getActivePrefix(TrustedClient $client){
        foreach ($client->prefixes as $clientPrefix) {
            if($clientPrefix->active)
                return array("data" => array("type" => "prefixes","id" => $clientPrefix->prefix->prefix_value));
        }
    }
}