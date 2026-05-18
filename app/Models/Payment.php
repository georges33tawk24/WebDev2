<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'service_request_id',
        'user_id',
        'method',
        'amount',
        'currency',
        'status',
        'gateway_reference',
        'paid_at',
        'crypto_currency',
        'wallet_address',
        'transaction_hash',
        'crypto_amount',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'crypto_amount' => 'decimal:8',
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCrypto(): bool
    {
        return $this->method === 'crypto';
    }
}