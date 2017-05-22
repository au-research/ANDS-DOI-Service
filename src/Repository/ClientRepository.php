<?php

namespace ANDS\DOI\Repository;

use ANDS\DOI\Validator\IPValidator;
use ANDS\DOI\Model\Client as Client;
use Illuminate\Database\Capsule\Manager as Capsule;

class ClientRepository
{

    private $message = null;

    public function create($params)
    {
        $client = new Client;
        $client->fill($params);
        $client->save();

        // update datacite_symbol
        $this->generateDataciteSymbol($client);

        return $client;
    }

    public function getAll()
    {
        return Client::all();
    }

    /**
     * Generate a datacite symbol for the given client
     * ANDS.CENTRE-1
     * ANDS.CENTRE-9
     * ANDS.CENTRE10
     * ANDS.CENTRE99
     * ANDS.C100
     * ANDS.C102
     *
     * @param Client $client
     * @return Client
     */
    public function generateDataciteSymbol(Client $client)
    {
        $prefix = "ANDS.";
        $id = $client->client_id;

        // prefix before the
        if ($id < 100) {
            $prefix .= "CENTRE";
        }

        if ($id < 10) {
            $prefix .= "-";
        } elseif ($id >= 100) {
            // prefix before the ID (new form)
            $prefix .= "C";
        }

        $client->datacite_symbol = $prefix.$id;
        $client->save();

        return $client;
    }

    public function getFirst()
    {
        return Client::first();
    }

    public function getByID($id)
    {
        return Client::find($id);
    }

    public function getByAppID($appID)
    {
        return Client::where('app_id', $appID)->first();
    }

    /**
     * Authenticate a client based on their shared secret and/or their ipAddress
     *
     * @param $appID
     * @param null $sharedSecret
     * @param null $ipAddress
     * @param bool $manual
     * @return bool
     */
    public function authenticate(
        $appID,
        $sharedSecret = null,
        $ipAddress = null,
        $manual = false
    ) {
        $test_prefix = false;
        if(substr($appID,0,4)=="TEST") {
            $appID = str_replace("TEST","",$appID);
            $test_prefix = true;
        }
        $client = $this->getByAppID($appID);

        // No Client Exists
        if ($client === null) {
            $this->setMessage("Client does not exists");
            return false;
        }

        // Client exists and it's a manual request
        if ($manual) {
            return $client;
        }

        //client exists and has been set to a test account via the app_id make sure that the test prefix is used

        if($test_prefix) $client['datacite_prefix'] = "10.5072/";

        // if sharedSecret is provided
        if ($sharedSecret) {
            if ($client->shared_secret !== $sharedSecret) {
                $this->setMessage("Authentication Failed. Mismatch shared secret provided");
                return false;
            }

            return $client;
        }

        // ip address matching
        if ($ipAddress &&
            IPValidator::validate($ipAddress, $client->ip_address) === false
        ) {
            $this->setMessage("Authentication Failed. Mismatch IP Address. Provided IP Address: ". $ipAddress);
            return false;
        }

        return $client;
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

    /**
     * @return null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param null $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }


}