<?php

namespace App\Casts;

use App\Support\SectionSettingsSchema;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<array<string, string>, array<string, string>|string|null> */
class ControlledSectionSettings implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return SectionSettingsSchema::normalize(is_array($decoded) ? $decoded : []);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return json_encode(SectionSettingsSchema::normalize(is_array($decoded) ? $decoded : []), JSON_THROW_ON_ERROR);
    }
}
