<?php

namespace TrAddressPtt\Models;

use Illuminate\Database\Eloquent\Model;
use TrAddressPtt\Models\Neighborhood;

class Postcode extends Model
{
    protected $fillable = ['neighborhood_id', 'code'];
    public $timestamps = false;

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }
} 