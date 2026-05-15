<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'reference_number',
        'citizen_id',
        'service_id',
        'office_id',
        'status',
        'notes',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(RequestStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class);
    }
}