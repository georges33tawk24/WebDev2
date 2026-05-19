<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestStatusHistory extends Model
{
    protected $fillable = [
        'service_request_id',
        'changed_by',
        'from_status',
        'to_status',
        'comment',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getDisplayCommentAttribute(): ?string
    {
        if (! $this->comment) {
            return null;
        }

        $clean = preg_replace('/\s*\[payment:\d+\]\s*$/', '', $this->comment);

        return is_string($clean) && $clean !== '' ? $clean : null;
    }
}
