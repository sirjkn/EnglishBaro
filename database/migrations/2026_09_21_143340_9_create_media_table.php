<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['image', 'video', 'document', 'ebook', 'audio', 'other']);
            $table->enum('source_type', ['upload', 'external']);
            $table->enum('provider', ['local', 'youtube', 'imgur', 'vimeo', 'other'])->default('local');
            $table->string('url')->nullable();
            $table->string('external_id')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
