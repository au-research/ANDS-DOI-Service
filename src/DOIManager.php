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
     * @return bool
     */
    public function authenticate()
    {
        return true;
    }

    /**
     * Returns the currently authenticated client
     */
    public function getAuthenticatedClient()
    {

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