<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function staff(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
    public function serviceRequests(): HasMany
{
    return $this->hasMany(ServiceRequest::class);
}
}
