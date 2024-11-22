<?php

namespace App\Models;

use App\Models\User;
use App\Enums\IdType;
use App\Models\Respondent;
use App\Services\IdService;
use App\Services\FormService;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Form extends Model
{
	use HasFactory;

	protected $fillable = ['user_id', 'slug', 'title', 'fields', 'notification', 'show_time_to_complete'];
	protected $casts = [
        'fields' => 'array',
        'notification' => 'array',
    ];
	protected $hidden = ['id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($form) {
            $form->slug = IdService::create(IdType::FORM);
            $form->fields = FormService::addIdsToFieldItems($form->fields);
        });

        static::updating(function ($form) {
            $form->fields = FormService::addIdsToFieldItems($form->fields);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'public_id');
	}
    public function formMetrics()
	{
		return $this->hasOne(FormMetrics::class, 'form_id', 'slug');
	}

	public function respondents()
	{
		return $this->hasMany(Respondent::class);
	}
}
