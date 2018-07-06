<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 5/6/18
 * Time: 10:36 AM
 */

namespace ANDS\DOI\Model;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Prefix
 * @package ANDS\DOI\Model
 *
 * a datacite prefix that is stored in the registry
 *
 */
class Prefix Extends Model
{
    protected $table = "prefixes";
    protected $primaryKey = "id";
    protected $fillable = ["prefix_value", "datacite_id", "created"];
    public $timestamps = false;
}