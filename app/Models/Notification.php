<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function localizedTitle(): string
    {
        return $this->localizedPart('title');
    }

    public function localizedBody(): string
    {
        $body = $this->localizedPart('body');
        $i18n = $this->data['i18n'] ?? null;

        if (is_array($i18n) && isset($i18n['body_suffix_key']) && is_string($i18n['body_suffix_key'])) {
            $body .= ' '.__(
                $i18n['body_suffix_key'],
                self::resolveReplaceParams(is_array($i18n['body_suffix_replace'] ?? null) ? $i18n['body_suffix_replace'] : []),
            );
        }

        return $body;
    }

    /**
     * @param  array<string, mixed>  $replace
     * @return array<string, mixed>
     */
    public static function resolveReplaceParams(array $replace): array
    {
        $resolved = [];

        foreach ($replace as $key => $value) {
            if (is_array($value)) {
                if (isset($value['status']) && is_string($value['status'])) {
                    $resolved[$key] = __('ui.status.'.$value['status']);

                    continue;
                }

                if (isset($value['datetime'])) {
                    $resolved[$key] = localized_datetime($value['datetime']);

                    continue;
                }

                if (isset($value['office_id'])) {
                    $office = Office::query()->find($value['office_id']);
                    $resolved[$key] = $office?->localized('name') ?? __('ui.na');

                    continue;
                }

                if (isset($value['service_id'])) {
                    $service = Service::query()->find($value['service_id']);
                    $resolved[$key] = $service?->localized('name') ?? __('ui.na');

                    continue;
                }
            }

            $resolved[$key] = $value;
        }

        return $resolved;
    }

    private function localizedPart(string $part): string
    {
        $i18n = $this->data['i18n'] ?? null;

        if (! is_array($i18n)) {
            return $part === 'title' ? (string) $this->title : (string) $this->body;
        }

        $key = $i18n[$part.'_key'] ?? null;

        if (! is_string($key)) {
            return $part === 'title' ? (string) $this->title : (string) $this->body;
        }

        $replace = is_array($i18n[$part.'_replace'] ?? null)
            ? $i18n[$part.'_replace']
            : [];

        return __($key, self::resolveReplaceParams($replace));
    }
}
