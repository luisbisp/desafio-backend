<?php

namespace App\Services;
use App\Enums\IdType;
use App\Services\IdService;

class FormService
{
    public static function addIdsToFieldItems(array $fields): array
    {
        foreach ($fields as $key => $value) {
            if(!array_key_exists("field_id", $fields[$key])){
                $fields[$key]["field_id"] = IdService::create(IdType::FIELD);
            }
        }

        return $fields;
    }
}

