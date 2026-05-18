<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'service_request_id',
        'citizen_id',
        'office_id',
        'rating',
        'comment',
        'public_reply',
        'private_reply',
    ];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
    public function service(): BelongsTo
{
    return $this->belongsTo(Service::class);
}
}
