<?php

namespace ANDS\DOI;

use ANDS\DOI\Repository\ClientRepository;

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

        // @todo throw response
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

        // @todo validate url, url domain

        // @todo validate xml, xml schema

        // construct DOI
        $doiValue = $this->getNewDOI();

        // validation on the DOIValue

        // replaced doiValue
        $xml = $this->replaceDOIValue($doiValue, $xml);

        // mint using dataciteClient
        $result = $this->dataciteClient->mint($doiValue, $url, $xml);

        // @todo gather response
        // $this->response(something)

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

    /**
     * Replaces the DOI Identifier value in the provided XML
     *
     * @param $doiValue
     * @param $xml
     * @return string
     */
    private function replaceDOIValue($doiValue, $xml)
    {
        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);

        // remove the current identifier
        $currentIdentifier = $doiXML->getElementsByTagName('identifier');
        for ($i = 0; $i < $currentIdentifier->length; $i++) {
            $doiXML
                ->getElementsByTagName('resource')
                ->item(0)
                ->removeChild($currentIdentifier->item($i));
        }

        // add new identifier to the DOM
        $newIdentifier = $doiXML->createElement('identifier', $doiValue);
        $newIdentifier->setAttribute('identifierType', "DOI");
        $doiXML
            ->getElementsByTagName('resource')
            ->item(0)
            ->insertBefore(
                $newIdentifier,
                $doiXML->getElementsByTagName('resource')->item(0)->firstChild
            );

        return $doiXML->saveXML();
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
}