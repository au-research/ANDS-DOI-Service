<?php

namespace ANDS\DOI\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientPrefixes
 * @package ANDS\DOI\Model
 *
 * as of R28 trusted clients were given new and unique prefixes
 * this solution allowed to have their legacy prefix as well
 * their current and active prefix stored in the databse
 *
 *
 */
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