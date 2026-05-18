<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'email',
    'email_verified_at',
    'is_active',
    'password',
    'role_id',
    'office_id',
    'id_document_path',
    'date_of_birth',
    'phone',
    'two_factor_verified_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'phone' => 'encrypted',
            'id_document_path' => 'string',
            'two_factor_verified_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function isCitizen(): bool
    {
        return $this->role?->slug === 'citizen';
    }

    public function needsIdDocument(): bool
    {
        if (! $this->isCitizen()) {
            return false;
        }

        $path = $this->id_document_path;

        if (blank($path) || $this->isPlaceholderIdPath($path)) {
            return true;
        }

        return ! Storage::disk('public')->exists($path);
    }

    public function hasValidIdDocument(): bool
    {
        return $this->isCitizen() && ! $this->needsIdDocument();
    }

    public function purgeInvalidIdDocumentPath(): void
    {
        if (! $this->isCitizen()) {
            return;
        }

        $path = $this->id_document_path;

        if (blank($path)) {
            return;
        }

        if ($this->isPlaceholderIdPath($path) || ! Storage::disk('public')->exists($path)) {
            $this->forceFill(['id_document_path' => null])->save();
        }
    }

    private function isPlaceholderIdPath(string $path): bool
    {
        return str_contains($path, 'seed-placeholder');
    }
}
