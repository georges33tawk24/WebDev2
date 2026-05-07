<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'name',
        'municipality',
        'address',
        'contact_number',
        'contact_email',
        'maps_place_id',
        'latitude',
        'longitude',
        'working_hours',
    ];
    protected $casts = [
        'working_hours' => 'array',
    ];
        
}
