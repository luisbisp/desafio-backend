<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormMetrics extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'total_respondents', 'total_time'];
    protected $hidden = ['id'];

}
