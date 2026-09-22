<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('title');
            $table->timestamps();

            $table->unique(['track_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
