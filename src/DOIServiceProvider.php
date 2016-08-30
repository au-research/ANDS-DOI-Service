<?php

namespace ANDS\DOI;

use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
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
    private $doiRepo = null;
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
        DoiRepository $doiRespository,
        DataCiteClient $dataciteClient
    ) {
        $this->clientRepo = $clientRespository;
        $this->doiRepo = $doiRespository;
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

    /**
     * Returns if a client is authenticated
     *
     * @param $doiValue
     * @return bool
     */
    public function isDoiAuthenticatedClients($doiValue)
    {
        $clientPrefix=$this->getAuthenticatedClient()->datacite_prefix.str_pad($this->getAuthenticatedClient()->client_id, 2,0,STR_PAD_LEFT)."/";
        return (strpos($doiValue,$clientPrefix)===0);
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

        // Validate xml
        if($this->validateXML($xml) === false){
            $this->setResponse('responsecode', 'MT006');
            return false;
        }

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
     * Returns true if xml is datacite valid else false and sets error
     *
     * @return boolean
     */
    private function validateXML($xml)
    {
        $xmlValidator = new XMLValidator();
        $result = $xmlValidator->validateSchemaVersion($xml);
        if ($result === false) {
            $this->setResponse("verbosemessage", $xmlValidator->getValidationMessage());
            return false;
        }
        return true;
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

        $client_id = str_pad($this->getAuthenticatedClient()->client_id, 2,0,STR_PAD_LEFT)."/";

        $doiValue = uniqid();

        return $prefix . $client_id . $doiValue;
    }



    public function update()
    {
        // @todo
    }

    public function activate($doiValue)
    {
        // validate client
        // @todo event handler, message
        if (!$this->isClientAuthenticated()) {
            $this->setResponse('responsecode', 'MT009');
            return false;
        }

        // check if this client owns this doi

        if(!$this->isDoiAuthenticatedClients($doiValue)){
            $this->setResponse('responsecode', 'MT0010');
            $this->setResponse('verbosemessage',$doiValue." is not owned by ".$this->getAuthenticatedClient()->client_name);
            return false;
        }

        //get the doi info
        $doi = $this->doiRepo->getByID($doiValue);

        $doi_xml = $doi->datacite_xml;

        //check if the doi is inactive
        if($doi->status!='INACTIVE')
        {
            $this->setResponse('responsecode', 'MT010');
            $this->setResponse('verbosemessage', 'DOI '.$doiValue." not set to INACTIVE so cannot activate it");
            return false;
        }

        // activate using dataciteClient update method;
        $result = $this->dataciteClient->update($doi_xml);

        if ($result === true) {
            $this->setResponse('responsecode', 'MT004');
            //update the database DOIRepository
            //@todo update DOI to the database it should be ACTIVE
        } else {
            $this->setResponse('responsecode', 'MT010');
        }

        return $result;
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