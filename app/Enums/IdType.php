<?php

namespace App\Enums;

enum IdType: string
{
    case FORM = 'form';
    case USER = 'user';
    case ANSWER = 'answer';
    case RESPONDENT = 'respondent';
    case FIELD = 'field';
}
