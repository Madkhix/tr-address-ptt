<?php

namespace TrAddressPtt\Models;

use Illuminate\Database\Eloquent\Model;
use TrAddressPtt\Models\District;
use TrAddressPtt\Models\Postcode;
use TrAddressPtt\Models\Subdistrict;

class Neighborhood extends Model
{
    protected $fillable = ['district_id', 'subdistrict_id', 'name'];
    public $timestamps = false;

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function postcodes()
    {
        return $this->hasMany(Postcode::class);
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }
} 