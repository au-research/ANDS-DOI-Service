<?php

namespace ANDS\DOI;

use ANDS\DOI\Repository\ClientRespository;

/**
 * ANDS DOI Manager
 *
 * Class DOIManager
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ANDS\DOI\Repository
 */
class DOIManager
{

    private $clientRepo = null;
    private $authenticatedClient = null;

    /**
     * DOIManager constructor.
     * @param ClientRespository $clientRespository
     */
    public function __construct(ClientRespository $clientRespository)
    {
        $this->clientRepo = $clientRespository;
    }

    /**
     * Authenticate a client
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
        $client = $this->clientRepo->authenticate($appID, $sharedSecret, $ipAddress);

        if ($client) {
            $this->setAuthenticatedClient($client);
            return true;
        }

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


    public function mint()
    {

    }

    public function update()
    {

    }

    public function activate()
    {

    }

    public function deactivate()
    {

    }
}