<?php

namespace App\Models\Concerns;

trait HasLocalizedContent
{
    public function localized(string $attribute): ?string
    {
        $value = $this->getAttribute($attribute);

        if (app()->getLocale() !== 'ar') {
            return $value;
        }

        $arabic = $this->getAttribute($attribute.'_ar');

        return filled($arabic) ? $arabic : $value;
    }

    /**
     * @return list<string>
     */
    public function localizedList(string $attribute): array
    {
        $value = $this->getAttribute($attribute);
        $base = is_array($value) ? $value : [];

        if (app()->getLocale() !== 'ar') {
            return $base;
        }

        $arabic = $this->getAttribute($attribute.'_ar');

        return is_array($arabic) && $arabic !== [] ? $arabic : $base;
    }
}
