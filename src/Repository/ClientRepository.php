<?php

namespace ANDS\DOI\Repository;

use ANDS\DOI\Validator\IPValidator;
use ANDS\DOI\Model\Client as Client;
use Illuminate\Database\Capsule\Manager as Capsule;

class ClientRespository
{

    private $authenticatedClient = null;

    public function getFirst()
    {
        return Client::first();
    }

    public function getByID($id)
    {
        return Client::find($id);
    }

    /**
     * Authenticate a client based on their shared secret and/or their ipAddress
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
        $client = Client::where('app_id', $appID)->first();

        if ($client === null) {
            return false;
        }

        // shared secret matching
        if ($sharedSecret &&
            $client->shared_secret === $sharedSecret
        ) {
            $this->setAuthenticatedClient($client);
            return true;
        }

        // ip address matching
        if ($ipAddress &&
            IPValidator::validate($ipAddress, $client->ip_address)
        ) {
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
     */
    public function setAuthenticatedClient($client)
    {
        $this->authenticatedClient = $client;
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
     * ClientRespository constructor.
     * @param $databaseURL
     * @param string $database
     * @param string $username
     * @param string $password
     * @internal param string $databasePassword
     */
    public function __construct(
        $databaseURL,
        $database = "dbs_dois",
        $username = "webuser",
        $password = ""
    ) {
        $capsule = new Capsule;
        $capsule->addConnection(
            [
                'driver' => 'mysql',
                'host' => $databaseURL,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ], 'default'
        );
        $capsule->setAsGlobal();
        $capsule->getConnection('default');
        $capsule->bootEloquent();
    }


}