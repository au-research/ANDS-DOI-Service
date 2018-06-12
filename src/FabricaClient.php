<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 4/6/18
 * Time: 11:22 AM
 */

namespace ANDS\DOI;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Model\Client as TrustedClient;
use Guzzle\Http\Client as GuzzleClient;

class FabricaClient implements DataCiteClient
{

    private $username;
    private $password;
    private $dataciteUrl = 'https://app.test.datacite.org/';

    private $errors = array();
    private $messages = array();
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

        $result = $this->http->post('/clients', $clientInfo);

        // TODO parse result;
        return $result;
    }

    public function updateClient(TrustedClient $client)
    {

        $clientInfo = $this->getClientInfo($client);

        $result = $this->http->patch('/clients', $clientInfo);

        // TODO parse result;
        return $result;
    }

    public function deleteClient(TrustedClient $client)
    {
        
        $result = $this->http->delete('/clients', $client->datacite_symbol);

        // TODO parse result;
        return $result;
    }
    
    public function getClientByDataCiteSymbol($datacite_symbol)
    {
        $result = $this->http->get("/clients/$datacite_symbol")->send();
        return $result->json();
    }

    public function getClients()
    {
        $result = $this->http->get('/clients')->send();
        return $result->json();
    }

    public function getProviderPrefixes()
    {
        $result = $this->http->get('/provider-prefixes',[], ["query" => ['provider-id'=>'ands']])->send();

        return $result->json();
    }

    public function getClientPrefixes(TrustedClient $client)
    {
        $result = $this->http->get('/provider-prefixes',[], ["query" =>
            ['client_id'=>$client->datacite_symbol,
            'provider-id'=>'ands']])->send();
        return $result->json();
    }

    public function getUnalocatedPrefixes()
    {
        $result = $this->http->get('/provider-prefixes',[], ["query" => ['provider-id'=>'ands','state'=>'without-client']])->send();
        return $result->json();
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
        foreach ($client->prefixes as $prefix) {
            foreach ($prefix->prefix as $p) {
                $prefixes[] = array("id" => trim($p->prefix_value, "/"),
                    "type" => "prefix");
            }
        }
        return array("data" => $prefixes);
    }

}