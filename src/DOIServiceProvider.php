<?php

namespace ANDS\DOI;

use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Validator\URLValidator;
use ANDS\DOI\Validator\XMLValidator;

/**
 * ANDS DOI Service Provider
 *
 * Class DOIServiceProvider
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ANDS\DOI\Repository
 */
class DOIServiceProvider
{

    private $clientRepo = null;
    private $dataciteClient = null;
    private $authenticatedClient = null;
    private $response = null;

    /**
     * DOIServiceProvider constructor.
     * @param ClientRepository $clientRespository
     * @param DataCiteClient $dataciteClient
     */
    public function __construct(
        ClientRepository $clientRespository,
        DataCiteClient $dataciteClient
    ) {
        $this->clientRepo = $clientRespository;
        $this->dataciteClient = $dataciteClient;
    }

    /**
     * Authenticate a client
     *
     * @param $appID
     * @param null $sharedSecret
     * @param null $ipAddress
     * @return bool
     */
    public function authenticate(
        $appID,
        $sharedSecret = null,
        $ipAddress = null
    ) {
        $client = $this->clientRepo->authenticate($appID, $sharedSecret,
            $ipAddress);

        if ($client) {
            $this->setAuthenticatedClient($client);
            return true;
        }

        $this->setResponse('responsecode', 'MT009');
        return false;
    }

    /**
     * @return null
     */
    public function getAuthenticatedClient()
    {
        return $this->authenticatedClient;
    }

    /**
     * Setting the current authenticated client for this object
     *
     * @param $client
     * @return $this
     */
    public function setAuthenticatedClient($client)
    {
        $this->authenticatedClient = $client;
        $this->setResponse('app_id', $client->app_id);
        return $this;
    }

    /**
     * Returns if a client is authenticated
     *
     * @return bool
     */
    public function isClientAuthenticated()
    {
        return $this->getAuthenticatedClient() === null ? false : true;
    }


    public function mint($url, $xml)
    {
        // validate client
        // @todo event handler, message
        if (!$this->isClientAuthenticated()) {
            return false;
        }

        // Validate URL and URL Domain
        $this->setResponse('url', $url);
        $validDomain = URLValidator::validDomains(
            $url, $this->getAuthenticatedClient()->domains
        );
        if (!$validDomain) {
            $this->setResponse("responsecode", "MT014");
            return false;
        }

        // @todo validate xml, xml schema

        // construct DOI
        $doiValue = $this->getNewDOI();
        $this->setResponse('doi', $doiValue);

        // validation on the DOIValue

        // replaced doiValue
        $xml = XMLValidator::replaceDOIValue($doiValue, $xml);

        //update the database DOIRepository
        //@todo add a DOI to the database it should be REQUESTED

        // mint using dataciteClient
        $result = $this->dataciteClient->mint($doiValue, $url, $xml);

        if ($result === true) {
            $this->setResponse('responsecode', 'MT001');
            // @todo set the DOI created earlier status to ACTIVE
        } else {
            $this->setResponse('responsecode', 'MT005');
        }

        return $result;
    }

    /**
     * Returns a new DOI for the currently existing authenticated client
     *
     * @return string
     */
    private function getNewDOI()
    {
        $prefix = $this->getAuthenticatedClient()->datacite_prefix;

        // set to test prefix if  authenticated client is a test DOI APP ID
        if (substr($this->getAuthenticatedClient()->app_id, 0, 4) == 'TEST') {
            $prefix = "10.5072";
        }

        $doiValue = uniqid();
        return $prefix . $doiValue;
    }



    public function update()
    {
        // @todo
    }

    public function activate()
    {
        // @todo
    }

    public function deactivate()
    {
        // @todo
    }

    public function setResponse($key, $value)
    {
        $this->response[$key] = $value;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }
}