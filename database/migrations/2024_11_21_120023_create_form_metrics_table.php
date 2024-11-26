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
        Schema::create('form_metrics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('form_id')->index();
            $table->integer('total_time')->default(0);
            $table->integer('total_respondents')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_metrics');
    }
};
