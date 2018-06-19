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
    

    public function addDomains($domains){
        $domArray = explode(",", $domains);
        foreach ($domArray as $d){
            $this->addDomain($d);
        }
    }


    public function addDomain($domain){
        $domain = trim($domain);
        if($domain == '')
            return;
        $dm = ClientDomain::where("client_domain", $domain)
            ->where("client_id", $this->client_id)->first();
        if ($dm != null) {
            return;
        }
        $this->domains()->save(new ClientDomain(["client_domain" => $domain]));
    }


    public function removeClientDomains(){
        ClientDomain::where("client_id", $this->client_id)->delete();
    }
    /**
     * Returns all the prefixes assigned to this client
     */

    public function prefixes()
    {
        return $this->hasMany(
            ClientPrefixes::class, "client_id", "client_id"
        );
    }

    public function addClientPrefixes($prefixes){
        $prefixArray = explode(",", $prefixes);
        foreach ($prefixArray as $p){
            $this->addClientPrefix($p);
        }
    }
    /** add prefix to client */

    public function addClientPrefix($prefix_value, $active = true){

        $prefix = null;
        $prefix_value = trim($prefix_value);
        if($prefix_value == '')
            return;

        //set all other prefixes for this client as non active if this prefix is the active one
        if($active){
            ClientPrefixes::where("client_id", $this->client_id)->update(["active"=>false]);
        }

        try {
            // Get the Prefix if exists
            $prefix = Prefix::where("prefix_value", $prefix_value)->first();
            if($prefix) {
                //if this prefix already assigned to this client do nothing)
                $cp = ClientPrefixes::where("prefix_id", $prefix->id)
                    ->where("client_id", $this->client_id)->first();
                if ($cp != null) {
                    ClientPrefixes::where("prefix_id", $prefix->id)
                        ->where("client_id", $this->client_id)->update(["active"=>$active]);
                    return;
                }
            }

        }
        catch(Exception $e)
        {}

        if($prefix == null)// create a new prefix and assign it to the Client
        {
            $prefix = new Prefix(["prefix_value" => $prefix_value]);
            $prefix->save();
        }

        $this->prefixes()->save(new ClientPrefixes(["prefix_id" => $prefix->id, "active"=>$active]));



    }

    public function removeClientPrefix($prefix_value){
        $prefix = Prefix::where("prefix_value", $prefix_value)->first();
        if($prefix == null){
            return;
        }
        ClientPrefixes::where("client_id", $this->client_id)->where("prefix_id", $prefix->id)->delete();
    }
    
    
    public function removeClientPrefixes(){
        ClientPrefixes::where("client_id", $this->client_id)->delete();
    }
    
}