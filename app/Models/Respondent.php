<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Form;
use App\Enums\IdType;
use App\Models\Answer;
use App\Services\IdService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Respondent extends Model
{
    use HasFactory;

    protected $casts = [
        "completed_at" => "datetime"
    ];

    protected $fillable = ['form_id'];
    protected $hidden = ['id'];

	public function form()
    {
        return $this->belongsTo(Form::class);
    }

	public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->public_id = IdService::create(IdType::RESPONDENT);
        });
    }

    public function setAsCompleted() : Respondent
    {
        $now = Carbon::now();
        $this->completed_at = $now;
        $this->updated_at = $now;
        $this->save();

        return $this;
    }
}
