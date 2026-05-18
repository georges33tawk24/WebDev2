<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'service_request_id',
        'citizen_id',
        'office_id',
        'rating',
        'comment',
        'public_reply',
        'private_reply',
    ];

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}