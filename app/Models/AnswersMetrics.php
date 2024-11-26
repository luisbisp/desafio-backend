<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswersMetrics extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'field_id', 'views', 'submits'];
    protected $hidden = ['id'];

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'slug');
    }
}
