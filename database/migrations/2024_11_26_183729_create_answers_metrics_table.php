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
        Schema::create('answers_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('form_id')->index();
            $table->string('field_id')->index()->unique();
            $table->integer('views')->default(0);
            $table->integer('submits')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers_metrics');
    }
};
