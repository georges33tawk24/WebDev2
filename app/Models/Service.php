<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'office_id',
        'category_id',
        'name',
        'description',
        'price',
        'estimated_duration_minutes',
        'required_documents',
        'is_active',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'is_active' => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function serviceRequests()
{
    return $this->hasMany(ServiceRequest::class);
}
}