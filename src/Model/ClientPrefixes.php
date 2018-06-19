<?php

namespace ANDS\DOI\Model;

use Illuminate\Database\Eloquent\Model;

class ClientPrefixes extends Model
{
    protected $table = "doi_client_prefixes";
    protected $primaryKey = "id";
    protected $fillable = ["client_id", "prefix_id", "active"];
    public $timestamps = false;
    public function prefix()
    {
        return $this->hasOne(
            Prefix::class,  "id", "prefix_id");
    }
    
}