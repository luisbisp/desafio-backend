<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('answers', function (Blueprint $table) {
			$table->id();
            $table->string('public_id')->unique();
            $table->string('respondent_id')->index();
            $table->string('form_id')->index();
			$table->string('question');
			$table->text('value');
			$table->string('type');
			$table->string('field_id');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('answers');
	}
};
