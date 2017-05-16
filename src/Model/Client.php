<?php

namespace ANDS\DOI\Model;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'client_name',
        'client_contact_name',
        'ip_address',
        'app_id',
        'client_contact_email',
        'datacite_prefix',
        'datacite_symbol',
        'shared_secret'
    ];

    public $timestamps = false;

    /**
     * The table of the model
     * @var string
     */
    protected $table = "doi_client";

    /**
     * The primary key of the model,
     * used for DataciteClient::find() method
     *
     * @var string
     */
    protected $primaryKey = "client_id";

    /**
     * Returns all the domain owned by this client
     */
    public function domains()
    {
        return $this->hasMany(
            ClientDomain::class, "client_id", "client_id"
        );
    }
}