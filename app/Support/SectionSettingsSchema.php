<?php

namespace App\Support;

use App\Rules\SafeUrl;
use Illuminate\Validation\Rule;

final class SectionSettingsSchema
{
    /** @var array<string, list<string>> */
    private const ENUMS = [
        'background' => ['white', 'light', 'blue'],
        'alignment' => ['left', 'center'],
        'container' => ['default', 'narrow', 'wide'],
    ];

    /** @var list<string> */
    private const TEXT_KEYS = [
        'alt_text',
        'secondary_button_text',
        'kicker_text',
        'rotating_item_1',
        'rotating_item_2',
        'location_text',
        'degree_text',
        'visual_eyebrow',
        'visual_title',
        'visual_program_1',
        'visual_program_2',
        'visual_footer',
        'feature_1_title',
        'feature_1_description',
        'feature_2_title',
        'feature_2_description',
        'feature_3_title',
        'feature_3_description',
        'footer_label',
        'stat_1_label',
        'stat_2_label',
        'stat_3_label',
        'stat_4_label',
        'step_1_title',
        'step_1_description',
        'step_2_title',
        'step_2_description',
        'step_3_title',
        'step_3_description',
        'step_4_title',
        'step_4_description',
        'info_text',
        'campaign_fallback_text',
        'campaign_link_text',
        'item_1',
        'item_2',
        'item_3',
        'item_4',
        'item_5',
        'item_6',
        'secondary_media_label',
        'tertiary_media_label',
        'partner_link_text',
    ];

    /** @var list<string> */
    private const URL_KEYS = [
        'secondary_button_url',
        'campaign_link_url',
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return [...array_keys(self::ENUMS), ...self::TEXT_KEYS, ...self::URL_KEYS];
    }

    /** @return array<string, list<mixed>> */
    public static function validationRules(): array
    {
        $rules = [];

        foreach (self::ENUMS as $key => $values) {
            $rules["settings.{$key}"] = ['nullable', 'string', Rule::in($values)];
        }

        foreach (self::TEXT_KEYS as $key) {
            $rules["settings.{$key}"] = ['nullable', 'string', 'max:1500'];
        }

        foreach (self::URL_KEYS as $key) {
            $rules["settings.{$key}"] = ['nullable', 'string', 'max:2048', new SafeUrl];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, string>
     */
    public static function normalize(array $settings): array
    {
        $normalized = [];

        foreach (self::ENUMS as $key => $allowedValues) {
            $value = $settings[$key] ?? null;

            if (is_string($value) && in_array($value, $allowedValues, true)) {
                $normalized[$key] = $value;
            }
        }

        foreach ([...self::TEXT_KEYS, ...self::URL_KEYS] as $key) {
            $value = $settings[$key] ?? null;

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value !== '') {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}
