<?php

namespace App\Services;

use App\Enums\IdType;
use Illuminate\Support\Str;

class IdService
{
    public static function create(IdType $type): string
    {
        $settings = self::getSettings($type);
        return $settings[0] . Str::random($settings[1]);
    }

    protected static function getSettings(IdType $type): array
    {
        // prefix, length
        return match ($type) {
            IdType::USER => ['user_', 16],
            IdType::RESPONDENT => ['rsp_', 32],
            IdType::ANSWER => ['ans_', 32],
            IdType::FORM => ['', 8],
            IdType::FIELD => ['field_', 16],
            default => ['', 16],
        };
    }
}

