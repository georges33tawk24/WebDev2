<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasLocalizedContent;

    protected $fillable = [
        'office_id',
        'category_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'estimated_duration_minutes',
        'required_documents',
        'required_documents_ar',
        'is_active',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'required_documents_ar' => 'array',
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