<?php

namespace App\Models\Concerns;

trait HasLocalizedContent
{
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (app()->isLocale('fr')) {
            return $attributes;
        }

        foreach ($this->translatable ?? [] as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->localizedValue($key, $attributes[$key]);
            }
        }

        return $attributes;
    }

    public function getAttributeValue($key): mixed
    {
        $value = parent::getAttributeValue($key);

        if (app()->isLocale('fr') || ! in_array($key, $this->translatable ?? [], true)) {
            return $value;
        }

        return $this->localizedValue($key, $value);
    }

    private function localizedValue(string $key, mixed $value): mixed
    {
        $locale = app()->getLocale();
        $translations = $this->getAttributes()['translations'] ?? null;
        $translations = is_string($translations) ? json_decode($translations, true) : $translations;
        $translated = is_array($translations) ? data_get($translations, $locale.'.'.$key) : null;

        if ($key === 'settings' && is_array($value) && is_array($translated)) {
            return [...$value, ...$translated];
        }

        if (is_string($translated) && trim($translated) !== '') return $translated;
        if (is_array($translated) && $translated !== []) return $translated;

        return $value;
    }
}
