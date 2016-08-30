<?php

namespace ANDS\DOI\Repository;

use ANDS\DOI\Model\Doi as Doi;
use Illuminate\Database\Capsule\Manager as Capsule;

class DoiRepository
{


    public function getFirst()
    {
        return Doi::first();
    }

    public function getByID($id)
    {
        return Doi::find($id);
    }


    /**
     * DoiRespository constructor.
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