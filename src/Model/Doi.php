<?php

namespace ANDS\DOI\Model;

use Illuminate\Database\Eloquent\Model;

class Doi extends Model
{
    /**
     * The table of the model
     * @var string
     */
    protected $table = "doi_objects";

    /**
     * The primary key of the model,
     * used for Doi::find() method
     *
     * @var string
     */
    protected $primaryKey = "doi_id";

    protected $casts = [
        'doi_id' => 'string',
    ];

}