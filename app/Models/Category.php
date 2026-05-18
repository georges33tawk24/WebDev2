<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasLocalizedContent;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
