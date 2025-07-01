<?php

namespace TrAddressPtt\Models;

use Illuminate\Database\Eloquent\Model;
use TrAddressPtt\Models\District;
use TrAddressPtt\Models\Neighborhood;

class Subdistrict extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['district_id', 'name'];
    public $timestamps = false;

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }
} 