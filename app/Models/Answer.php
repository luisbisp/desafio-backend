<?php

namespace App\Models;

use App\Enums\IdType;
use App\Models\Respondent;
use App\Services\IdService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'respondent_id',
		'form_id',
		'question',
		'value',
		'type',
		'field_id'
	];

	protected $hidden = [
		'id',
	];

	protected static function boot()
	{
		parent::boot();
		static::creating(function ($user) {
			$user->public_id = IdService::create(IdType::ANSWER);
		});
	}

	public function respondent()
	{
		return $this->belongsTo(Respondent::class, 'respondent_id', 'public_id');
	}

	public function form()
	{
		return $this->belongsTo(Form::class, 'form_id', 'slug');
	}
}
