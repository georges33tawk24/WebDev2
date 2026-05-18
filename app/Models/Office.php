<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasLocalizedContent;

    protected $fillable = [
        'name',
        'name_ar',
        'municipality',
        'municipality_ar',
        'address',
        'address_ar',
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
